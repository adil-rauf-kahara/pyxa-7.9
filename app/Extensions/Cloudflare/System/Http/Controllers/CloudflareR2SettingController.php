<?php

namespace App\Extensions\Cloudflare\System\Http\Controllers;

use App\Extensions\Cloudflare\System\Http\Requests\CloudflareR2Request;
use App\Http\Controllers\Controller;
use Exception;

class CloudflareR2SettingController extends Controller
{
    public function index()
    {
        return view('cloudflare::settings');
    }

    public function update(CloudflareR2Request $request): ?\Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $request['CLOUDFLARE_R2_URL'] = $request['CLOUDFLARE_R2_URL'] ?: $request['CLOUDFLARE_R2_ENDPOINT'];

        try {
            \App\Helpers\Classes\Helper::setEnv($data);

            return response()->json([
                'message' => 'Cloudflare R2 settings updated successfully!',
                'type'    => 'success',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'type'    => 'error',
            ], 500);
        }
    }
}
