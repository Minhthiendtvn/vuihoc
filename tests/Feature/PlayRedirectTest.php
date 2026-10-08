<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Kiểm chứng fix: bấm "Chơi" khi chưa có hồ sơ học viên hoạt động
 * phải redirect về nơi phù hợp kèm thông báo — KHÔNG đá vòng về trang chủ.
 */
class PlayRedirectTest extends TestCase
{
    use DatabaseTransactions;

    private function publishedLesson(): Lesson
    {
        $lesson = Lesson::where('status', 'published')->first();
        $this->assertNotNull($lesson, 'Cần có bài học published trong DB seed.');

        return $lesson;
    }

    public function test_khach_chua_dang_nhap_duoc_dua_ve_trang_dang_nhap()
    {
        $lesson = $this->publishedLesson();

        $response = $this->post("/bai-hoc/{$lesson->slug}/choi/quiz");

        $response->assertRedirect(route('auth.login'));
    }

    public function test_phu_huynh_chua_chon_con_duoc_dua_ve_trang_con_cua_toi()
    {
        $parent = User::where('role', 'parent')->first();
        $this->assertNotNull($parent, 'Cần có tài khoản phụ huynh trong DB seed.');
        $lesson = $this->publishedLesson();

        $response = $this->actingAs($parent)
            ->post("/bai-hoc/{$lesson->slug}/choi/quiz");

        $response->assertRedirect(route('identity.children.index'));
        $response->assertSessionHas('info');
    }

    public function test_giao_vien_duoc_dua_ve_trang_chu_kem_thong_bao()
    {
        $teacher = User::where('role', 'teacher')->first();
        $this->assertNotNull($teacher, 'Cần có tài khoản giáo viên trong DB seed.');
        $lesson = $this->publishedLesson();

        $response = $this->actingAs($teacher)
            ->post("/bai-hoc/{$lesson->slug}/choi/quiz");

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('info');
    }
}
