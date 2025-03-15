<?php

namespace App\Extensions\Cloudflare\System\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloudflareR2Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'CLOUDFLARE_R2_ACCESS_KEY_ID'     => 'required',
            'CLOUDFLARE_R2_SECRET_ACCESS_KEY' => 'required',
            'CLOUDFLARE_R2_BUCKET'            => 'required',
            'CLOUDFLARE_R2_DEFAULT_REGION'    => 'required',
            'CLOUDFLARE_R2_ENDPOINT'          => 'required|url',
            'CLOUDFLARE_R2_URL'               => 'required|url',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'CLOUDFLARE_R2_URL' => $this->input('CLOUDFLARE_R2_URL') ?: $this->input('CLOUDFLARE_R2_ENDPOINT'),
        ]);
    }
}
