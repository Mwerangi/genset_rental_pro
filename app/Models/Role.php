<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Role extends Model
{
    protected $fillable = ['key', 'label', 'description', 'badge_color', 'is_system', 'sort_order'];

    protected $casts = ['is_system' => 'boolean'];

    // ── Cache helpers ──────────────────────────────────────────────────────────

    public static function allCached(): Collection
    {
        return Cache::remember('roles_all', 3600, fn () => static::orderBy('sort_order')->get());
    }

    public static function clearCache(): void
    {
        Cache::forget('roles_all');
    }

    // ── Lookup helpers ─────────────────────────────────────────────────────────

    public static function labelFor(string $key): string
    {
        return static::allCached()->firstWhere('key', $key)?->label
            ?? ucwords(str_replace('_', ' ', $key));
    }

    public static function badgeColorFor(string $key): string
    {
        return static::allCached()->firstWhere('key', $key)?->badge_color
            ?? 'bg-gray-100 text-gray-700';
    }

    /** Returns [ 'key' => 'Label', ... ] — same shape as the old User::roles() */
    public static function asKeyLabel(): array
    {
        return static::allCached()->pluck('label', 'key')->toArray();
    }

    // ── Available badge colors (must be listed here for Tailwind to keep them) ─

    public static function availableColors(): array
    {
        return [
            'bg-red-100 text-red-800'     => 'Red',
            'bg-orange-100 text-orange-800' => 'Orange',
            'bg-yellow-100 text-yellow-800' => 'Yellow',
            'bg-green-100 text-green-800' => 'Green',
            'bg-teal-100 text-teal-800'   => 'Teal',
            'bg-cyan-100 text-cyan-800'   => 'Cyan',
            'bg-blue-100 text-blue-800'   => 'Blue',
            'bg-indigo-100 text-indigo-800' => 'Indigo',
            'bg-purple-100 text-purple-800' => 'Purple',
            'bg-gray-100 text-gray-700'   => 'Gray',
        ];
    }

    // ── Key generation ─────────────────────────────────────────────────────────

    public static function generateKey(string $label): string
    {
        return Str::snake(Str::slug($label, '_'));
    }
}
