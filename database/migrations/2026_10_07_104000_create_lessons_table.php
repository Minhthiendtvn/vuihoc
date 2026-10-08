<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('objective')->nullable();
            $table->enum('difficulty', ['de', 'trung_binh', 'kho'])->default('trung_binh');
            $table->smallInteger('duration_minutes')->unsigned()->default(10);
            $table->text('instructions')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->boolean('is_demo')->default(false);
            $table->timestamps();

            $table->index('skill_id', 'ls_skill');
            $table->index('status', 'ls_status');
            $table->index('slug', 'ls_slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
