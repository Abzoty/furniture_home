<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo_url',
        'about_image_url',
        'about_description',
        'terms_and_conditions',
        'facebook_url',
        'whatsapp_number',
        'phone_number',
        'second_phone_number'
    ];
}
