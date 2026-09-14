<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_registries', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type')->index();
            $table->string('slug')->unique();
            $table->json('structure');
            $table->string('ai_provider')->nullable()->index();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_registries');
    }
};