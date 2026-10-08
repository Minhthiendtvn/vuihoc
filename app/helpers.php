<?php

use App\Models\LearnerProfile;

if (! function_exists('is_admin')) {
    /**
     * User hiện tại có phải admin không.
     * Admin có toàn quyền: qua mọi cổng kiểm tra role và kiểm tra sở hữu.
     */
    function is_admin(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }
}

if (! function_exists('active_profile')) {
    /**
     * Hồ sơ học viên đang hoạt động của user hiện tại.
     *
     * - role learner: profile của chính user.
     * - role parent: LearnerProfile theo session('active_profile_id'),
     *   và profile đó phải thuộc về parent này (parent_id = user id).
     * - role khác: null.
     */
    function active_profile(): ?LearnerProfile
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        if ($user->role === 'learner') {
            return $user->learnerProfile;
        }

        if ($user->role === 'parent') {
            $profileId = session('active_profile_id');

            if (! $profileId) {
                return null;
            }

            return LearnerProfile::where('id', $profileId)
                ->where('parent_id', $user->id)
                ->first();
        }

        return null;
    }
}
