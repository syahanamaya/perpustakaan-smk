<?php

namespace App\Services;

use App\Models\Book;
use App\Models\FineSetting;

class FineCalculator
{
    public static function activeSetting(): ?FineSetting
    {
        return FineSetting::query()->where('is_active', true)->first()
            ?? FineSetting::query()->latest()->first();
    }

    public static function bookPrice(Book $book): int
    {
        return (int) ($book->price ?? $book->harga ?? $book->harga_buku ?? 0);
    }

    public static function damagePercent(?FineSetting $setting, string $level): int
    {
        $level = strtolower($level);
        $light = (int) ($setting?->damage_light_percent ?? 25);
        $medium = (int) ($setting?->damage_medium_percent ?? 50);
        $heavy = (int) ($setting?->damage_heavy_percent ?? 75);

        return match ($level) {
            'ringan' => max(0, min(100, $light)),
            'berat' => max(0, min(100, $heavy)),
            default => max(0, min(100, $medium)),
        };
    }

    public static function chargeableLateDays(int $lateDays, ?FineSetting $setting): int
    {
        $tolerance = (int) ($setting?->tolerance_days ?? 0);

        return max(0, $lateDays - $tolerance);
    }

    /**
     * Hitung denda dari harga buku + aturan, bukan dari input manual.
     *
     * @return array{
     *   book_price:int,
     *   damage_level:?string,
     *   damage_percent:int,
     *   late_fine:int,
     *   condition_fine:int,
     *   total_fine:int
     * }
     */
    public static function forReturn(
        Book $book,
        string $condition,
        ?string $damageLevel,
        int $lateDays,
        ?FineSetting $setting = null
    ): array {
        $setting = $setting ?? self::activeSetting();
        $bookPrice = self::bookPrice($book);
        $condition = strtolower($condition);
        $chargeableDays = self::chargeableLateDays($lateDays, $setting);
        $lateFine = $chargeableDays * (int) ($setting?->late_fee_per_day ?? 0);

        $percent = 0;
        $level = null;
        $conditionFine = 0;

        if ($condition === 'hilang') {
            $percent = 100;
            $conditionFine = $bookPrice;
        } elseif ($condition === 'rusak') {
            $level = strtolower($damageLevel ?: 'sedang');
            $percent = self::damagePercent($setting, $level);
            $conditionFine = (int) round($bookPrice * $percent / 100);
        }

        $maxPerBook = (int) ($setting?->max_fee_per_book ?? 0);
        $total = $lateFine + $conditionFine;
        if ($maxPerBook > 0) {
            $total = min($total, $maxPerBook);
        }

        if (($setting?->rounding_rule ?? '') === 'Dibulatkan ke atas (Ribuan)' && $total > 0) {
            $total = (int) (ceil($total / 1000) * 1000);
        }

        return [
            'book_price' => $bookPrice,
            'damage_level' => $level,
            'damage_percent' => $percent,
            'late_fine' => $lateFine,
            'condition_fine' => $conditionFine,
            'total_fine' => $total,
        ];
    }
}
