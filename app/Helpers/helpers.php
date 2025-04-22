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
                '(', ')',  // Parentheses
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

    if (!function_exists('formatCurrency')) {
        function formatCurrency(float $amount, $hideCurrency = false): string
        {
            return ($hideCurrency ? '' : 'USD ') . number_format($amount, 2, '.', ',');
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
    
    
}