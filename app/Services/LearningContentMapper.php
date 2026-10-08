<?php

namespace App\Services;

use RuntimeException;

/** Resolves dump IDs without changing existing destination primary keys. */
class LearningContentMapper
{
    public const PARENTS = [
        'topics' => ['subject_id', 'subjects'], 'skills' => ['topic_id', 'topics'],
        'lessons' => ['skill_id', 'skills'], 'questions' => ['lesson_id', 'lessons'],
        'question_options' => ['question_id', 'questions'], 'matching_pairs' => ['question_id', 'questions'],
        'fill_answers' => ['question_id', 'questions'], 'sort_items' => ['question_id', 'questions'],
    ];

    public function plan(array $source, array $existing, array $nextIds = []): array
    {
        $mapped = $ids = $counts = $newIds = [];
        foreach (LearningSqlParser::COLUMNS as $table => $columns) {
            $incoming = [];
            foreach ($source[$table] as $sourceId => $row) {
                if (isset(self::PARENTS[$table])) {
                    [$fk, $parent] = self::PARENTS[$table];
                    // Source FK IDs never fall back to the destination namespace.
                    if (! is_int($row[$fk]) || ! isset($ids[$parent][$row[$fk]])) {
                        throw new RuntimeException("{$table} ID {$sourceId}: thiếu {$parent} trong file. Xuất đủ các bảng cha để ánh xạ an toàn.");
                    }
                    $row[$fk] = $ids[$parent][$row[$fk]];
                }
                $incoming[$sourceId] = $row;
            }
            $groups = $sourceGroups = [];
            foreach ($existing[$table] as $id => $row) {
                $groups[$this->key($table, $row)][] = $id;
            }
            foreach ($incoming as $sourceId => $row) {
                $sourceGroups[$this->key($table, $row)][] = $sourceId;
            }
            $index = $seen = [];
            foreach ($existing[$table] as $id => $row) {
                $base = $this->key($table, $row);
                $key = $this->disambiguate($table, $row, $base, $groups, $sourceGroups);
                if (isset($index[$key])) {
                    throw new RuntimeException("{$table}: nhiều bản ghi hosting cùng khóa nội dung. Cần xử lý trùng trước khi import.");
                }
                $index[$key] = $id;
            }
            $nextId = max($existing[$table] ? max(array_keys($existing[$table])) : 0, ($nextIds[$table] ?? 1) - 1);
            $mapped[$table] = [];
            $newIds[$table] = [];
            $counts[$table] = ['total' => count($incoming), 'new' => 0, 'update' => 0, 'remapped' => 0];
            foreach ($incoming as $sourceId => $row) {
                $base = $this->key($table, $row);
                $key = $this->disambiguate($table, $row, $base, $groups, $sourceGroups);
                if (isset($seen[$key])) {
                    throw new RuntimeException("{$table}: nhiều bản ghi trong file cùng khóa nội dung. Không thể tự ghép.");
                }
                $seen[$key] = true;
                $targetId = $index[$key] ?? ++$nextId;
                if (! isset($index[$key])) {
                    $newIds[$table][$targetId] = true;
                }
                $counts[$table][isset($index[$key]) ? 'update' : 'new']++;
                $counts[$table]['remapped'] += (int) ($targetId !== $sourceId);
                $ids[$table][$sourceId] = $targetId;
                $row['id'] = $targetId;
                $mapped[$table][$targetId] = $row;
            }
        }

        return ['data' => $mapped, 'counts' => $counts, 'new_ids' => $newIds, 'hash' => hash('sha256', json_encode([$ids, $counts], JSON_THROW_ON_ERROR))];
    }

    private function key(string $table, array $row): string
    {
        $fields = match ($table) {
            'subjects' => ['slug'], 'topics' => ['subject_id', 'slug'],
            'skills' => ['topic_id', 'slug'], 'lessons' => ['skill_id', 'slug'],
            'questions' => ['lesson_id', 'game_type', 'prompt'],
            'fill_answers' => ['question_id', 'blank_index', 'sort_order'],
            default => ['question_id', 'sort_order'],
        };
        $values = [];
        foreach ($fields as $field) {
            $value = $row[$field];
            if ($field === 'slug' && (! is_string($value) || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $value))) {
                throw new RuntimeException("{$table}: slug phải là chữ thường ASCII, số và dấu gạch nối.");
            }
            if (($field === 'sort_order' || $field === 'blank_index') && (! is_int($value) || $value < 0)) {
                throw new RuntimeException("{$table}: {$field} phải là số nguyên không âm.");
            }
            $values[] = $value;
        }

        return hash('sha256', json_encode($values, JSON_THROW_ON_ERROR));
    }

    private function disambiguate(string $table, array $row, string $base, array $groups, array $sourceGroups): string
    {
        if (count($groups[$base] ?? []) > 1 || count($sourceGroups[$base] ?? []) > 1) {
            if ($table !== 'questions') {
                throw new RuntimeException("{$table}: khóa nội dung không duy nhất. Cần xử lý trùng trước khi import.");
            }
            if (! is_int($row['sort_order']) || $row['sort_order'] < 0) {
                throw new RuntimeException('questions: cần sort_order hợp lệ để phân biệt câu hỏi trùng nội dung.');
            }

            return $base.':'.$row['sort_order'];
        }

        return $base;
    }
}
