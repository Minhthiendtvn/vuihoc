<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Support\Str;

/**
 * Tự sinh slug từ tên khi admin để trống; nếu slug đã tồn tại
 * thì thêm hậu tố -2, -3... để bảo đảm duy nhất.
 */
trait ResolvesSlug
{
    protected function resolveSlug(string $modelClass, ?string $input, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($input ?: $source) ?: 'muc';
        $slug = $base;
        $i = 2;

        while (
            $modelClass::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
