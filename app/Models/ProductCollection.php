<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

/**
 * Koleksi / aktivitas (Running, Yoga, Workout, ...). Berbeda dari kategori (satu per produk),
 * satu produk bisa masuk banyak koleksi. Tampil sebagai ubin "Belanja per aktivitas" di beranda
 * dan filter `?koleksi=slug` di katalog.
 */
#[Fillable(['name', 'slug', 'tagline', 'description', 'image_path', 'icon', 'tone', 'is_active', 'show_on_home', 'sort_order'])]
class ProductCollection extends Model
{
    use HasFactory;

    protected $table = 'collections';

    /** Warna ubin di beranda => [kelas ubin, kelas gelembung, label admin]. */
    public const TONES = [
        'cyan' => ['bg-brand-400 text-ink', 'bg-white/30', 'Biru NuFit'],
        'ink' => ['bg-ink text-white', 'bg-brand-400/20', 'Navy gelap'],
        'volt' => ['bg-volt text-ink', 'bg-white/40', 'Hijau stabilo'],
        'orange' => ['bg-accent-500 text-white', 'bg-white/15', 'Oranye'],
        'sky' => ['bg-brand-100 text-brand-900', 'bg-white/60', 'Biru muda'],
        'sand' => ['bg-sand-200 text-ink', 'bg-white/60', 'Abu pasir'],
    ];

    /** Ikon garis yang tersedia di <x-sport-icon> => label admin. */
    public const ICONS = [
        'running' => 'Lari / sepatu',
        'yoga' => 'Yoga & pilates',
        'workout' => 'Workout / dumbbell',
        'padel' => 'Padel / raket',
        'swimming' => 'Renang',
        'support' => 'Support & recovery',
        'essentials' => 'Botol / essentials',
        'apparel' => 'Pakaian',
        'hiking' => 'Hiking / outdoor',
        'earphones' => 'Earphone',
        'sparkles' => 'Kilau (umum)',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_on_home' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'collection_product', 'collection_id', 'product_id');
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    /** Kunci ikon untuk <x-sport-icon name>; null = ditebak dari nama. */
    public function iconName(): ?string
    {
        return array_key_exists((string) $this->icon, self::ICONS) ? $this->icon : null;
    }

    /** @return array{0: string, 1: string}|null kelas ubin & gelembung; null = warna bergiliran. */
    public function toneClasses(): ?array
    {
        $tone = self::TONES[(string) $this->tone] ?? null;

        return $tone ? [$tone[0], $tone[1]] : null;
    }

    public function url(): string
    {
        return route('products.index', ['koleksi' => $this->slug]);
    }
}
