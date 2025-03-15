<?php

namespace App\Extensions\OnboardingPro\System\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyUser extends Model
{
    protected $table = 'survey_user';

    protected $fillable = [
        'user_id',
        'survey_id',
        'point',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
