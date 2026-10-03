<?php

namespace App\Support;

class ClassLoanQuota
{
    public const LEVELS = ['X', 'XI', 'XII'];

    public static function defaults(): array
    {
        return [
            'X' => ['max_paket' => 4, 'max_bebas' => 2],
            'XI' => ['max_paket' => 4, 'max_bebas' => 2],
            'XII' => ['max_paket' => 5, 'max_bebas' => 2],
        ];
    }

    public static function path(): string
    {
        return storage_path('app/loan_quotas.json');
    }

    public static function all(): array
    {
        $merged = self::defaults();
        $path = self::path();

        if (is_file($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                foreach (self::LEVELS as $level) {
                    if (! isset($decoded[$level]) || ! is_array($decoded[$level])) {
                        continue;
                    }
                    $merged[$level]['max_paket'] = (int) ($decoded[$level]['max_paket'] ?? $merged[$level]['max_paket']);
                    $merged[$level]['max_bebas'] = (int) ($decoded[$level]['max_bebas'] ?? $merged[$level]['max_bebas']);
                }
            }
        }

        return $merged;
    }

    public static function forClass(?string $class): array
    {
        $level = strtoupper(trim((string) $class));
        $all = self::all();

        return $all[$level] ?? ['max_paket' => 4, 'max_bebas' => 2];
    }

    public static function save(array $byLevel): void
    {
        $payload = self::defaults();
        foreach (self::LEVELS as $level) {
            if (! isset($byLevel[$level]) || ! is_array($byLevel[$level])) {
                continue;
            }
            $payload[$level]['max_paket'] = max(0, min(20, (int) ($byLevel[$level]['max_paket'] ?? $payload[$level]['max_paket'])));
            $payload[$level]['max_bebas'] = max(0, min(20, (int) ($byLevel[$level]['max_bebas'] ?? $payload[$level]['max_bebas'])));
        }

        $dir = dirname(self::path());
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(self::path(), json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function mapForForm(): array
    {
        $out = [];
        foreach (self::all() as $level => $row) {
            $paket = (int) $row['max_paket'];
            $bebas = (int) $row['max_bebas'];
            $out[$level] = [
                'max_books' => $paket + $bebas,
                'max_paket' => $paket,
                'max_bebas' => $bebas,
            ];
        }

        return $out;
    }

    /** @return list<object{class_level: string, max_paket: int, max_bebas: int}> */
    public static function forHeadForm(): array
    {
        $rows = [];
        foreach (self::all() as $level => $row) {
            $rows[] = (object) [
                'class_level' => $level,
                'max_paket' => (int) $row['max_paket'],
                'max_bebas' => (int) $row['max_bebas'],
            ];
        }

        return $rows;
    }
}
