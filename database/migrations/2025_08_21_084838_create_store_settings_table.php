<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_url', 500);
            $table->string('about_image_url', 500);
            $table->text('about_description');
            $table->text('terms_and_conditions');
            $table->string('facebook_url');
            $table->string('whatsapp_number', 20);
            $table->string('phone_number', 20);
            $table->string('second_phone_number', 20);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_settings');
    }
};
