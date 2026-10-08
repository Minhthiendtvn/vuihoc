<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xp_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('learner_profiles')->cascadeOnDelete();
            $table->string('source', 32);
            $table->integer('amount');
            $table->text('meta_json')->nullable();
            $table->timestamps();

            $table->index('profile_id', 'xp_profile');
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon', 16);
            $table->string('criteria', 64);
            $table->integer('threshold')->unsigned()->default(1);
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });

        Schema::create('profile_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('learner_profiles')->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained('badges')->cascadeOnDelete();
            $table->timestamp('earned_at');
            $table->timestamps();

            $table->unique(['profile_id', 'badge_id'], 'uq_pb_profile_badge');
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('learner_profiles')->cascadeOnDelete();
            $table->enum('target_type', ['lesson', 'topic']);
            $table->unsignedBigInteger('target_id');
            $table->timestamps();

            $table->unique(['profile_id', 'target_type', 'target_id'], 'uq_fav_unique');
        });

        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users');
            $table->string('name');
            $table->string('code', 16)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('class_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('profile_id')->constrained('learner_profiles')->cascadeOnDelete();
            $table->timestamp('joined_at');
            $table->timestamps();

            $table->unique(['classroom_id', 'profile_id'], 'uq_cm_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_members');
        Schema::dropIfExists('classrooms');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('profile_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('xp_events');
    }
};
