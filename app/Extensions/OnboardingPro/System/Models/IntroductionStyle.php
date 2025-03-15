<?php

namespace App\Extensions\OnboardingPro\System\Models;

use Illuminate\Database\Eloquent\Model;

class IntroductionStyle extends Model
{
    protected $table = 'introduction_style';

    protected $fillable = [
        'title_size',
        'description_size',
        'background_color',
        'title_color',
        'description_color',
        'dark_background_color',
        'dark_title_color',
        'dark_description_color',
    ];
}
