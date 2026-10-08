<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon', 16)->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->tinyInteger('grade_min')->unsigned()->default(6);
            $table->tinyInteger('grade_max')->unsigned()->default(12);
            $table->boolean('is_published')->default(true);
            $table->boolean('is_demo')->default(false);
            $table->timestamps();

            $table->index('subject_id', 'tp_subject');
            $table->index('slug', 'tp_slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
