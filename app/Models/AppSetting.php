<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value'])]
class AppSetting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever(self::cacheKey($key), function () use ($key, $default) {
            $row = self::query()->find($key);

            return $row ? $row->value : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget(self::cacheKey($key));
    }

    private static function cacheKey(string $key): string
    {
        return 'app_setting:'.$key;
    }
}
