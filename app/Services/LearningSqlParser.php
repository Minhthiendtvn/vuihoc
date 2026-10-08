<?php

namespace App\Services;

use RuntimeException;

/** Parses literals only. No part of the uploaded SQL is ever executed. */
class LearningSqlParser
{
    public const COLUMNS = [
        'subjects' => 'id,name,slug,icon,color,description,sort_order,is_published,is_demo,created_at,updated_at',
        'topics' => 'id,subject_id,name,slug,description,icon,sort_order,grade_min,grade_max,is_published,is_demo,created_at,updated_at',
        'skills' => 'id,topic_id,name,slug,description,sort_order,is_demo,created_at,updated_at',
        'lessons' => 'id,skill_id,title,slug,objective,difficulty,duration_minutes,instructions,summary,sort_order,status,grade,is_demo,created_at,updated_at',
        'questions' => 'id,lesson_id,game_type,grade,prompt,explanation,difficulty,points,sort_order,is_demo,created_at,updated_at',
        'question_options' => 'id,question_id,option_text,is_correct,sort_order,created_at,updated_at',
        'matching_pairs' => 'id,question_id,left_text,right_text,sort_order,created_at,updated_at',
        'fill_answers' => 'id,question_id,blank_index,answer_text,sort_order,created_at,updated_at',
        'sort_items' => 'id,question_id,item_text,category,sort_order,created_at,updated_at',
    ];

    public function parse(string $sql): array
    {
        if (strlen($sql) > 32 * 1024 * 1024 || ! mb_check_encoding($sql, 'UTF-8')) {
            throw new RuntimeException('File phải là UTF-8 và không quá 32 MB.');
        }
        $data = array_fill_keys(array_keys(self::COLUMNS), []);
        $layouts = [];
        $total = 0;
        foreach ($this->statements($sql) as $statement) {
            // CREATE is read only to determine positional INSERT column order.
            if (preg_match('/^CREATE TABLE `?(\w+)`?\s*\(/i', $statement, $match)) {
                $table = $match[1];
                if (isset(self::COLUMNS[$table])) {
                    preg_match_all('/^\s*`(\w+)`\s+/m', $statement, $columns);
                    $layouts[$table] = $columns[1];
                }

                continue;
            }
            if (! preg_match('/^INSERT\s+INTO\s+`?(\w+)`?\b/i', $statement, $match)) {
                continue;
            }
            $table = $match[1];
            if (! isset(self::COLUMNS[$table])) {
                continue;
            }
            if (! preg_match('/^INSERT\s+INTO\s+`?'.preg_quote($table, '/').'`?\s*(?:\(([^)]*)\))?\s*VALUES\s*(.*)$/is', $statement, $insert)) {
                throw new RuntimeException("INSERT không được hỗ trợ ở bảng {$table} (chỉ nhận VALUES thuần).");
            }
            $columns = ! empty($insert[1]) ? $this->columnNames($insert[1]) : ($layouts[$table] ?? null);
            $expected = explode(',', self::COLUMNS[$table]);
            if ($columns === null || count($columns) !== count($expected) || array_diff($columns, $expected) || count(array_unique($columns)) !== count($columns)) {
                throw new RuntimeException("Cấu trúc cột {$table} không khớp. Hãy xuất full dump hiện tại hoặc INSERT có đủ tên cột.");
            }
            foreach ($this->rows($insert[2], $table) as $values) {
                if (count($values) !== count($columns)) {
                    throw new RuntimeException("Sai số cột trong {$table}.");
                }
                $row = array_combine($columns, $values);
                if (! is_int($row['id']) || $row['id'] < 1 || isset($data[$table][$row['id']])) {
                    throw new RuntimeException("ID không hợp lệ hoặc trùng trong {$table}.");
                }
                if (++$total > 100000) {
                    throw new RuntimeException('File vượt giới hạn 100.000 bản ghi nội dung.');
                }
                $data[$table][$row['id']] = $row;
            }
        }
        if ($total === 0) {
            throw new RuntimeException('Không tìm thấy INSERT nội dung hợp lệ trong file.');
        }

        return $data;
    }

    private function columnNames(string $input): array
    {
        $columns = [];
        foreach (explode(',', $input) as $column) {
            if (! preg_match('/^\s*`?(\w+)`?\s*$/', $column, $match)) {
                throw new RuntimeException('Tên cột không hợp lệ.');
            }
            $columns[] = $match[1];
        }

        return $columns;
    }

    private function statements(string $sql): \Generator
    {
        $buffer = '';
        $quote = null;
        $length = strlen($sql);
        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $sql[$i + 1] ?? '';
            if ($quote !== null) {
                $buffer .= $char;
                if ($char === '\\') {
                    if (++$i >= $length) {
                        throw new RuntimeException('Chuỗi SQL chưa đóng.');
                    }
                    $buffer .= $sql[$i];
                } elseif ($char === $quote) {
                    if ($next === $quote) {
                        $buffer .= $sql[++$i];
                    } else {
                        $quote = null;
                    }
                }
            } elseif ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
                $buffer .= $char;
            } elseif (($char === '-' && $next === '-' && ctype_space($sql[$i + 2] ?? "\n")) || $char === '#') {
                while ($i < $length && $sql[$i] !== "\n") {
                    $i++;
                }
                $buffer .= "\n";
            } elseif ($char === '/' && $next === '*') {
                $end = strpos($sql, '*/', $i + 2);
                if ($end === false) {
                    throw new RuntimeException('Comment SQL chưa đóng.');
                }
                $i = $end + 1;
                $buffer .= ' ';
            } elseif ($char === ';') {
                if (trim($buffer) !== '') {
                    yield trim($buffer);
                }
                $buffer = '';
            } else {
                $buffer .= $char;
            }
        }
        if ($quote !== null || trim($buffer) !== '') {
            throw new RuntimeException('File SQL chưa hoàn chỉnh (thiếu dấu đóng chuỗi hoặc dấu chấm phẩy).');
        }
    }

    private function rows(string $input, string $table): \Generator
    {
        $i = 0;
        $length = strlen($input);
        $skip = function () use (&$i, $length, $input) {
            while ($i < $length && ctype_space($input[$i])) {
                $i++;
            }
        };
        do {
            $skip();
            if (($input[$i++] ?? '') !== '(') {
                throw new RuntimeException("VALUES không hợp lệ trong {$table}.");
            }
            $row = [];
            do {
                $skip();
                if (($input[$i] ?? '') === "'") {
                    $i++;
                    $value = '';
                    $closed = false;
                    while ($i < $length) {
                        $char = $input[$i++];
                        if ($char === '\\') {
                            $escape = $input[$i++] ?? '';
                            $value .= match ($escape) {
                                '0' => "\0", 'n' => "\n", 'r' => "\r", 't' => "\t", 'b' => "\x08", 'Z' => "\x1a",
                                '\\', "'", '"' => $escape,
                                default => throw new RuntimeException("Escape không hỗ trợ trong {$table}."),
                            };
                        } elseif ($char === "'") {
                            if (($input[$i] ?? '') === "'") {
                                $value .= "'";
                                $i++;
                            } else {
                                $closed = true;
                                break;
                            }
                        } else {
                            $value .= $char;
                        }
                    }
                    if (! $closed) {
                        throw new RuntimeException("Chuỗi chưa đóng trong {$table}.");
                    }
                } else {
                    $start = $i;
                    while ($i < $length && ! in_array($input[$i], [',', ')'], true)) {
                        $i++;
                    }
                    $literal = trim(substr($input, $start, $i - $start));
                    if (strcasecmp($literal, 'NULL') === 0) {
                        $value = null;
                    } elseif (preg_match('/^-?\d+$/D', $literal) && filter_var($literal, FILTER_VALIDATE_INT) !== false) {
                        $value = (int) $literal;
                    } else {
                        throw new RuntimeException("Chỉ chấp nhận chuỗi, số nguyên và NULL trong {$table}.");
                    }
                }
                $row[] = $value;
                $skip();
                $delimiter = $input[$i++] ?? '';
            } while ($delimiter === ',');
            if ($delimiter !== ')') {
                throw new RuntimeException("VALUES không hợp lệ trong {$table}.");
            }
            yield $row;
            $skip();
            if ($i === $length) {
                return;
            }
            if (($input[$i++] ?? '') !== ',') {
                throw new RuntimeException("Không nhận biểu thức hoặc ON DUPLICATE KEY trong {$table}.");
            }
        } while ($i <= $length);
    }
}
