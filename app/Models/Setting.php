<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::find($key)?->value ?? $default;
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    // Penanda versi untuk memaksa browser memuat ulang gambar saat QRIS diganti.
    public static function qrisVersion(): int
    {
        return static::find('qris_image')?->updated_at?->timestamp ?? 0;
    }

    // QRIS dianggap siap jika gambarnya sudah diunggah dan filenya benar-benar ada.
    public static function qrisConfigured(): bool
    {
        $path = static::get('qris_image');

        return $path !== null && $path !== '' && Storage::disk('public')->exists($path);
    }
}
