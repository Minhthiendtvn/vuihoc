<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class LearningContentImporter
{
    public function preview(array $data): array
    {
        return $this->analyze($data)['counts'];
    }

    public function analyze(array $data): array
    {
        $existing = [];
        $nextIds = [];
        foreach (LearningSqlParser::COLUMNS as $table => $columns) {
            if (array_diff(explode(',', $columns), Schema::getColumnListing($table))) {
                throw new RuntimeException("Schema hosting thiếu cột ở {$table}. Chạy các migration hiện có trước.");
            }
            $hasPrimary = false;
            foreach (Schema::getIndexes($table) as $index) {
                $hasPrimary = $hasPrimary || ($index['primary'] && $index['columns'] === ['id']);
                if ($index['unique'] && $index['columns'] !== ['id'] && ! ($table === 'subjects' && $index['columns'] === ['slug'])) {
                    throw new RuntimeException("{$table}: unique key khác schema đã hỗ trợ; cần kiểm tra trước khi import.");
                }
            }
            if (! $hasPrimary) {
                throw new RuntimeException("{$table}: thiếu primary key id.");
            }
            $select = ['id', 'sort_order'];
            if (isset(LearningContentMapper::PARENTS[$table])) {
                $select[] = LearningContentMapper::PARENTS[$table][0];
            }
            if (in_array($table, ['subjects', 'topics', 'skills', 'lessons'], true)) {
                $select[] = 'slug';
            }
            if ($table === 'questions') {
                $select = array_merge($select, ['game_type', 'prompt']);
            }
            if ($table === 'fill_answers') {
                $select[] = 'blank_index';
            }
            $query = DB::table($table)->select($select)->orderBy('id');
            if (DB::transactionLevel() > 0) {
                $query->lockForUpdate();
            }
            $existing[$table] = [];
            foreach ($query->get() as $row) {
                $row = (array) $row;
                foreach ($select as $column) {
                    if ($column === 'id' || str_ends_with($column, '_id') || in_array($column, ['sort_order', 'blank_index'], true)) {
                        $row[$column] = (int) $row[$column];
                    }
                }
                $existing[$table][$row['id']] = $row;
            }
            if (DB::getDriverName() === 'mysql') {
                $sequence = DB::selectOne('SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [DB::getTablePrefix().$table]);
                $nextIds[$table] = (int) ($sequence->next_id ?? 1);
            }
        }

        return (new LearningContentMapper)->plan($data, $existing, $nextIds);
    }

    public function import(array $data, int $adminId, string $filename, string $hash, ?string $expectedPlanHash = null): array
    {
        if (DB::getDriverName() === 'mysql') {
            foreach (array_merge(array_keys(LearningSqlParser::COLUMNS), ['learning_content_imports']) as $table) {
                $engine = DB::selectOne('SELECT ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [DB::getTablePrefix().$table]);
                if (! $engine || strtoupper($engine->engine) !== 'INNODB') {
                    throw new RuntimeException("{$table} phải dùng InnoDB để rollback an toàn.");
                }
            }
        }

        return DB::transaction(function () use ($data, $adminId, $filename, $hash, $expectedPlanHash) {
            $plan = $this->analyze($data);
            if ($expectedPlanHash !== null && ! hash_equals($expectedPlanHash, $plan['hash'])) {
                throw new RuntimeException('Dữ liệu hosting đã thay đổi từ lúc preview. Vui lòng upload lại để kiểm tra ánh xạ mới.');
            }
            foreach (LearningSqlParser::COLUMNS as $table => $columns) {
                foreach (array_chunk($plan['data'][$table], 300) as $rows) {
                    $newRows = [];
                    $updates = [];
                    foreach ($rows as $row) {
                        if (isset($plan['new_ids'][$table][$row['id']])) {
                            $newRows[] = $row;
                        } else {
                            $updates[] = $row;
                        }
                    }
                    // A concurrent Admin insert must cause rollback, never be overwritten.
                    if ($newRows) {
                        DB::table($table)->insert($newRows);
                    }
                    if ($table !== 'subjects') {
                        if ($updates) {
                            DB::table($table)->upsert($updates, ['id'], array_diff(explode(',', $columns), ['id', 'created_at']));
                        }

                        continue;
                    }
                    // Never let MySQL alternate unique slug matching update another ID.
                    foreach ($updates as $row) {
                        $id = $row['id'];
                        unset($row['id'], $row['created_at']);
                        DB::table($table)->where('id', $id)->update($row);
                    }
                }
            }
            DB::table('learning_content_imports')->insert([
                'admin_id' => $adminId, 'filename' => $filename, 'sha256' => $hash,
                'counts' => json_encode($plan['counts'], JSON_THROW_ON_ERROR), 'created_at' => now(),
            ]);

            return $plan['counts'];
        });
    }
}
