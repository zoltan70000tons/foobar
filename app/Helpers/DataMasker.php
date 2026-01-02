<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class DataMasker {
    public static function maskMiddle(
        string $value,
        int $visibleStart = 3,
        int $visibleEnd = 0,
        string $maskChar = '*',
    ): string {
        $length = mb_strlen($value);

        if ($length <= $visibleStart + $visibleEnd) {
            return str_repeat($maskChar, $length);
        }

        return mb_substr($value, 0, $visibleStart) .
            str_repeat($maskChar, $length - ($visibleStart + $visibleEnd)) .
            ($visibleEnd > 0 ? mb_substr($value, -$visibleEnd) : '');
    }

    /**
     * Mask email but keep domain readable
     * john.doe@email.com -> jo****@email.com
     */
    public static function maskEmail(string $email): string {
        if (!str_contains($email, '@')) {
            return self::maskMiddle($email);
        }

        [$name, $domain] = explode('@', $email, 2);

        return self::maskMiddle($name, 2) . '@' . $domain;
    }

    /**
     * +48123456789 -> +48******89
     */
    public static function maskPhone(string $phone): string {
        return self::maskMiddle($phone, 3, 2);
    }

    /**
     * 939 Strosin Plaza -> 939 *****
     */
    public static function maskAddress(string $address): string {
        $parts = preg_split('/\s+/', trim($address));

        if (count($parts) <= 1) {
            return self::maskMiddle($address);
        }

        return $parts[0] . ' ' . Str::repeat('*', 5);
    }

    /**
     * TEST EFREN JAKAYLA RIPPIN -> TEST *****
     */
    public static function maskName(string $name): string {
        $parts = preg_split('/\s+/', trim($name));

        return $parts[0] . ' ' . Str::repeat('*', 5);
    }

    /**
     * Mask dob YYYY-MM-DD -> ****-**-DD
     */
    public static function maskDob(string $dob): string {
        $length = mb_strlen($dob);

        if ($length !== 10) {
            return self::maskMiddle($dob);
        }

        return '****-**-' . mb_substr($dob, -2);
    }

    /**
     * Mask postal code 12345 -> ***45
     */
    public static function maskPostalCode(string $postalCode): string {
        return self::maskMiddle($postalCode, 0, 2);
    }

    /**
     * Generic mask using Str::mask (offset-based)
     */
    public static function maskOffset(string $value, int $start = 3, string $maskChar = '*'): string {
        return Str::mask($value, $maskChar, $start);
    }
}
