# VuiHoc — CONTRACT KỸ THUẬT (Phase 1 → Phase 2)

Tài liệu BẮT BUỘC cho 5 agent phase 2 (Identity, Library, Gameplay, Progress, Admin) và agent API phase 3.
Agent nào cũng phải đọc kỹ file này trước khi viết code. Nội dung mẫu tự viết 100%, KHÔNG copy từ nguồn khác.

Công nghệ: Laravel 11 + MySQL (MariaDB), server render Blade + Alpine.js, **không React/Node ở web**.
Gói demo chạy trên Laragon, không cần Node/Composer phía Thiện.

---

## (a) Danh sách bảng + cột chính

| Bảng | Cột chính |
|---|---|
| `users` | id, name, email unique, password, role enum(admin,teacher,parent,learner) default learner, avatar_path, email_verified_at, remember_token |
| `learner_profiles` | id, user_id nullable unique FK users, parent_id nullable FK users, display_name, avatar_emoji default 🦊, grade (6–12), daily_goal default 3, font_size enum(normal,large,xlarge), total_xp, level, current_streak, longest_streak, last_play_date |
| `subjects` | id, name, slug unique, icon, color, description, sort_order, is_published, is_demo |
| `topics` | id, subject_id FK cascade, name, slug, description, icon, sort_order, grade_min default 6, grade_max default 12, is_published, is_demo |
| `skills` | id, topic_id FK cascade, name, slug, description, sort_order, is_demo |
| `lessons` | id, skill_id FK cascade, title, slug, objective, difficulty enum(de,trung_binh,kho), duration_minutes default 10, instructions, sort_order, status enum(draft,published) default draft, is_demo |
| `questions` | id, lesson_id FK cascade, game_type enum(quiz,matching,sort,fill), prompt, explanation, difficulty, points default 10, sort_order, is_demo |
| `question_options` | id, question_id FK cascade, option_text, is_correct bool, sort_order — dùng cho game quiz |
| `matching_pairs` | id, question_id FK cascade, left_text, right_text, sort_order — dùng cho game matching |
| `sort_items` | id, question_id FK cascade, item_text, category (tên nhóm cần phân loại, hoặc số thứ tự dạng chuỗi cho game sắp xếp), sort_order — dùng cho game sort |
| `fill_answers` | id, question_id FK cascade, blank_index (vị trí `___` trong prompt, bắt đầu từ 0), answer_text, sort_order — dùng cho game fill |
| `play_sessions` | id, profile_id FK cascade, lesson_id FK, game_type, token unique(64), status enum(started,finished,expired), question_ids_json, started_at, expires_at, finished_at, score, max_score, accuracy decimal(5,2), duration_seconds, xp_earned, answers_json |
| `xp_events` | id, profile_id FK cascade, source varchar(32), amount, meta_json |
| `badges` | id, name, slug unique, description, icon, criteria varchar(64) — mã: first_play, streak_3, streak_7, streak_30, xp_1000, xp_5000, perfect_5, explorer_3, scholar_10; threshold, is_demo |
| `profile_badges` | id, profile_id FK cascade, badge_id FK cascade, earned_at; unique(profile_id,badge_id) tên `uq_pb_profile_badge` |
| `favorites` | id, profile_id FK cascade, target_type enum(lesson,topic), target_id; unique(profile_id,target_type,target_id) tên `uq_fav_unique` |
| `classrooms` | id, owner_id FK users, name, code unique(16), description |
| `class_members` | id, classroom_id FK cascade, profile_id FK cascade, joined_at; unique(classroom_id,profile_id) tên `uq_cm_unique` |

**Quy ước DB:** tên index tường minh NGẮN GỌN (MariaDB giới hạn 64 ký tự). FK đặt đúng cột như trên.

---

## (b) Models + relations + scopes

- `User` — fillable: name, email, password, role, avatar_path; casts: email_verified_at datetime, password hashed.
  - `learnerProfile()` hasOne LearnerProfile (user_id)
  - `childProfiles()` hasMany LearnerProfile (parent_id)
  - `classrooms()` hasMany Classroom (owner_id)
- `LearnerProfile` — fillable đầy đủ các cột (trừ id/timestamps); casts int/date.
  - `user()` belongsTo User (user_id); `parent()` belongsTo User (parent_id)
  - `playSessions()`, `favorites()`, `xpEvents()` hasMany (profile_id)
  - `badges()` belongsToMany Badge qua `profile_badges`, withPivot('earned_at'), withTimestamps()
- `Subject` — `topics()` hasMany; `scopePublished()` (is_published = true)
- `Topic` — `subject()` belongsTo; `skills()` hasMany; `scopePublished()`; `scopeForGrade($grade)` (grade_min ≤ grade ≤ grade_max)
- `Skill` — `topic()` belongsTo; `lessons()` hasMany
- `Lesson` — `skill()` belongsTo; `questions()` hasMany; `scopePublished()` (status = 'published')
- `Question` — `lesson()` belongsTo; `options()` hasMany QuestionOption; `pairs()` hasMany MatchingPair; `sortItems()` hasMany SortItem; `fillAnswers()` hasMany FillAnswer
- `QuestionOption`, `MatchingPair`, `SortItem`, `FillAnswer` — mỗi model có `question()` belongsTo Question
- `PlaySession` — `profile()` belongsTo LearnerProfile (profile_id); `lesson()` belongsTo Lesson; helper `isPlayable()`: status = started && expires_at còn hiệu lực
- `XpEvent` — `profile()` belongsTo LearnerProfile
- `Badge` — `profiles()` belongsToMany LearnerProfile qua `profile_badges`
- `ProfileBadge` — `profile()`, `badge()` belongsTo
- `Favorite` — `profile()` belongsTo LearnerProfile
- `Classroom` — `owner()` belongsTo User (owner_id); `profiles()` belongsToMany LearnerProfile qua `class_members` (withPivot joined_at, withTimestamps); `members()` hasMany ClassMember
- `ClassMember` — `classroom()` belongsTo Classroom; `profile()` belongsTo LearnerProfile

---

## (c) Bảng phân công route

| File route | URL chính | Chủ sở hữu |
|---|---|---|
| `routes/web.php` | (không đặt route ở đây — chỉ comment chỉ dẫn) | Phase 1 |
| `routes/web_identity.php` | đăng ký/đăng nhập/đăng xuất, quên mật khẩu (link token), hồ sơ học viên (tạo/sửa, đổi tên/avatar/emoji/mục tiêu ngày/cỡ chữ), phụ huynh chuyển hồ sơ con qua `session('active_profile_id')`, trang quản lý con | **agent Identity** |
| `routes/web_library.php` | `/` trang chủ, `/thu-vien`, `/mon-hoc/{slug}`, `/chu-de/{slug}`, `/bai-hoc/{slug}`, `/yeu-thich` | **agent Library** |
| `routes/web_gameplay.php` | `POST /bai-hoc/{lesson}/choi/{game_type}`, `GET /choi/{token}`, `POST /choi/{token}/nop-bai`, `GET /choi/{token}/ket-qua` | **agent Gameplay** |
| `routes/web_progress.php` | `/tien-do`, `/phu-huynh`, `/lop*` (tạo lớp, tham gia bằng mã, xem thành viên) | **agent Progress** |
| `routes/web_admin.php` | `/admin/*` — bọc `middleware('role:admin')`: quản lý môn/chủ đề/kỹ năng/bài học/câu hỏi (4 loại game), huy hiệu, người dùng, nội dung mẫu | **agent Admin** |
| `routes/api.php` | `/api/v1/*` (Sanctum đã cài sẵn, phase 2 không đụng tới) | **agent API (phase 3)** |

**Quy ước đặt tên route:** `tên-file.tên-hành-động`, ví dụ: `library.index`, `library.subject`, `gameplay.show`, `gameplay.submit`, `gameplay.result`, `gameplay.start`, `progress.index`, `parent.index`, `admin.subjects.index`...

**Middleware:** alias `role` (App\Http\Middleware\CheckRole) — kiểm tra `Auth::user()->role` nằm trong danh sách tham số (`'role:admin'`, `'role:teacher,parent'`...), sai → abort 403. Đã đăng ký trong `bootstrap/app.php`.

**Helper:** `active_profile(): ?LearnerProfile` (trong `app/helpers.php`, đã đăng ký composer autoload files):
- role learner → profile của chính user;
- role parent → LearnerProfile theo `session('active_profile_id')` và phải có `parent_id` = user id;
- các role khác → null.

---

## (d) Luồng chơi game bằng play token (agent Gameplay)

1. **Start** — `POST /bai-hoc/{lesson}/choi/{game_type}` (lesson đã published):
   - Tạo `play_sessions`: `token` = chuỗi random 40 ký tự (Str::random(40)), `expires_at` = now + 30 phút, `question_ids_json` = danh sách id câu hỏi của bài (json), `status` = started.
   - Redirect về `GET /choi/{token}`.
2. **Show** — `GET /choi/{token}`:
   - Kiểm tra: session tồn tại, `profile_id` == active_profile()->id, `status` = started, chưa quá `expires_at`; nếu hết hạn → đánh dấu `expired`.
   - **KHÔNG trả đáp án đúng về client:**
     - quiz: chỉ trả danh sách options (id + option_text), KHÔNG có `is_correct`;
     - matching: xáo trộn (shuffle) 2 cột left/right độc lập;
     - sort: xáo trộn items, KHÔNG trả `category`;
     - fill: chỉ trả `prompt` (chứa `___`), KHÔNG trả `fill_answers`.
3. **Submit** — `POST /choi/{token}/nop-bai`:
   - Kiểm tra lại: token tồn tại, đúng chủ sở hữu, status = started, chưa hết hạn, và thời gian làm bài ≥ 5 giây (so với `started_at`).
   - **Chấm server-side** theo game_type (so sánh với dữ liệu DB, không tin client):
     - quiz: option được chọn có `is_correct` = true;
     - matching: cặp left→right khớp với `matching_pairs`;
     - sort: thứ tự items khớp `sort_order`/`category`;
     - fill: đáp án điền khớp `answer_text` (so khớp không phân biệt hoa/thường, trim khoảng trắng).
   - Cập nhật: `status` = finished, `finished_at`, `score`, `max_score`, `accuracy`, `duration_seconds`, `answers_json`.
   - Gọi `GamificationService::recordPlay($profile, $session)` để cộng XP, tính cấp độ, streak, huy hiệu.
4. **Result** — `GET /choi/{token}/ket-qua`: hiển thị điểm, độ chính xác, XP nhận được, huy hiệu mới, giải thích từng câu (`explanation`).

---

## (e) Interface GamificationService (agent Progress viết tại `app/Services/GamificationService.php`)

```php
namespace App\Services;

use App\Models\LearnerProfile;
use App\Models\PlaySession;
use App\Models\Skill;
use Illuminate\Support\Collection;

class GamificationService
{
    /**
     * Ghi nhận một lượt chơi: cộng XP, cập nhật streak/cấp độ, xét huy hiệu.
     * @return ['xp'=>int,'level_up'=>bool,'new_level'=>int,'new_badges'=>Collection,'streak'=>int]
     */
    public static function recordPlay(LearnerProfile $profile, PlaySession $session): array;

    /** Cấp độ tương ứng với tổng XP (theo config 'vuihoc.levels'). */
    public static function levelForXp(int $xp): int;

    /**
     * Gợi ý độ khó tiếp theo cho skill.
     * Dựa vào accuracy 3 lượt chơi gần nhất của profile ở skill này:
     * >= 80% → tăng 1 bậc; < 40% → giảm 1 bậc; còn lại giữ nguyên.
     * Bậc: de < trung_binh < kho.
     */
    public static function suggestNextDifficulty(LearnerProfile $profile, Skill $skill): string;
}
```

- `recordPlay` phải: tạo `xp_events` (source = 'play', meta_json chứa session_id/score/accuracy), cập nhật `total_xp`, `level`, `current_streak`/`longest_streak`/`last_play_date`, xét và gắn huy hiệu mới vào `profile_badges` theo `badges.criteria` + `threshold`, trả về mảng đúng cấu trúc trên.
- Huy hiệu mới trả về trong `new_badges` là Collection các model `Badge`.

---

## (f) Quy tắc XP (config `config/vuihoc.php` — ĐỌC CONFIG, không hardcode)

- Mỗi câu quiz đúng: `xp.quiz_correct` (=10); thưởng tốc độ tối đa `xp.quiz_time_bonus_max` (=5).
- Mỗi cặp matching đúng: `xp.matching_pair` (=5).
- Mỗi item sort đúng vị trí: `xp.sort_item` (=5).
- Mỗi chỗ trống fill đúng: `xp.fill_blank` (=10).
- Đạt 100% lượt chơi: cộng thêm `xp.perfect_bonus_percent` (=20%) trên tổng XP lượt đó.
- Mỗi ngày có lượt chơi (chuỗi học): cộng `xp.streak_day` (=2).
- Bảng cấp độ: `vuihoc.levels` — level N cần tổng XP ≥ ngưỡng (1:0, 2:100, 3:250, 4:450, 5:700, 6:1000, 7:1400, 8:1900, 9:2500, 10:3200).
- Tên loại game: `vuihoc.game_types`; tên độ khó: `vuihoc.difficulties`.

---

## (g) Quy ước code chung

1. **Giao diện:** Blade + Alpine.js. Alpine.js đã có sẵn local tại `public/js/alpine.min.js`, layout `layouts/app.blade.php` đã nhúng `defer`. **TUYỆT ĐỐI không dùng CDN nào khác trong layout.**
2. **Ngôn ngữ UI:** tiếng Việt 100%.
3. **Nội dung mẫu:** tự viết 100%, không copy từ bất kỳ nguồn nào.
4. **DB:** tên index tường minh, ngắn gọn (MariaDB giới hạn 64 ký tự).
5. **Route:** đặt tên theo dạng `file.hành-động` như mục (c).
6. **Không viết đè file route của agent khác.** Mỗi agent chỉ sửa file route của mình.
7. **Auth:** dùng session auth mặc định của Laravel (agent Identity triển khai).
8. **Timezone:** Asia/Ho_Chi_Minh (đã cấu hình trong `.env`).
9. **Trả lời câu hỏi đúng/sai chỉ ở server** — xem luồng (d).
10. Mọi số liệu XP/cấp độ đọc từ `config/vuihoc.php` qua `config('vuihoc.xp.quiz_correct')`...

---

## Tình trạng phase 1 (đã xong)

- Laravel 11 + Sanctum đã cài; `.env` cấu hình MySQL DB `vuihoc`; APP_KEY đã tạo.
- 16 bảng đã migrate (18 models): users, learner_profiles, subjects, topics, skills, lessons, questions, question_options, matching_pairs, sort_items, fill_answers, play_sessions, xp_events, badges, profile_badges, favorites, classrooms, class_members.
- `config/vuihoc.php`, middleware `role`, `app/helpers.php` (`active_profile()`), layout `layouts/app.blade.php` + `partials/alerts` + `public/css/vuihoc.css`, Alpine.js local.
- Route `/` hiện trả text `VuiHoc OK` (200) — agent Library viết trang chủ thật ở phase 2.
