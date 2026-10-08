<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LearningContentImporter;
use App\Services\LearningSqlParser;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\LearningContentTestCase;

class LearningContentImportTest extends LearningContentTestCase
{
    private function sql(string $table = 'subjects', int $id = 1, array $overrides = []): string
    {
        $columns = explode(',', LearningSqlParser::COLUMNS[$table]);
        $row = array_fill_keys($columns, null);
        foreach (['name', 'slug', 'title', 'prompt', 'icon', 'color', 'option_text', 'left_text', 'right_text', 'item_text', 'category', 'answer_text'] as $column) {
            if (array_key_exists($column, $row)) {
                $row[$column] = 'test';
            }
        }
        foreach (['sort_order', 'is_demo', 'is_published', 'grade_min', 'grade_max', 'duration_minutes', 'points', 'is_correct', 'blank_index'] as $column) {
            if (array_key_exists($column, $row)) {
                $row[$column] = 1;
            }
        }
        foreach (['difficulty' => 'de', 'status' => 'published', 'game_type' => 'quiz'] as $column => $value) {
            if (array_key_exists($column, $row)) {
                $row[$column] = $value;
            }
        }
        $row = array_replace($row, ['id' => $id], $overrides);
        if ($table === 'subjects') {
            $row = array_replace($row, ['slug' => $overrides['slug'] ?? 'toan', 'name' => $overrides['name'] ?? 'Toán']);
        }
        $values = array_map(fn ($value) => $value === null ? 'NULL' : (is_int($value) ? (string) $value : "'".str_replace(['\\', "'"], ['\\\\', "\\'"], $value)."'"), $row);

        return 'INSERT INTO `'.$table.'` (`'.implode('`,`', $columns).'`) VALUES ('.implode(',', $values).');';
    }

    public function test_actual_dump_is_parsed_without_system_tables(): void
    {
        $data = (new LearningSqlParser)->parse(file_get_contents(base_path('database/vuihoc-database.sql')));
        $this->assertSame(array_keys(LearningSqlParser::COLUMNS), array_keys($data));
        $this->assertCount(13728, $data['question_options']);
        $this->assertGreaterThan(13000, count($data['questions']));
        $this->assertStringContainsString('Định dạng', $data['lessons'][287]['title']);
        (new LearningContentImporter)->import($data, 1, 'full.sql', str_repeat('a', 64));
        $this->assertSame(count($data['questions']), DB::table('questions')->count());
        $this->assertSame(1, DB::table('users')->count());
    }

    public function test_ignored_commands_and_quoted_sql_never_execute_and_import_is_repeatable(): void
    {
        $text = "O'Brien; DROP TABLE users; -- chuỗi\nTiếng Việt \\ test";
        $sql = 'DROP TABLE users; CREATE TABLE users (id INT); TRUNCATE users; DELETE FROM users; INSERT INTO users VALUES (99);'.$this->sql(overrides: ['name' => $text]);
        $parser = new LearningSqlParser;
        $data = $parser->parse($sql);
        $this->assertSame($text, $data['subjects'][1]['name']);
        $importer = new LearningContentImporter;
        $this->assertSame(1, $importer->preview($data)['subjects']['new']);
        $importer->import($data, 1, 'test.sql', str_repeat('a', 64));
        $data['subjects'][1]['name'] = 'Đã cập nhật';
        $importer->import($data, 1, 'test.sql', str_repeat('a', 64));
        $this->assertSame(1, DB::table('subjects')->count());
        $this->assertSame('Đã cập nhật', DB::table('subjects')->value('name'));
        $this->assertSame('Giữ nguyên', DB::table('users')->value('name'));
        $this->assertSame(2, DB::table('learning_content_imports')->count());
        $other = $parser->parse($this->sql(id: 2, overrides: ['slug' => 'van']));
        $importer->import($other, 1, 'other.sql', str_repeat('b', 64));
        $this->assertSame(2, DB::table('subjects')->count());
    }

    public function test_child_first_file_imports_in_fk_order(): void
    {
        $sql = $this->sql('topics', 1, ['subject_id' => 1, 'slug' => 'topic']).$this->sql();
        (new LearningContentImporter)->import((new LearningSqlParser)->parse($sql), 1, 'order.sql', str_repeat('a', 64));
        $this->assertSame(1, DB::table('topics')->value('subject_id'));
    }

    public function test_late_database_failure_rolls_back_content_and_history(): void
    {
        DB::statement("CREATE TRIGGER reject_topic BEFORE INSERT ON topics BEGIN SELECT RAISE(ABORT, 'invalid topic'); END");
        $data = (new LearningSqlParser)->parse($this->sql().$this->sql('topics', 1, ['subject_id' => 1]));
        try {
            (new LearningContentImporter)->import($data, 1, 'fail.sql', str_repeat('a', 64));
            $this->fail('Import must fail');
        } catch (QueryException) {
            $this->assertSame(0, DB::table('subjects')->count());
            $this->assertSame(0, DB::table('learning_content_imports')->count());
        }
    }

    public function test_same_slug_with_different_source_id_reuses_hosting_id(): void
    {
        $parser = new LearningSqlParser;
        $importer = new LearningContentImporter;
        $importer->import($parser->parse($this->sql()), 1, 'a.sql', str_repeat('a', 64));
        $data = $parser->parse($this->sql(id: 2, overrides: ['name' => 'Updated']));
        $this->assertSame(1, $importer->preview($data)['subjects']['update']);
        $importer->import($data, 1, 'b.sql', str_repeat('b', 64));
        $this->assertSame(1, DB::table('subjects')->value('id'));
        $this->assertSame('Updated', DB::table('subjects')->value('name'));
    }

    public function test_missing_parent_is_rejected_before_writes(): void
    {
        $this->expectException(RuntimeException::class);
        (new LearningContentImporter)->preview((new LearningSqlParser)->parse($this->sql('topics', 1, ['subject_id' => 999])));
    }

    public function test_parent_ids_cannot_fall_back_to_hosting_namespace(): void
    {
        $parser = new LearningSqlParser;
        $importer = new LearningContentImporter;
        $importer->import($parser->parse($this->sql().$this->sql(id: 2, overrides: ['slug' => 'van']).$this->sql('topics', 1, ['subject_id' => 1])), 1, 'a.sql', str_repeat('a', 64));
        $this->expectException(RuntimeException::class);
        $importer->preview($parser->parse($this->sql('topics', 1, ['subject_id' => 2])));
    }

    public function test_truncated_file_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        (new LearningSqlParser)->parse(rtrim($this->sql(), ';'));
    }

    public function test_changed_temporary_file_is_rejected(): void
    {
        $admin = new User;
        $admin->id = 1;
        $admin->role = 'admin';
        $this->actingAs($admin);
        $this->post(route('admin.imports.preview'), ['sql_file' => UploadedFile::fake()->createWithContent('data.sql', $this->sql())]);
        $pending = session('learning_import');
        Storage::disk('local')->put($pending['path'], $this->sql(id: 2));
        $this->post(route('admin.imports.store'), ['token' => $pending['token'], 'confirm' => 1])->assertSessionHasErrors('sql_file');
        $this->assertSame(0, DB::table('subjects')->count());
    }

    public function test_guest_and_oversize_upload_are_rejected(): void
    {
        $this->get(route('admin.imports.index'))->assertForbidden();
        $admin = new User;
        $admin->id = 1;
        $admin->role = 'admin';
        $this->actingAs($admin)->post(route('admin.imports.preview'), ['sql_file' => UploadedFile::fake()->create('huge.sql', 32769)])->assertSessionHasErrors('sql_file');
    }

    public function test_sql_expression_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        (new LearningSqlParser)->parse(str_replace("'Toán'", 'SLEEP(1)', $this->sql()));
    }

    public function test_duplicate_ids_are_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        (new LearningSqlParser)->parse($this->sql().$this->sql());
    }

    public function test_admin_upload_preview_confirmation_and_replay_protection(): void
    {
        $admin = new User;
        $admin->id = 1;
        $admin->role = 'admin';
        $this->actingAs($admin);
        $this->post(route('admin.imports.preview'), ['sql_file' => UploadedFile::fake()->createWithContent('data.sql', $this->sql())])->assertSessionHas('learning_import');
        $this->assertSame(0, DB::table('subjects')->count());
        $this->get(route('admin.imports.index'))->assertOk()->assertSee('Xem trước');
        $pending = session('learning_import');
        $this->post(route('admin.imports.store'), ['token' => $pending['token'], 'confirm' => 1])->assertSessionHas('success');
        Storage::disk('local')->assertMissing($pending['path']);
        $this->post(route('admin.imports.store'), ['token' => $pending['token'], 'confirm' => 1])->assertSessionHasErrors('sql_file');
        $this->assertSame(1, DB::table('learning_content_imports')->count());
    }

    public function test_non_admin_is_denied_all_import_routes(): void
    {
        foreach (['student', 'teacher', 'parent'] as $role) {
            $user = new User;
            $user->id = 1;
            $user->role = $role;
            $this->actingAs($user);
            $this->get(route('admin.imports.index'))->assertForbidden();
            foreach (['preview', 'store', 'cancel'] as $action) {
                $this->post(route('admin.imports.'.$action))->assertForbidden();
            }
        }
    }

    public function test_wrong_extension_and_expired_preview_are_rejected(): void
    {
        $admin = new User;
        $admin->id = 1;
        $admin->role = 'admin';
        $this->actingAs($admin);
        $this->post(route('admin.imports.preview'), ['sql_file' => UploadedFile::fake()->createWithContent('data.txt', $this->sql())])->assertSessionHasErrors('sql_file');
        $this->post(route('admin.imports.preview'), ['sql_file' => UploadedFile::fake()->createWithContent('data.sql', $this->sql())]);
        $pending = session('learning_import');
        $pending['expires'] = time() - 1;
        $this->withSession(['learning_import' => $pending])->post(route('admin.imports.store'), ['token' => $pending['token'], 'confirm' => 1])->assertSessionHasErrors('sql_file');
        $this->assertSame(0, DB::table('subjects')->count());
    }
}
