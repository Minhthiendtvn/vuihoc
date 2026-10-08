<?php

namespace App\Http\Controllers\Identity\Concerns;

/**
 * Dữ liệu dùng chung cho form hồ sơ: danh sách emoji avatar và cỡ chữ.
 * Đặt ở đây để ProfileController và ParentController dùng chung,
 * tránh sửa model dùng chung với các agent khác.
 */
trait HasAvatarOptions
{
    /** ~12 emoji cho học viên chọn làm avatar. */
    public static function avatarEmojis(): array
    {
        return ['🦊', '🐱', '🐶', '🐼', '🐯', '🦁', '🐸', '🐵', '🐧', '🦄', '🐝', '🦋'];
    }

    /** Nhãn tiếng Việt cho các cỡ chữ của hồ sơ. */
    public static function fontSizeOptions(): array
    {
        return [
            'normal' => 'Bình thường',
            'large'  => 'Chữ lớn',
            'xlarge' => 'Chữ rất lớn',
        ];
    }

    /** Gói dữ liệu truyền xuống view form hồ sơ. */
    protected function avatarFormData(): array
    {
        return [
            'emojis' => static::avatarEmojis(),
            'fontSizes' => static::fontSizeOptions(),
        ];
    }

    /** Rule validation cho trường avatar_emoji. */
    protected function avatarEmojiRule(): string
    {
        return 'in:' . implode(',', static::avatarEmojis());
    }
}
