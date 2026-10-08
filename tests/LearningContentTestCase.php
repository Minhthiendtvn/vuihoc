<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

abstract class LearningContentTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32)), 'database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Storage::fake('local');
        foreach (['101000_create_subjects', '102000_create_topics', '103000_create_skills', '104000_create_lessons', '105000_create_questions'] as $migration) {
            (require base_path('database/migrations/2026_10_07_'.$migration.'_table.php'))->up();
        }
        Schema::table('lessons', fn (Blueprint $table) => $table->unsignedTinyInteger('grade')->nullable());
        Schema::table('questions', fn (Blueprint $table) => $table->unsignedTinyInteger('grade')->nullable());
        (require base_path('database/migrations/2026_10_08_100000_add_summary_to_lessons_table.php'))->up();
        (require base_path('database/migrations/2026_10_08_110000_create_learning_content_imports_table.php'))->up();
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        DB::table('users')->insert(['id' => 1, 'name' => 'Giữ nguyên']);
    }
}
