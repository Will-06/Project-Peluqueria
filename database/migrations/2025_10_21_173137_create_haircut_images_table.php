<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('haircut_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('haircut_id')->constrained()->onDelete('cascade');
            $table->string('image_url', 500);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('haircut_images');
    }
};