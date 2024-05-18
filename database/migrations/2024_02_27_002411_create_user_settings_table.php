<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('setting_id');
            $table->unsignedBigInteger('value_id');

            $table->unique(['user_id', 'setting_id']);

            $table->timestamps();
            $table->foreign('value_id')->references('id')->on('settings_values')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('setting_id')->references('id')->on('settings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_settings');
    }
};
