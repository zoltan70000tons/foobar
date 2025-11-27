<?php

use Carbon\Carbon;
use Illuminate\Support\Str;

if (! function_exists('generateSlug')) {
    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param  string  $string
     * @param  string  $separator
     * @return string
     */
    function generateSlug($string, $separator = '-')
    {
        return Str::slug($string, $separator);
    }
}

if (! function_exists('sanitizeInput')) {
    /**
     * Sanitize input fields by removing unwanted characters.
     *
     * @param  string  $input
     * @param  array|null  $allowedTags
     * @return string
     */
    if (! function_exists('sanitizeInput')) {
        /**
         * Sanitize input fields by removing unwanted characters.
         *
         * @param  string  $input
         * @param  array|null  $allowedTags
         * @return string
         */
        function sanitizeInput($input, $allowedTags = null)
        {
            // Predefined dangerous characters to remove if no allowedTags are specified
            $dangerousCharacters = [
                "'",       // Single quote
                '"',       // Double quote
                ';',       // Semicolon
                '<',       // Less than
                '>',       // Greater than
                '\\',      // Backslash
                '/',       // Forward slash
                '`',       // Backtick
                '--',      // SQL comments
                '#',       // MySQL comments
                '%',       // Wildcard
                '*',       // SQL wildcard
                '=',       // Equals
                '(',
                ')',  // Parentheses
            ];

            // If allowedTags are specified, strip all other tags
            if ($allowedTags) {
                $sanitized = strip_tags($input, implode('', $allowedTags));
            } else {
                // Remove all HTML tags if no allowedTags are specified
                $sanitized = strip_tags($input);

                // Remove dangerous characters
                foreach ($dangerousCharacters as $char) {
                    $sanitized = str_replace($char, '', $sanitized);
                }
            }

            // Remove invisible characters and unnecessary spaces
            $sanitized = preg_replace('/[\x00-\x1F\x7F]/u', '', $sanitized);
            $sanitized = trim($sanitized);

            // Return sanitized input
            return $sanitized;
        }
    }
}


if (!function_exists('formatCurrency')) {
    /**
     * Format currency amount based on language.
     *
     * @param float|int|string $amount
     * @param bool $hideCurrency
     * @param string $lang
     * @param bool $hideDecimals
     * @return string
     */
    function formatCurrency($amount, $hideCurrency = false, $lang = 'en', $hideDecimals = false): string
    {
        if (!is_numeric($amount)) {
            return '';
        }

        $amount = (float) $amount;

        // Set locale-specific formatting
        switch ($lang) {
            case 'de':
                $decimal = ',';
                $thousand = '.';
                $currency = $hideCurrency ? '' : 'USD ';
                break;
            case 'es':
                $decimal = '.';
                $thousand = ',';
                $currency = $hideCurrency ? '' : 'USD ';
                break;
            default: // 'en'
                $decimal = '.';
                $thousand = ',';
                $currency = $hideCurrency ? '' : 'USD ';
                break;
        }

        $decimals = $hideDecimals ? 0 : 2;
        $formatted = number_format($amount, $decimals, $decimal, $thousand);

        return $currency . $formatted;
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date, $fullMonth = false, $hideYear = false)
    {
        if (empty($date)) return null;
        $format = ($fullMonth ? 'F' : 'M') . ' d' . ($hideYear ? '' : ', Y');
        return Carbon::parse($date)->format($format);
    }
}

if (!function_exists('formatFeeName')) {
    function formatFeeName($string)
    {
        return ucwords(strtolower(str_replace('_', ' ', $string)));
    }
}

if (!function_exists('capitalizeWords')) {
    function capitalizeWords(string $text): string
    {
        return ucwords(strtolower($text));
    }
}


if (!function_exists('getPassengerOrderLabel')) {
    function getPassengerOrderLabel(int $order, string $lang = 'en', string $format = 'number'): string
    {
        if ($order < 1) return '';

        $lang = in_array($lang, ['en', 'es', 'de']) ? $lang : 'en';
        app()->setLocale($lang);
        $lead = __('passengers.lead');
        $ordinals  = __('passengers.ordinals');
        $suffix    = __('passengers.suffix');
        $fallback  = __('passengers.fallback');

        $numSuffixGenerators = [
            'en' => fn($n) => match ($n % 100) {
                11, 12, 13 => 'th',
                default => match ($n % 10) {
                    1 => 'st',
                    2 => 'nd',
                    3 => 'rd',
                    default => 'th'
                }
            },
            'es' => fn($n) => '°',
            'de' => fn($n) => '.',
        ];

        $numSuffix = $numSuffixGenerators[$lang]($order);

        if ($order === 1) {
            return $lead;
        }

        if ($format === 'words') {
            if (is_array($ordinals) && isset($ordinals[$order])) {
                return "{$ordinals[$order]} {$suffix}";
            }

            if (str_contains($fallback, ':orderth')) {
                return str_replace(':orderth', $order . $numSuffix, $fallback);
            }
            return str_replace(':order', $order, $fallback);
        }

        if (str_contains($fallback, ':orderth')) {
            return str_replace(':orderth', $order . $numSuffix, $fallback);
        }
        if (str_contains($fallback, ':order')) {
            return str_replace(':order', $order, $fallback);
        }

        return "{$order}{$numSuffix} {$suffix}";
    }
}
