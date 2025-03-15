<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Resources\Admin;

use App\Extensions\Chatbot\System\Http\Resources\Api\ChatbotHistoryResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ChatbotConversationResource extends JsonResource
{
    public function toArray(Request $request): array|Arrayable|JsonSerializable
    {
        return [
            'id'          => $this->getAttribute('id'),
            'chatbot'     => ChatbotResource::make($this->getAttribute('chatbot')),
            'lastMessage' => $this->getAttribute('lastMessage') ? ChatbotHistoryResource::make($this->getAttribute('lastMessage')) : [
                'read_at' => now(),
            ],
            'chatbot_id'  => $this->getAttribute('chatbot_id'),
            'created_at'  => $this->getAttribute('created_at'),
            'histories'   => ChatbotHistoryResource::collection($this->getAttribute('histories')),
        ];
    }
}
