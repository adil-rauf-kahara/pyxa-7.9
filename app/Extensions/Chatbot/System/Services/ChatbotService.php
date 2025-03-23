<?php

namespace App\Extensions\Chatbot\System\Services;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotAvatar;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ChatbotService
{
    public function agentConversations(array $chatbots, ?string $orderBy = null): Collection|array
    {
        return ChatbotConversation::query()
            ->with(['histories', 'lastMessage'])
            ->whereNotNull('connect_agent_at')
            ->whereIn('chatbot_id', $chatbots)
            ->when($orderBy, function (Builder $query) use ($orderBy) {
                $query->orderBy($orderBy ?: 'id', 'desc');
            })
            ->get();
    }

    public function conversations(array $chatbots, ?string $orderBy = null): Collection|array
    {
        return ChatbotConversation::query()
            ->with(['histories', 'lastMessage'])
            ->whereIn('chatbot_id', $chatbots)
            ->when($orderBy, function (Builder $query) use ($orderBy) {
                $query->orderBy($orderBy ?: 'id', 'desc');
            })
            ->get();
    }

    public function update(Model|int $model, array $data): Model
    {
        if (is_int($model)) {
            $model = $this->query()->findOrFail($model);
        }

        $model->update($data);

        return $model;
    }

    public function avatars(): Collection|array
    {
        return ChatbotAvatar::query()
            ->where(function (Builder $query) {
                return $query->where('user_id', Auth::id())->orWhereNull('user_id');
            })
            ->get();
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return Chatbot::query();
    }
}
