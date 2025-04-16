<?php

namespace App\Http\Controllers\Api;

use App\Actions\EmailConfirmation;
use App\Domains\Entity\Contracts\EntityDriverInterface;
use App\Domains\Entity\Contracts\WithCreditInterface;
use App\Domains\Entity\EntityStats;
use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use App\Jobs\SendPasswordResetEmail;
use App\Models\Setting;
use App\Models\User;
use App\Models\Plan;
use App\Models\UserOrder;
use App\Models\Team\Team;
use Carbon\Carbon;
use App\Models\Currency;
use App\Models\Gateways;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Cashier\Subscription as Subscriptions;
use App\Enums\Plan\FrequencyEnum;
use App\Actions\CreateActivity;
use App\Services\GatewaySelector;
use App\Services\PaymentGateways\Contracts\CreditUpdater;
use App\Domains\Entity\Enums\EntityEnum;

class AuthController extends Controller
{
    use CreditUpdater;
    
    /**
     * @OA\Post(
     *      path="/api/auth/register",
     *      operationId="register",
     *      tags={"Authentication"},
     *      summary="Register a new user",
     *      description="Registers a new user with the provided data",
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              required={"name", "surname", "email", "password", "password_confirmation"},
     *
     *              @OA\Property(property="name", type="string", example="John"),
     *              @OA\Property(property="surname", type="string", example="Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="password123"),
     *              @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *              @OA\Property(property="affiliate_code", type="string", nullable=true, example="your_affiliate_code"),
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="User registered successfully",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error or user already exists",
     *      ),
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string', 'max:255'],
            'surname'  => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $affCode = null;
        if ($request->affiliate_code !== null) {
            $affUser = User::where('affiliate_code', $request->affiliate_code)->first();
            if ($affUser !== null) {
                $affCode = $affUser->id;
            }
        }

        if (Helper::appIsDemo()) {
            $user = User::query()->create([
                'name'                    => $request->name,
                'surname'                 => $request->surname,
                'email'                   => $request->email,
                'email_confirmation_code' => Str::random(67),
                'password'                => Hash::make($request->password),
                'email_verification_code' => Str::random(67),
                'affiliate_id'            => $affCode,
                'affiliate_code'          => Str::upper(Str::random(12)),
            ]);
            EntityStats::all()->map(function ($entity) use ($user) {
                return $entity->forUser($user)->list()->each(function (EntityDriverInterface&WithCreditInterface $entity) {
                    return $entity->setDefaultCreditForDemo();
                });
            });
        } else {
            $user = User::query()->create([
                'name'                    => $request->name,
                'surname'                 => $request->surname,
                'email'                   => $request->email,
                'email_confirmation_code' => Str::random(67),
                'password'                => Hash::make($request->password),
                'email_verification_code' => Str::random(67),
                'affiliate_id'            => $affCode,
                'affiliate_code'          => Str::upper(Str::random(12)),
            ]);

            $user->updateCredits(setting('freeCreditsUponRegistration', User::getFreshCredits()));
        }

        return response()->json('OK', 200);
    }

    /**
     * @OA\Post(
     *      path="/api/auth/logout",
     *      operationId="logout",
     *      tags={"Authentication"},
     *      summary="Logout the authenticated user",
     *      description="Logs out the authenticated user and revokes the access token",
     *      security={{ "passport": {} }},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Logout successful",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->each(function ($token, $key) {
            $token->delete();
        });

        return response()->json(['message' => 'Logout successful'], 200);
    }

    /**
     * Send a password reset link to the given user.
     *
     * @OA\Post(
     *      path="/api/auth/forgot-password",
     *      operationId="forgotPassword",
     *      tags={"Authentication"},
     *      summary="Initiate password reset",
     *      description="Initiate the password reset process by sending an email with a reset link.",
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              required={"email"},
     *
     *              @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Password reset link sent successfully",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error or user not found",
     *      ),
     * )
     */
    public function sendPasswordResetMail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user !== null) {
            $user->password_reset_code = Str::random(67);
            $user->save();

            // Dispatch the job to send the password reset email asynchronously
            dispatch(new SendPasswordResetEmail($user));

            return response()->json(['message' => __('Password reset link sent successfully')], 200);
        }

        return response()->json(['error' => __('User not found')], 422);
    }

    /**
     * Verify user's email using the provided confirmation code.
     *
     * @OA\Get(
     *      path="/api/auth/email/verify",
     *      operationId="verifyEmail",
     *      tags={"Authentication"},
     *      summary="Verify user's email",
     *      description="Verify the user's email using the provided confirmation code.",
     *
     *      @OA\Parameter(
     *          name="email_confirmation_code",
     *          in="query",
     *          required=true,
     *          description="Email confirmation code",
     *
     *          @OA\Schema(type="string"),
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Email verified successfully",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error or user not found",
     *      ),
     * )
     */
    public function emailConfirmationMail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email_confirmation_code' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::where('email_confirmation_code', $request->email_confirmation_code)->first();

        if ($user !== null) {
            $user->email_confirmation_code = null;
            $user->email_confirmed = 1;
            $user->status = 1;
            $user->save();

            return response()->json(['message' => 'Email verified successfully'], 200);
        }

        return response()->json(['error' => __('Email not found')], 422);
    }

    /**
     * Resend the confirmation email.
     *
     * @OA\Post(
     *      path="/api/auth/email/verify/resend",
     *      operationId="resendConfirmationEmail",
     *      tags={"Authentication"},
     *      summary="Resend confirmation email",
     *      description="Resend the confirmation email to the user if not already verified.",
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              required={"email"},
     *
     *              @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Confirmation email resent successfully",
     *
     *          @OA\JsonContent(
     *              type="object",
     *              example={"message": "Confirmation email resent successfully"},
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=403,
     *          description="Email already verified",
     *
     *          @OA\JsonContent(
     *              type="object",
     *              example={"error": "Email already verified"},
     *          ),
     *      ),
     * )
     */
    public function resend(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if (! $user->isConfirmed() && ! $user->isAdmin()) {
            EmailConfirmation::forUser($user)->resend();

            return response()->json(['message' => __('Confirmation email resent successfully')], 200);
        }

        return response()->json(['error' => __('Email not found or already verified')], 403);
    }

    /**
     * Get actively supported login methods.
     *
     * @OA\Get(
     *      path="/api/auth/social-login",
     *      operationId="getSupportedLoginMethods",
     *      tags={"Authentication"},
     *      summary="Get supported login methods",
     *      description="Get actively supported login methods as a list.",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="array",
     *
     *              @OA\Items(
     *                  type="string",
     *                  example="github",
     *              ),
     *              example={"github", "google"},
     *          ),
     *      ),
     * )
     */
    public function getSupportedLoginMethods(): JsonResponse
    {
        $setting = Setting::getCache();

        $supportedLoginMethods = [];

        if ($setting->github_active) {
            $supportedLoginMethods[] = 'github';
        }

        if ($setting->twitter_active) {
            $supportedLoginMethods[] = 'twitter';
        }

        if ($setting->google_active) {
            $supportedLoginMethods[] = 'google';
        }

        if ($setting->facebook_active) {
            $supportedLoginMethods[] = 'facebook';
        }

        return response()->json($supportedLoginMethods, 200);
    }
    
    
public function registerUser(Request $request)
{
    // Validate incoming request
    $request->validate([
        'name'      => 'required|string|max:255',
        'email'     => 'required|email|max:255|unique:users,email',
        'surname'   => 'nullable|string|max:255',
        'password'  => 'required|string|min:6',
        'plan'      => 'nullable|exists:plans,id'
    ]);

    // Fetch the selected plan or default to Plan ID 1
    $plan = $request->plan ? Plan::findOrFail($request->plan) : Plan::findOrFail(1);

    // Ensure these are arrays (handle potential JSON stored in DB)
    $planAiTools = is_array($plan->plan_ai_tools) ? $plan->plan_ai_tools : json_decode($plan->plan_ai_tools, true);
    $planFeatures = is_array($plan->plan_features) ? $plan->plan_features : json_decode($plan->plan_features, true);
    $openAiItems = is_array($plan->open_ai_items) ? $plan->open_ai_items : json_decode($plan->open_ai_items, true);

    // Ensure we have arrays, even if they were NULL or invalid
    $planAiTools = is_array($planAiTools) ? $planAiTools : [];
    $planFeatures = is_array($planFeatures) ? $planFeatures : [];
    $openAiItems = is_array($openAiItems) ? $openAiItems : [];

    // Set all features, AI tools, and open AI items to false
    // foreach ($planAiTools as $key => $value) {
    //     $planAiTools[$key] = true;
    // }

    // foreach ($planFeatures as $key => $value) {
    //     $planFeatures[$key] = true;
    // }

    // foreach ($openAiItems as $key => $value) {
    //     $openAiItems[$key] = true;
    // }

    // Create user with default false values
    $user = User::create([
        'name'          => $request->name,
        'email'         => $request->email,
        'surname'       => $request->surname ?? '',
        'password'      => Hash::make($request->password),
        'affiliate_code' => Str::upper(Str::random(12)), 
        'open_ai_items' => json_encode($openAiItems),  // Store as JSON
        'plan_ai_tools' => json_encode($planAiTools),  // Store as JSON
        'plan_features' => json_encode($planFeatures), // Store as JSON
    ]);
    
        
        // $tax = 0;
        // $taxRate = 0;
        // $status = 'free_approved';
        $total = $plan->price;
        $gateway = Gateways::where('is_active', 1)->first();
        // dd($gateway);
       
        if ($gateway) {
            
            
            $taxValue = taxToVal($plan->price, $gateway->tax);
            $total += $taxValue;
            $gatewayCode = $gateway->code;
            $tax = $taxValue;
            $taxRate = $gateway->tax;
            $status = $gateway->code . '_approved';
            // dd($status);
            
            GatewaySelector::selectGateway($gatewayCode)::subscribe($plan);
            
        }
        $currency = Currency::where('id', $gateway->currency)->first()->code;
        $settings = Setting::getCache();

        
            // Create the subscription with the customer ID, price ID, and necessary options.
            $subscription = new Subscriptions;
            $subscription->user_id = $user->id;
            $subscription->name = $plan->name;
            $subscription->stripe_id = 'FLS-' . strtoupper(Str::random(13));
            $subscription->stripe_status = $status;
            $subscription->stripe_price = 'Not Needed';
            $subscription->quantity = 1;
            $subscription->trial_ends_at = null;
            $subscription->ends_at = $plan->frequency === FrequencyEnum::LIFETIME_MONTHLY->value ? Carbon::now()->addMonths(1) : Carbon::now()->addYears(1);
            $subscription->auto_renewal = 1;
            $subscription->plan_id = $plan->id;
            $subscription->paid_with = $gatewayCode;
            $subscription->tax_rate = $taxRate;
            $subscription->tax_value = $tax;
            $subscription->total_amount = $total;
            $subscription->save();

            // save the order
            $order = new UserOrder;
            $order->order_id = $subscription->stripe_id;
            $order->plan_id = $plan->id;
            $order->user_id = $user->id;
            $order->payment_type = $gatewayCode;
            $order->price = $total;
            $order->affiliate_earnings = ($total * $settings->affiliate_commission_percentage) / 100;
            $order->status = 'Success';
            $order->country = $user->country ?? 'Unknown';
            $order->tax_rate = $taxRate;
            $order->tax_value = $tax;
            $order->save();

            self::creditIncreaseSubscribePlan($user, $plan);
            
            CreateActivity::for($user, __('Subscribed to'), $plan->name . ' ' . __('Plan'), null);
            
            
            

    return response()->json([
        'message' => 'User registered successfully!',
        'user' => $user
    ], 201);
}

public function addFeatureToUser(Request $request)
{
    // $request->validate([
    //     'email'        => 'required',
    //     'feature_tag'  => 'required|string',
    //     'tool_name'    => 'required|string',
    //     'model_name'   => 'required|string',
    //     'credit'       => 'required|integer',
    //     'isUnlimited'  => 'required|boolean',
    // ]);

    $user = User::where('email', $request->email)->firstOrFail();

    // ---- Handle plan_ai_tools ---- //
    $userFeatures = $user->plan_ai_tools ?? [];

    if (is_string($userFeatures)) {
        $userFeatures = json_decode($userFeatures, true);
    }
    
    if (!is_array($userFeatures)) {
        $userFeatures = [];
    }
    
    // Now this will work:
    if (array_key_exists($request->feature_tag, $userFeatures)) {
        $userFeatures[$request->feature_tag] = true;
    } else {
        return response()->json([
            'message' => 'Feature does not exist in plan_ai_tools!',
            'available_features' => $userFeatures
        ], 400);
    }

    // ---- Handle entity_credits ---- //
    $entityCredits = $user->entity_credits ?? [];
    if (!is_array($entityCredits)) $entityCredits = [];

    if (!isset($entityCredits[$request->tool_name])) {
        return response()->json([
            'message' => 'Tool name does not exist in entity_credits!',
            'available_tools' => array_keys($entityCredits)
        ], 400);
    }

    if (!isset($entityCredits[$request->tool_name][$request->model_name])) {
        return response()->json([
            'message' => 'Model name does not exist under tool!',
            'available_models' => array_keys($entityCredits[$request->tool_name])
        ], 400);
    }

    $entityCredits[$request->tool_name][$request->model_name]['credit'] = $request->credit;
    $entityCredits[$request->tool_name][$request->model_name]['isUnlimited'] = $request->isUnlimited;

    // ---- Update User ---- //
    $user->update([
        'plan_ai_tools' => $userFeatures,
        'entity_credits' => $entityCredits,
    ]);

    return response()->json([
        'message' => 'Feature & credits updated successfully!',
        'updated_features' => $userFeatures,
        'updated_entity_credits' => $entityCredits
        // 'model_name' => 
    ], 200);
}

public function addTokensToUser(Request $request)
{
    // dd($request->all());
    
    // Validate the request: At least one of the two parameters must be provided
    $request->validate([
        'email'       => 'required|exists:users,email',
        'word_tokens'   => 'nullable|integer|min:0',
        'image_tokens'  => 'nullable|integer|min:0',
    ]);

    // Find the user
    
    // dd("here");
    
    $user = User::where('email',$request->email)->firstOrFail();
    
    
    

    // Add the new tokens to the existing values
    if ($request->has('word_tokens')) {
        $user->remaining_words += $request->word_tokens;
    }

    if ($request->has('image_tokens')) {
        $user->remaining_images += $request->image_tokens;
    }

    // Save the updated user data
    $user->save();

    return response()->json([
        'message' => 'Tokens added successfully!',
        'updated_remaining_words' => $user->remaining_words,
        'updated_remaining_images' => $user->remaining_images,
    ], 200);
}

public function updateEntityCreditsToUnlimited(Request $request)
{
    // Validate the request
    // $request->validate([
    //     'email' => 'required|exists:users,email',
    // ]);

    // Find the user
    $user = User::where('email', $request->email)->firstOrFail();

    // Decode entity_credits if it's a string (to handle it as an array)
    $entityCredits = $user->entity_credits ?? [];
    if (is_string($entityCredits)) {
        $entityCredits = json_decode($entityCredits, true);
    }

    // Ensure it's an array
    if (!is_array($entityCredits)) {
        $entityCredits = [];
    }

    // Iterate over all tool names in entity_credits and set isUnlimited to true for all models
    foreach ($entityCredits as $toolName => $models) {
        foreach ($models as $modelName => $modelData) {
            // Set isUnlimited to true
            // $entityCredits[$toolName][$modelName]['isUnlimited'] = true;
            // if (in_array(strtolower($modelName), $textModels)) 
            // {
                if ($entityCredits[$toolName][$modelName]['credit'] > 0 && $entityCredits[$toolName][$modelName]['isUnlimited'] == false) 
                {
                    $entityCredits[$toolName][$modelName]['isUnlimited'] = true;
                }
            // }
        
        }
        
    }

    // Update the user's entity_credits field
    $user->update([
        'entity_credits' => $entityCredits,
    ]);

    // Return a success response
    return response()->json([
        'message' => 'All models updated to isUnlimited true successfully!',
        'updated_entity_credits' => $entityCredits
    ], 200);
}


public function updateTextModelsToUnlimited(Request $request)
{
    // Validate the request
    $request->validate([
        'email' => 'required|exists:users,email',
    ]);

    // Find the user
    $user = User::where('email', $request->email)->firstOrFail();

    // Get the entity_credits and ensure it's an array
    $entityCredits = $user->entity_credits;

    // If it's a string, decode it to an array
    if (is_string($entityCredits)) {
        $entityCredits = json_decode($entityCredits, true);

        // If json_decode fails, fallback to an empty array
        if (json_last_error() !== JSON_ERROR_NONE) {
            $entityCredits = [];
        }
    }

    // Ensure it's an array
    if (!is_array($entityCredits)) {
        $entityCredits = [];
    }

    // Define the list of text-based models that should have `isUnlimited` set to true
    $textModels = [
        'davinci-002',
        'o1-preview',
        'o1-mini',
        'o3-mini',
        'text-embedding-004',
        'gemini-1__5-pro-latest',
        'gemini-pro',
        'gpt-4__5-preview',
        'plagiarismcheck',
        'text-davinci-003',
        'gpt-3__5-turbo-16k',
        'gpt-3__5-turbo',
        'gpt-3.5-turbo-16k',
        'gpt-3__5-turbo-0125',
        'gpt-3.5-turbo',
        'gpt-3__5-turbo-1106',
        'gpt-3.5-turbo-0125',
        'gpt-3.5-turbo-1106',
        'gpt-4',
        'gpt-4-turbo',
        'gpt-4-1106-preview',
        'gpt-4-0125-preview',
        'gpt-4-vision-preview',
        'gpt-4o',
        'gpt-4o-mini',
        'text-embedding-ada-002',
        'text-embedding-3-small',
        'text-embedding-3-large',
        'whisper-1',
        'tts-1',
        'tts-1-hd',
        'claude-3-5-sonnet-20240620',
        'claude-3-7-sonnet-20250219',
        'claude-3-haiku-20241022',
        'claude-3-5-sonnet-20241022',
        'claude-3-sonnet-20240229',
        'claude-2__1',
        'claude-2__0',
        'claude-3-opus-20240229',
        'claude-3-haiku-20240307',
        'claude-2.1',
        'claude-2.0',
        'voyage-2',
        'voyage-large-2',
        'voyage-code-2',
        'gemini-1.5-pro-latest',
        'gemini-pro',
        'gemini-1.5-flash',
        'gemini-pro-vision',
        'deepseek-chat',
        'deepseek-reasoner',
        'davinci-002',
        'azure',
        'speechify',
        'serper',
        'perplexity',
        'x_ai/grok-2-1212',
        'grok-2-1212',
        'x_ai/grok-2-vision-1212',
        'grok-2-vision-1212',
        'anthropic/claude-3-5-haiku-20241022',
        'anthropic/claude-3-5-haiku-20241022:beta',
        'anthropic/claude-3-5-haiku',
        'anthropic/claude-3-5-haiku:beta',
        'neversleep/llama-3__1-lumimaid-70b',
        'anthracite-org/magnum-v4-72b',
        'x-ai/grok-beta',
        'mistralai/ministral-8b',
        'mistralai/ministral-3b',
        'qwen/qwen-2__5-7b-instruct',
        'nvidia/llama-3__1-nemotron-70b-instruct',
        'inflection/inflection-3-pi',
        'inflection/inflection-3-productivity',
        'liquid/lfm-40b:free',
        'liquid/lfm-40b',
        'thedrummer/rocinante-12b',
        'eva-unit-01/eva-qwen-2__5-14b',
        'anthracite-org/magnum-v2-72b',
        'meta-llama/llama-3__2-3b-instruct:free',
        'meta-llama/llama-3__2-1b-instruct:free',
        'meta-llama/llama-3__2-3b-instruct',
        'meta-llama/llama-3__2-1b-instruct',
        'perplexity/llama-3__1-sonar-huge-128k-online',
        'perplexity/llama-3__1-sonar-large-128k-online',
        'perplexity/llama-3__1-sonar-large-128k-chat',
        'perplexity/llama-3__1-sonar-small-128k-online',
        'perplexity/llama-3__1-sonar-small-128k-chat'
    ];

    // Iterate through all tool names in entity_credits and set `isUnlimited` for text-based models
    foreach ($entityCredits as $toolName => $models) {
        foreach ($models as $modelName => $modelData) {
            // If the model is a text-based model, set `isUnlimited` to true
            // if (in_array(strtolower($modelName), $textModels)) {
            //     $entityCredits[$toolName][$modelName]['isUnlimited'] = true;
            // }
            
            if (in_array(strtolower($modelName), $textModels)) 
            {
                if ($entityCredits[$toolName][$modelName]['credit'] > 0 && $entityCredits[$toolName][$modelName]['isUnlimited'] == false) 
                {
                    $entityCredits[$toolName][$modelName]['isUnlimited'] = true;
                }
            }
            
             // Additional check: if credits > 0 and isUnlimited is false, set isUnlimited to true
             
            
        }
    }

    // Update the user's entity_credits with the new data
    $user->update([
        'entity_credits' => $entityCredits
    ]);

    // Return a success response
    return response()->json([
        'message' => 'Text models updated to isUnlimited true successfully!',
        'updated_entity_credits' => $entityCredits
    ], 200);
}






/**
 * Get the list of text-based models dynamically from the EntityEnum.
 *
 * @return array
 */
// private function getTextModels(): array
// {
//     // List of text-based models from EntityEnum
//     $textModels = [
//         EntityEnum::DAVINCI,
//         EntityEnum::TEXT_DAVINCI_003,
//         EntityEnum::GPT_3_5_TURBO_16K,
//         EntityEnum::GPT_3_5_TURBO,
//         EntityEnum::GPT_3_5_TURBO_0125,
//         EntityEnum::GPT_3_5_TURBO_1106,
//         EntityEnum::GPT_4,
//         EntityEnum::GPT_4_TURBO,
//         EntityEnum::GPT_4_1106_PREVIEW,
//         EntityEnum::GPT_4_0125_PREVIEW,
//         EntityEnum::GPT_4_VISION_PREVIEW,
//         EntityEnum::GPT_4_O,
//         EntityEnum::GPT_4_O_MINI,
//         EntityEnum::GPT_4_O1_PREVIEW,
//         EntityEnum::GPT_4_O1_MINI,
//         EntityEnum::TEXT_EMBEDDING_ADA_002,
//         EntityEnum::TEXT_EMBEDDING_3_SMALL,
//         EntityEnum::TEXT_EMBEDDING_3_LARGE,
//         EntityEnum::WHISPER_1,
//     ];

//     // Return the array of text-based model names
//     return array_map(fn($model) => $model->value, $textModels);
// }



}
