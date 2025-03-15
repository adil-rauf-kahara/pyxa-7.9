<?php

namespace App\Extensions\OnboardingPro\System\Models;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $table = 'survey';

    protected $fillable = [
        'description',
        'background_color',
        'text_color',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if ($model->isDirty('status') && $model->status == 1) {
                Survey::query()->where('status', 1)->update(['status' => 0]);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('status') && $model->status == 1) {
                Survey::query()->where('status', 1)->update(['status' => 0]);
            }
        });
    }
}
