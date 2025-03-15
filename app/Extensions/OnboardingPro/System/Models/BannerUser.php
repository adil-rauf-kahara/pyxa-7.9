<?php

namespace App\Extensions\OnboardingPro\System\Models;

use Illuminate\Database\Eloquent\Model;

class BannerUser extends Model
{
    protected $table = 'banner_user';

    protected $fillable = [
        'user_id',
        'banner_id',
    ];
}
