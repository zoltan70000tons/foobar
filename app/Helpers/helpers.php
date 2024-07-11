<?php 

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