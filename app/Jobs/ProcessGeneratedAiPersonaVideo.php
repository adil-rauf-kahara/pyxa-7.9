<?php

namespace App\Jobs;

use App\Models\UserOpenai;
use App\Extensions\AiPersona\System\Services\AiPersonaService;
use App\Extensions\AiPersona\System\Models\AiPersona;
use App\Models\SettingTwo;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;

class ProcessGeneratedAiPersonaVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $videoId;
    public $inputText;
    public $userId;
    public $teamId;
    public $fullName;
    public $url;

    public function __construct($videoId, $inputText, $userId, $teamId,$fullName, $url)
    {
        $this->videoId = $videoId;
        $this->inputText = $inputText;
        $this->userId = $userId;
        $this->teamId = $teamId;
        $this->fullName = $fullName;
        $this->url = $url;
    }

    public function handle()
    {
        $service = new AiPersonaService();
        $settingsTwo = SettingTwo::getCache();
        $imageStorage = $settingsTwo->ai_image_storage;

        // Poll until video is ready (you can limit retries or add delay)
        $attempts = 0;
        $maxAttempts = 10000;
        $delaySeconds = 10;

        while ($attempts < $maxAttempts) {
            sleep($delaySeconds);
            $status = $service->retrieveVideo($this->videoId);
            // dd

            if (isset($status['data']['status']) && $status['data']['status'] === 'completed') {
                $videoUrl = $status['data']['video_url'] ?? null;

                if ($videoUrl) {
                    $videoContents = file_get_contents($videoUrl);
                    $videoName = $this->videoId . '.mp4';
                    $localPath = 'uploads/' . $videoName;

                    if ($imageStorage === 's3') {
                        file_put_contents(public_path($localPath), $videoContents);
                        try {
                            $uploadedFile = new File(public_path($localPath));
                            $awsPath = Storage::disk('s3')->put('', $uploadedFile);
                            unlink(public_path($localPath));
                            $finalPath = Storage::disk('s3')->url($awsPath);
                        } catch (Exception $e) {
                            throw new \Exception('AWS Upload Failed: ' . $e->getMessage());
                        }
                        $storageType = UserOpenai::STORAGE_AWS;
                    } else {
                        Storage::disk('public')->put($videoName, $videoContents);
                        $finalPath = '/uploads/' . $videoName;
                        $storageType = UserOpenai::STORAGE_LOCAL;
                    }

                    AiPersona::create([
                        'user_id'   => $this->userId,
                        'avatar_id' => $this->videoId,
                        'status'    => 'completed',
                        'file_path' => $finalPath,
                    ]);

                    UserOpenai::create([
                        'team_id'   => $this->teamId,
                        'title'     => __('AI Persona Video'),
                        'slug'      => Str::random(7) . Str::slug($this->fullName) . '-workbsook',
                        'user_id'   => $this->userId,
                        'openai_id' => \App\Models\OpenAIGenerator::where('slug', 'ai_persona')->first()->id,
                        'input'     => $this->url,
                        'response'  => 'VIDEO',
                        'output'    => $finalPath,
                        'hash'      => Str::random(256),
                        'credits'   => 5,
                        'words'     => 0,
                        'storage'   => $storageType,
                        'payload'   => $this->inputText,
                    ]);

                    return;
                }
            }

            $attempts++;
        }

        // Log failure or notify admin/user
        throw new \Exception('Video generation failed after max attempts.');
    }
}

