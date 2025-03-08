<?php

declare(strict_types=1);

namespace App\Domains\Engine\Services;

use App\Domains\Entity\Enums\EntityEnum;
use App\Helpers\Classes\ApiHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FalAIService
{
    public const GENERATE_ENDPOINT = 'https://queue.fal.run/fal-ai/%s';

    public const CHECK_ENDPOINT = 'https://queue.fal.run/fal-ai/%s/requests/%s';

    public const HAIPER_URL = 'https://queue.fal.run/fal-ai/haiper-video-v2/image-to-video';

    public const IDEOGRAM_URL = 'https://queue.fal.run/fal-ai/ideogram/v2';

    public const KLING_URL = 'https://queue.fal.run/fal-ai/kling-video/v1/standard/text-to-video';

    public const LUMA_URL = 'https://queue.fal.run/fal-ai/luma-dream-machine';

    public const MINIMAX_URL = 'https://queue.fal.run/fal-ai/minimax-video';

    public const VEO_2_URL = 'https://queue.fal.run/fal-ai/veo2';

    public static function generate($prompt, ?EntityEnum $entity = EntityEnum::FLUX_PRO)
    {
        $entityValue = (setting('fal_ai_default_model') ?: $entity?->value);

        $entityValue = EntityEnum::fromSlug($entityValue)->value;

        $url = sprintf(self::GENERATE_ENDPOINT, $entityValue);

        $http = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
        ])->post($url, [
            'prompt' => $prompt,
        ]);

        if (($http->status() === 200) && $requestId = $http->json('request_id')) {
            return $requestId;
        }

        $detail = $http->json('detail');

        throw new RuntimeException(__($detail ?: 'Check your FAL API key.'));
    }

    public static function check($uuid, EntityEnum $entity = EntityEnum::FLUX_PRO): ?array
    {
        $entityValue = (setting('fal_ai_default_model') ?: $entity->value);

        $enum = EntityEnum::fromSlug($entityValue);

        if ($enum === EntityEnum::FLUX_SCHNELL) {
            $entityValue = 'flux-pro';
        }

        if ($enum === EntityEnum::FLUX_PRO_1_1 || $enum === EntityEnum::FLUX_PRO) {
            $entityValue = 'flux';
        }

        $url = sprintf(self::CHECK_ENDPOINT, $entityValue, $uuid);

        $http = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
        ])->get($url);

        if (($images = $http->json('images')) && is_array($images)) {
            $image = Arr::first($images);

            return [
                'image' => $image,
                'size'  => data_get($image, 'width') . 'x' . data_get($image, 'height'),
            ];
        }

        return null;
    }

    public static function ideogramGenerate(string $prompt)
    {
        $http = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
        ])->post(self::IDEOGRAM_URL,
            [
                'prompt'    => $prompt,
            ]);

        if (($http->status() === 200) && $requestId = $http->json('request_id')) {
            return $requestId;
        }

        $detail = $http->json('detail');

        throw new RuntimeException(__($detail ?: 'Check your FAL API key.'));
    }

    public static function haiperGenerate(string $prompt, string $imageUrl)
    {
        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
        ])
            ->post(self::HAIPER_URL,
                [
                    'prompt'    => $prompt,
                    'image_url' => $imageUrl,
                ]);

        return $response->json();
    }

    public static function minimaxGenerate(string $prompt)
    {
        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
        ])
            ->post(self::MINIMAX_URL,
                [
                    'prompt' => $prompt,
                ]);

        return $response->json();
    }

    public static function klingGenerate(string $prompt)
    {
        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
        ])
            ->post(self::KLING_URL,
                [
                    'prompt' => $prompt,
                ]);

        return $response->json();
    }

    public static function lumaGenerate(string $prompt)
    {
        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
        ])
            ->post(self::LUMA_URL,
                [
                    'prompt' => $prompt,
                ]);

        return $response->json();
    }

    public static function veo2Generate(string $prompt)
    {
        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
        ])
            ->post(self::VEO_2_URL,
                [
                    'prompt' => $prompt,
                ]);

        return $response->json();
    }

    public static function getStatus($url)
    {
        ini_set('max_execution_time', 440);
        set_time_limit(0);

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Key ' . ApiHelper::setFalAIKey(),
        ])
            ->get($url);

        return $response->json();
    }
}
