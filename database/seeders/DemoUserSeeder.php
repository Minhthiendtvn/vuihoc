<?php

namespace Database\Seeders;

use App\Models\LearnerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin
        User::updateOrCreate(
            ['email' => 'admin@vuihoc.local'],
            ['name' => 'Quản trị viên', 'password' => Hash::make('Admin@123'), 'role' => 'admin']
        );

        // 2. Giáo viên
        User::updateOrCreate(
            ['email' => 'giaovien@vuihoc.local'],
            ['name' => 'Cô Giáo Demo', 'password' => Hash::make('Demo@123'), 'role' => 'teacher']
        );

        // 3. Phụ huynh + 1 hồ sơ con "Bé An" lớp 6
        $parent = User::updateOrCreate(
            ['email' => 'phuhuynh@vuihoc.local'],
            ['name' => 'Phụ huynh Demo', 'password' => Hash::make('Demo@123'), 'role' => 'parent']
        );

        LearnerProfile::updateOrCreate(
            ['user_id' => null, 'parent_id' => $parent->id, 'display_name' => 'Bé An'],
            [
                'avatar_emoji' => '🐰', 'grade' => 6, 'daily_goal' => 3,
                'font_size' => 'normal', 'total_xp' => 0, 'level' => 1,
                'current_streak' => 0, 'longest_streak' => 0, 'last_play_date' => null,
            ]
        );

        // 4. Học sinh (learner) + profile "Minh" lớp 7
        $learner = User::updateOrCreate(
            ['email' => 'hocsinh@vuihoc.local'],
            ['name' => 'Học sinh Demo', 'password' => Hash::make('Demo@123'), 'role' => 'learner']
        );

        LearnerProfile::updateOrCreate(
            ['user_id' => $learner->id],
            [
                'parent_id' => null, 'display_name' => 'Minh',
                'avatar_emoji' => '🦊', 'grade' => 7, 'daily_goal' => 3,
                'font_size' => 'normal', 'total_xp' => 0, 'level' => 1,
                'current_streak' => 0, 'longest_streak' => 0, 'last_play_date' => null,
            ]
        );
    }
}
