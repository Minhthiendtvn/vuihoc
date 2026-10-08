<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('display_name');
            $table->string('avatar_emoji', 16)->default('🦊');
            $table->tinyInteger('grade')->unsigned()->nullable();
            $table->smallInteger('daily_goal')->unsigned()->default(3);
            $table->enum('font_size', ['normal', 'large', 'xlarge'])->default('normal');
            $table->integer('total_xp')->unsigned()->default(0);
            $table->smallInteger('level')->unsigned()->default(1);
            $table->integer('current_streak')->unsigned()->default(0);
            $table->integer('longest_streak')->unsigned()->default(0);
            $table->date('last_play_date')->nullable();
            $table->timestamps();

            $table->index('user_id', 'lp_user');
            $table->index('parent_id', 'lp_parent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learner_profiles');
    }
};
