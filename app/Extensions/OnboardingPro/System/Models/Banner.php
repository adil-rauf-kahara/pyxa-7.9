<?php

namespace App\Extensions\OnboardingPro\System\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banner';

    protected $fillable = [
        'description',
        'background_color',
        'text_color',
        'status',
        'permanent',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if ($model->isDirty('status') && $model->status == 1) {
                Banner::query()->where('status', 1)->update(['status' => 0]);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('status') && $model->status == 1) {
                Banner::query()->where('status', 1)->update(['status' => 0]);
            }
        });
    }
}
