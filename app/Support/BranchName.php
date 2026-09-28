<?php

namespace App\Support;

class BranchName
{
    public static function format(?string $name, string $fallback = 'Unknown Branch'): string
    {
        $name = trim((string) $name);
        $name = preg_replace('/\s+branch$/i', '', $name) ?? $name;

        return $name === ''
            ? $fallback
            : mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    }
}
