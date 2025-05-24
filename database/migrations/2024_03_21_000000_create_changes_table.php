<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->json('old');
            $table->json('new');
            $table->unsignedBigInteger('auth_id')->nullable();
            $table->timestamp('created_at', 3);

            $table->index(['created_at', 'model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changes');
    }
};
