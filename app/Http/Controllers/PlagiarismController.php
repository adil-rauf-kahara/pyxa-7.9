<?php

namespace App\Http\Controllers;

use App\Helpers\Classes\Helper;
use App\Models\OpenAIGenerator;
use App\Models\Setting;
use App\Models\SettingTwo;
use App\Models\UserOpenai;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\AiModel;
use App\Enums\AIEngine;

class PlagiarismController extends Controller
{
    // public function plagiarismCheck(Request $request)
    // {
        
        
    //     ini_set('max_execution_time', 240);

    //     $settings = SettingTwo::first();
        
    //     if($settings->plagiarism_key == ""){
    //         return response()->json(['message' => 'Please input plagiarism api key'], 401);
    //     }

    //     try {
    //         $client = new Client([
    //             // 'base_uri' => 'https://plagiarismcheck.org/api/v1/',
    //             'url' => 'https://api.gowinston.ai/v2/plagiarism/'
    //         ]);

    //         $headers = [
                
    //             'Content-Type' => 'application/json',
    //             'Authorization' => 'Bearer ' . $settings->plagiarism_key,
                
    //         ];
            
    //         $data = [
    //             'language' => 'en',
    //             'text' => $request->text
    //         ];

    //         $response =$client->post([
    //             'headers' => $headers,
    //             'data' => $data
    //         ]);
            
    //         dd(json_decode($response));
            
    //         $result = json_decode($response->getBody()->getContents());
            
            

    //         if ($result->success == true) {
    //             $resultId = $result->data->text->id;

    //             while (1) {

    //                 $response = $client->get("text/$resultId", [
    //                     'headers' => $headers,
    //                 ]);

    //                 $result = json_decode($response->getBody()->getContents());

    //                 if ($result->data->report != null) {
    //                     break;
    //                 }
    //             }

    //             $response = $client->get("text/report/$resultId", [
    //                 'headers' => $headers,
    //             ]);

    //             return response()->json(json_decode($response->getBody()->getContents()));
    //         } else {
    //             return response()->json(['message', 'Error in plagiarism.org api'], 401);
    //         }
    //     } catch (Exception $e) {
    //         dd($e);
    //         if ($e->hasResponse()) {
    //             $response = $e->getResponse();
    //             $statusCode = $response->getStatusCode();
    //             // Custom handling for specific status codes here...

    //             if ($statusCode == '404') {
    //                 // Handle a not found error
    //             } elseif ($statusCode == '500') {
    //                 // Handle a server error
    //             }

    //             $errorMessage = $response->getBody()->getContents();
    //             return response()->json(["status" => "error", "message" => json_decode($errorMessage)->message], 500);
    //             // Log the error message or handle it as required
    //         }
    //         return response()->json(['message' => $e->getMessage()], 500);
    //     }
    // }
    
    public function plagiarismCheck(Request $request)
{
   
    ini_set('max_execution_time', 240);
    
    
    if (auth()->user()->remaining_words < 1) {
        
        return redirect()->back()->with([
            'message' => __('You have no credits left. Please consider upgrading your plan.'),
            'type' => 'error',
        ]);
    }

    $settings = SettingTwo::first();

    if($settings->plagiarism_key == ""){
        return response()->json(['message' => 'Please input plagiarism api key'], 401);
    }

    try {
        $client = new Client([
            'base_uri' => 'https://api.gowinston.ai/v2/plagiarism/' // Ensure correct base URI
        ]);
        

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $settings->plagiarism_key,
        ];
        
        $data = [
            'language' => 'en',
            'text' => $request->text
        ];

        // POST request to the plagiarism check API
        $response = $client->post('', [
            'headers' => $headers,
            'json' => $data
        ]);
        
        $result = json_decode($response->getBody()->getContents());
        
        
        
        if($result->status == 200 )
        {
           
           
            $wordCount = str_word_count($request->text);
            
           
            
           userCreditDecreaseForWord(auth()->user(), $wordCount, 'plagiarismcheck');
              
            return response()->json(['$result' => $result]);
        }
         else {
            return response()->json(['message' => 'Error in plagiarism api'], 401);
        }
    } catch (Exception $e) {
       

        
        if ($e->hasResponse()) {
           
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

           $errorContent = json_decode($response->getBody()->getContents(), true);
           
            $errorMessage = $errorContent['response']['description'] ?? 'An unknown error occurred';
          
           
                return response()->json([
                "status" => "error",
                "message" => $errorMessage
            ], $statusCode);
            
        }
                   return response()->json([
                    "status" => "error",
                    "message" => $e->getMessage()
                ], 500);
        }
}


    public function detectAIContentCheck(Request $request)
    {
        
    ini_set('max_execution_time', 240);
    
    if (auth()->user()->remaining_words < 1) {
        
        return redirect()->back()->with([
            'message' => __('You have no credits left. Please consider upgrading your plan.'),
            'type' => 'error',
        ]);
    }

$settings = SettingTwo::first();

if (empty($settings->plagiarism_key)) {
    return response()->json(['message' => 'Please input plagiarism API key'], 401);
}

try {
    $client = new Client([
        'base_uri' => 'https://api.gowinston.ai/v2/ai-content-detection'
    ]);

    $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $settings->plagiarism_key,
    ];
    
    $data = [
        'text' => $request->text,
        'sentences' => true,
        'language' => 'en'
    ];

    // Send POST request
    $response = $client->post('', [
        'headers' => $headers,
        'json' => $data
    ]);

    $result = json_decode($response->getBody()->getContents(), true);
    
    

    if ($result['status'] == 200) {
        
         $wordCount = str_word_count($request->text);
        
        userCreditDecreaseForWord(auth()->user(), $wordCount, 'plagiarismcheck');
        
        return response()->json(['result' => $result]);
    } else {
        return response()->json(['message' => 'Error in AI content detection API'], 401);
    }
    } catch (Exception $e) {
       

        
        if ($e->hasResponse()) {
           
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

           $errorContent = json_decode($response->getBody()->getContents(), true);
           
           
            $errorMessage = $errorContent['description'] ?? 'An unknown error occurred';
          
           
                return response()->json([
                "status" => "error",
                "message" => $errorMessage
            ], $statusCode);
            
        }
                   return response()->json([
                    "status" => "error",
                    "message" => $e->getMessage()
                ], 500);
        }
}

    public function plagiarism()
    {
        return view('panel.user.openai.plagiarism.index');
    }

    public function detectAIContent()
    {
        return view('panel.user.openai.detectaicontent.index_detectaicontent');
    }

    public function plagiarismSave(Request $request)
    {
        $input = $request->input;
        $text = $request->text;
        $percent = $request->percent;

        $user = Auth::user();

        $post = OpenAIGenerator::where('slug', 'ai_plagiarism')->first();

        $entry = new UserOpenai();
        $entry->title = str($percent) . "% Plagiarism Document";
        $entry->slug = str()->random(7) . str($user->fullName())->slug() . '-workbook';
        $entry->user_id = Auth::id();
        $entry->openai_id = $post->id;
        $entry->input = $input;
        $entry->hash = str()->random(256);
        $entry->credits = 0;
        $entry->words = 0;
        $entry->output = $text;
        $entry->storage = "";
        $entry->response = $text;

        $entry->save();

        return response()->json(['success' => true]);
    }

    public function detectAIContentSave(Request $request)
    {
        $input = $request->input;
        $text = $request->text;
        $percent = $request->percent;

        $user = Auth::user();

        $post = OpenAIGenerator::where('slug', 'ai_content_detect')->first();

        $entry = new UserOpenai();
        $entry->title = str($percent) . "% AI Content Document";
        $entry->slug = str()->random(7) . str($user->fullName())->slug() . '-workbook';
        $entry->user_id = Auth::id();
        $entry->openai_id = $post->id;
        $entry->input = $input;
        $entry->hash = str()->random(256);
        $entry->credits = 0;
        $entry->words = 0;
        $entry->output = $text;
        $entry->storage = "";
        $entry->response = $text;

        $entry->save();

        return response()->json(['success' => true]);
    }

    public function plagiarismSetting(Request $request)
    {
        return view('panel.admin.settings.plagiarism_setting');
    }
    public function plagiarismSettingSave(Request $request)
    {
        $settings = SettingTwo::first();
        // TODO SETTINGS
        if (Helper::appIsNotDemo()) {
            $settings->plagiarism_key = $request->plagiarism_api_key;
            $settings->save();
        }
        return response()->json([], 200);
    }
    // public function serperapiTest(){
    //     try {
    //         $settings = SettingTwo::first();
    //         if ($settings->serper_api_key == "") {
    //             echo "You must provide Serper API Key.";
    //             return;
    //         }
    //         $client = new Client();
    //         $response = $client->post('https://google.serper.dev/search', [
    //             'headers' => [
    //                 'X-API-KEY' => $settings->serper_api_key,
    //                 'Content-Type' => 'application/json',
    //             ],
    //             'json' => [
    //                 'q' => 'Coffee',
    //             ],
    //         ]);
    //         $responseData = json_decode($response->getBody(), true);
    //         echo ' <br>'.$settings->serper_api_key.' - SUCCESS <br><hr> Example about "Coffee": <br>'. $responseData['organic'][0]['snippet'] .'<br>' ;
    //     } catch (\Exception $e) {
    //         echo $e->getMessage().' - '.$settings->serper_api_key.' -FAILED <br>';
    //     }
    // }
}
