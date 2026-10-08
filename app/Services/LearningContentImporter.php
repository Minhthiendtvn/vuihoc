<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class LearningContentImporter
{
    private const PARENTS = [
        'topics' => ['subject_id', 'subjects'], 'skills' => ['topic_id', 'topics'],
        'lessons' => ['skill_id', 'skills'], 'questions' => ['lesson_id', 'lessons'],
        'question_options' => ['question_id', 'questions'], 'matching_pairs' => ['question_id', 'questions'],
        'fill_answers' => ['question_id', 'questions'], 'sort_items' => ['question_id', 'questions'],
    ];

    public function preview(array $data): array
    {
        $existing = [];
        $counts = [];
        foreach (LearningSqlParser::COLUMNS as $table => $columns) {
            if (array_diff(explode(',', $columns), Schema::getColumnListing($table))) {
                throw new RuntimeException("Schema hosting thiếu cột ở {$table}. Chạy các migration hiện có trước.");
            }
            foreach (Schema::getIndexes($table) as $index) {
                if ($index['unique'] && $index['columns'] !== ['id'] && ! ($table === 'subjects' && $index['columns'] === ['slug'])) {
                    throw new RuntimeException("{$table}: unique key khác schema đã hỗ trợ; cần kiểm tra trước khi import.");
                }
            }
            $select = ['id'];
            if (isset(self::PARENTS[$table])) {
                $select[] = self::PARENTS[$table][0];
            }
            if (in_array('slug', explode(',', $columns), true)) {
                $select[] = 'slug';
            }
            if ($table === 'questions') {
                $select[] = 'game_type';
            }
            $query = DB::table($table)->select($select)->orderBy('id');
            if (DB::transactionLevel() > 0) {
                $query->lockForUpdate();
            }
            $existing[$table] = $query->get()->keyBy('id')->all();
            $counts[$table] = ['total' => count($data[$table]), 'new' => 0, 'update' => 0];
            foreach ($data[$table] as $id => $row) {
                $old = $existing[$table][$id] ?? null;
                $counts[$table][$old ? 'update' : 'new']++;
                if (isset(self::PARENTS[$table])) {
                    [$column, $parent] = self::PARENTS[$table];
                    if (! is_int($row[$column]) || $row[$column] < 1 || (! isset($data[$parent][$row[$column]]) && ! isset($existing[$parent][$row[$column]]))) {
                        throw new RuntimeException("{$table} ID {$id}: thiếu {$parent} ID ".($row[$column] ?? 'NULL').'.');
                    }
                    if ($old && $row[$column] != $old->$column) {
                        throw new RuntimeException("{$table} ID {$id}: khác bản ghi cha trên hosting. File phải cùng nguồn ID với database hiện tại.");
                    }
                }
                if ($old && isset($row['slug']) && $old->slug !== $row['slug']) {
                    throw new RuntimeException("{$table} ID {$id}: slug khác hosting; cần xử lý xung đột ID trước khi import.");
                }
                if ($table === 'questions' && $old && $old->game_type !== $row['game_type']) {
                    throw new RuntimeException("questions ID {$id}: không thể đổi loại game khi giữ các đáp án hiện có.");
                }
            }
        }
        // MySQL upsert matches ALL unique keys; reject slug collisions rather than
        // allowing subjects_slug_unique to update a different primary key.
        $slugs = [];
        foreach ($existing['subjects'] as $id => $row) {
            $slugs[mb_strtolower($row->slug)] = $id;
        }
        foreach ($data['subjects'] as $id => $row) {
            $slug = mb_strtolower((string) $row['slug']);
            if (isset($slugs[$slug]) && $slugs[$slug] !== $id) {
                throw new RuntimeException("subjects ID {$id}: slug đã thuộc ID khác.");
            }
            $slugs[$slug] = $id;
            $collision = DB::table('subjects')->where('slug', $row['slug'])->where('id', '<>', $id)->exists();
            if ($collision) {
                throw new RuntimeException("subjects ID {$id}: slug trùng theo collation database.");
            }
        }

        return $counts;
    }

    public function import(array $data, int $adminId, string $filename, string $hash): array
    {
        if (DB::getDriverName() === 'mysql') {
            foreach (array_merge(array_keys(LearningSqlParser::COLUMNS), ['learning_content_imports']) as $table) {
                $engine = DB::selectOne('SELECT ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [DB::getTablePrefix().$table]);
                if (! $engine || strtoupper($engine->engine) !== 'INNODB') {
                    throw new RuntimeException("{$table} phải dùng InnoDB để rollback an toàn.");
                }
            }
        }

        return DB::transaction(function () use ($data, $adminId, $filename, $hash) {
            // Recheck immediately before writes; previews are only estimates.
            $counts = $this->preview($data);
            foreach (LearningSqlParser::COLUMNS as $table => $columns) {
                foreach (array_chunk($data[$table], 300) as $rows) {
                    if ($table !== 'subjects') {
                        DB::table($table)->upsert($rows, ['id'], array_diff(explode(',', $columns), ['id', 'created_at']));

                        continue;
                    }
                    // Explicit id lookup avoids MySQL's implicit alternate-unique-key matching.
                    // Insert collisions abort the entire transaction instead of updating another ID.
                    foreach ($rows as $row) {
                        if (DB::table($table)->where('id', $row['id'])->exists()) {
                            $id = $row['id'];
                            unset($row['id'], $row['created_at']);
                            DB::table($table)->where('id', $id)->update($row);
                        } else {
                            DB::table($table)->insert($row);
                        }
                    }
                }
            }
            DB::table('learning_content_imports')->insert([
                'admin_id' => $adminId, 'filename' => $filename, 'sha256' => $hash,
                'counts' => json_encode($counts, JSON_THROW_ON_ERROR), 'created_at' => now(),
            ]);

            return $counts;
        });
    }
}
