<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->enum('game_type', ['quiz', 'matching', 'sort', 'fill']);
            $table->text('prompt');
            $table->text('explanation')->nullable();
            $table->enum('difficulty', ['de', 'trung_binh', 'kho'])->default('trung_binh');
            $table->smallInteger('points')->unsigned()->default(10);
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_demo')->default(false);
            $table->timestamps();

            $table->index('lesson_id', 'q_lesson');
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('question_id', 'qo_question');
        });

        Schema::create('matching_pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('left_text');
            $table->string('right_text');
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('question_id', 'mp_question');
        });

        Schema::create('sort_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('item_text');
            $table->string('category', 64);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('question_id', 'si_question');
        });

        Schema::create('fill_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->smallInteger('blank_index')->unsigned();
            $table->string('answer_text');
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('question_id', 'fa_question');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fill_answers');
        Schema::dropIfExists('sort_items');
        Schema::dropIfExists('matching_pairs');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
    }
};
