<?php

namespace Tests\Feature;

use App\Services\LearningContentImporter;
use App\Services\LearningContentMapper;
use App\Services\LearningSqlParser;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\LearningContentTestCase;

class LearningContentMappingTest extends LearningContentTestCase
{
    private function source(): array
    {
        return (new LearningSqlParser)->parse(file_get_contents(base_path('database/vuihoc-database.sql')));
    }

    public function test_all_levels_keep_destination_ids_and_foreign_keys_after_permutation(): void
    {
        $source = $this->source();
        $existing = $source;
        // Reverse each table's IDs and rewrite all FKs to mimic separately seeded databases.
        $maps = [];
        foreach ($existing as $table => $rows) {
            $keys = array_keys($rows);
            $maps[$table] = array_combine($keys, array_reverse($keys));
            $existing[$table] = [];
            foreach ($rows as $id => $row) {
                $row['id'] = $maps[$table][$id];
                if (isset(LearningContentMapper::PARENTS[$table])) {
                    [$fk, $parent] = LearningContentMapper::PARENTS[$table];
                    $row[$fk] = $maps[$parent][$row[$fk]];
                }
                $existing[$table][$row['id']] = $row;
            }
        }
        $plan = (new LearningContentMapper)->plan($source, $existing);
        foreach ($source as $table => $rows) {
            $this->assertSame(0, $plan['counts'][$table]['new']);
            foreach ($rows as $sourceId => $row) {
                $targetId = $maps[$table][$sourceId];
                $this->assertSame($existing[$table][$targetId]['id'], $plan['data'][$table][$targetId]['id']);
                if (isset(LearningContentMapper::PARENTS[$table])) {
                    [$fk] = LearningContentMapper::PARENTS[$table];
                    $this->assertSame($existing[$table][$targetId][$fk], $plan['data'][$table][$targetId][$fk]);
                }
            }
        }
    }

    public function test_unmatched_content_gets_fresh_id_instead_of_overwriting_colliding_id(): void
    {
        $source = $this->source();
        $existing = array_fill_keys(array_keys($source), []);
        $existing['subjects'][1] = array_replace($source['subjects'][1], ['slug' => 'local-only']);
        $plan = (new LearningContentMapper)->plan($source, $existing);
        $this->assertSame('toan', $plan['data']['subjects'][2]['slug']);
        $this->assertArrayNotHasKey(1, $plan['data']['subjects']);
        $reserved = (new LearningContentMapper)->plan($source, $existing, ['subjects' => 1000]);
        $this->assertSame('toan', $reserved['data']['subjects'][1000]['slug']);
    }

    public function test_ambiguous_catalog_and_question_keys_fail_closed(): void
    {
        $source = $this->source();
        $existing = $source;
        $existing['topics'][9999] = array_replace($existing['topics'][1], ['id' => 9999]);
        $this->expectException(RuntimeException::class);
        (new LearningContentMapper)->plan($source, $existing);
    }

    public function test_preview_mapping_change_requires_new_confirmation(): void
    {
        $source = $this->source();
        $importer = new LearningContentImporter;
        $plan = $importer->analyze($source);
        DB::table('subjects')->insert(array_replace($source['subjects'][1], ['id' => 100, 'slug' => 'local-only']));
        try {
            $importer->import($source, 1, 'stale.sql', str_repeat('a', 64), $plan['hash']);
            $this->fail('Changed mapping must require a new preview');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('preview', $error->getMessage());
            $this->assertSame(1, DB::table('subjects')->count());
            $this->assertSame(0, DB::table('learning_content_imports')->count());
        }
    }

    public function test_new_id_collision_rolls_back_instead_of_updating_unrelated_content(): void
    {
        DB::statement("CREATE TRIGGER concurrent_topic AFTER INSERT ON subjects WHEN NEW.slug = 'toan' BEGIN INSERT INTO topics (id, subject_id, name, slug) VALUES (1, NEW.id, 'Local topic', 'local-topic'); END");
        $source = $this->source();
        try {
            (new LearningContentImporter)->import($source, 1, 'collision.sql', str_repeat('a', 64));
            $this->fail('New ID collision must abort');
        } catch (QueryException) {
            $this->assertSame(0, DB::table('subjects')->count());
            $this->assertSame(0, DB::table('topics')->count());
            $this->assertSame(0, DB::table('learning_content_imports')->count());
        }
    }

    public function test_duplicate_questions_at_same_slot_are_rejected(): void
    {
        $source = $this->source();
        $source['questions'][99999] = array_replace($source['questions'][1], ['id' => 99999]);
        $this->expectException(RuntimeException::class);
        (new LearningContentMapper)->plan($source, array_fill_keys(array_keys($source), []));
    }

    public function test_real_hosting_export_preserves_ids_and_repeated_import_adds_nothing(): void
    {
        $path = getenv('LEARNING_IMPORT_HOSTING_FIXTURE');
        if (! $path) {
            $this->markTestSkipped('Private hosting fixture is supplied only for local verification.');
        }
        $parser = new LearningSqlParser;
        $host = $parser->parse(file_get_contents($path));
        foreach ($host as $table => $rows) {
            foreach (array_chunk($rows, 300) as $chunk) {
                DB::table($table)->insert($chunk);
            }
        }
        $source = $this->source();
        $importer = new LearningContentImporter;
        $plan = $importer->analyze($source);
        fwrite(STDERR, "\nHosting preview: ".json_encode($plan['counts'])."\n");
        $importer->import($source, 1, 'source.sql', str_repeat('a', 64), $plan['hash']);
        foreach ($host as $table => $rows) {
            $currentRows = DB::table($table)->get()->keyBy('id');
            foreach ($rows as $id => $row) {
                $current = (array) $currentRows[$id];
                $this->assertSame($id, $current['id']);
                if (isset($row['slug'])) {
                    $this->assertSame($row['slug'], $current['slug']);
                }
                if ($table === 'questions') {
                    $this->assertSame($row['prompt'], $current['prompt']);
                }
                if (isset(LearningContentMapper::PARENTS[$table])) {
                    [$fk] = LearningContentMapper::PARENTS[$table];
                    $this->assertSame($row[$fk], $current[$fk]);
                }
            }
        }
        $again = $importer->analyze($source);
        foreach ($again['counts'] as $counts) {
            $this->assertSame(0, $counts['new']);
        }
        $totals = [];
        foreach ($host as $table => $rows) {
            $totals[$table] = DB::table($table)->count();
        }
        $importer->import($source, 1, 'source.sql', str_repeat('a', 64), $again['hash']);
        foreach ($totals as $table => $total) {
            $this->assertSame($total, DB::table($table)->count());
        }
        $this->assertSame('Giữ nguyên', DB::table('users')->value('name'));
    }
}
