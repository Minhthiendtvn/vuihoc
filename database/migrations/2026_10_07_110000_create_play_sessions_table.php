<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('play_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('learner_profiles')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons');
            $table->enum('game_type', ['quiz', 'matching', 'sort', 'fill']);
            $table->string('token', 64)->unique();
            $table->enum('status', ['started', 'finished', 'expired'])->default('started');
            $table->text('question_ids_json')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->timestamp('finished_at')->nullable();
            $table->integer('score')->default(0);
            $table->integer('max_score')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0);
            $table->integer('duration_seconds')->default(0);
            $table->integer('xp_earned')->default(0);
            $table->text('answers_json')->nullable();
            $table->timestamps();

            $table->index('profile_id', 'ps_profile');
            $table->index('status', 'ps_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('play_sessions');
    }
};
