<?php

namespace App\Services;

use App\Models\SettingTwo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ElevenlabsService
{
    public const URL = 'https://api.elevenlabs.io/v1/voices';

    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = SettingTwo::query()->first()?->elevenlabs_api_key;
    }

    public function getVoices(): array|Collection
{
    $response = Http::withHeaders([
        'xi-api-key' => $this->apiKey,
    ])->timeout(30)
        ->get(self::URL);

    if ($response->failed()) {
        return [];
    }

    $data = $response->json();

    // Filter out voices where 'samples' is null and return the rest as they are
    return collect($data['voices'])
        ->filter(fn($voice) => is_null($voice['samples']))
        ->values(); // Reset array keys after filtering
}


}
