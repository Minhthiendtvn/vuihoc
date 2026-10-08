<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VuiHoc - Cấu hình chung của nền tảng
    |--------------------------------------------------------------------------
    | File config tập trung: tên nền tảng, loại game, độ khó, bảng XP theo cấp,
    | điểm XP cho từng hành động. Agent các phase sau đọc config này,
    | KHÔNG hardcode số liệu vào controller/service.
    */

    'name' => 'VuiHoc',

    // 4 kiểu chơi game (phase 2 - Gameplay)
    'game_types' => [
        'quiz'     => 'Trắc nghiệm',
        'matching' => 'Ghép cặp',
        'sort'     => 'Kéo-thả sắp xếp',
        'fill'     => 'Điền từ',
    ],

    'difficulties' => [
        'de'         => 'Dễ',
        'trung_binh' => 'Trung bình',
        'kho'        => 'Khó',
    ],

    // XP tích lũy cần để ĐẠT cấp (level N cần >= xp này)
    'levels' => [
        1  => 0,
        2  => 100,
        3  => 250,
        4  => 450,
        5  => 700,
        6  => 1000,
        7  => 1400,
        8  => 1900,
        9  => 2500,
        10 => 3200,
    ],

    // Điểm XP cho từng hành động đúng
    'xp' => [
        'quiz_correct'         => 10,  // mỗi câu trắc nghiệm đúng
        'quiz_time_bonus_max'  => 5,   // thưởng thêm tối đa khi trả lời nhanh
        'matching_pair'        => 5,   // mỗi cặp ghép đúng
        'sort_item'            => 5,   // mỗi item sắp xếp đúng vị trí
        'fill_blank'           => 10,  // mỗi chỗ trống điền đúng
        'perfect_bonus_percent'=> 20,  // thưởng % khi đạt 100% lượt chơi
        'streak_day'           => 2,   // XP thưởng mỗi ngày chuỗi học
    ],
];
