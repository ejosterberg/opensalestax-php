<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax\Internal;

/**
 * Type-safe scalar extraction from JSON-decoded arrays.
 *
 * `json_decode(..., true)` returns `array<string, mixed>`; PHPStan max
 * (rightly) refuses bare `(string) $arr['key']` casts because they'd error
 * on nested array values. These helpers narrow `mixed` to the expected
 * scalar shape and fall back to the default when the type doesn't match.
 *
 * Internal: not part of the public API; keeps DTO factories readable.
 */
final class Decode
{
    /** @param array<string, mixed> $data */
    public static function str(array $data, string $key, string $default = ''): string
    {
        $v = $data[$key] ?? null;
        if (is_string($v)) {
            return $v;
        }
        if (is_int($v) || is_float($v) || is_bool($v)) {
            return (string) $v;
        }
        return $default;
    }

    /** @param array<string, mixed> $data */
    public static function nullableStr(array $data, string $key): ?string
    {
        $v = $data[$key] ?? null;
        if ($v === null) {
            return null;
        }
        if (is_string($v)) {
            return $v;
        }
        if (is_int($v) || is_float($v) || is_bool($v)) {
            return (string) $v;
        }
        return null;
    }

    /** @param array<string, mixed> $data */
    public static function int(array $data, string $key, int $default = 0): int
    {
        $v = $data[$key] ?? null;
        if (is_int($v)) {
            return $v;
        }
        if (is_float($v)) {
            return (int) $v;
        }
        if (is_string($v) && is_numeric($v)) {
            return (int) $v;
        }
        return $default;
    }

    /** @param array<string, mixed> $data */
    public static function bool(array $data, string $key, bool $default = false): bool
    {
        $v = $data[$key] ?? null;
        if (is_bool($v)) {
            return $v;
        }
        if (is_int($v)) {
            return $v !== 0;
        }
        return $default;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function arr(array $data, string $key): array
    {
        $v = $data[$key] ?? null;
        return is_array($v) ? $v : [];
    }

    /**
     * @param array<string, mixed> $data
     * @return list<array<string, mixed>>
     */
    public static function listOfArrays(array $data, string $key): array
    {
        $v = $data[$key] ?? null;
        if (!is_array($v)) {
            return [];
        }
        $out = [];
        foreach ($v as $entry) {
            if (is_array($entry)) {
                $out[] = $entry;
            }
        }
        return $out;
    }
}
