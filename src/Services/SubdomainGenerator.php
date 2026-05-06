<?php

declare(strict_types=1);

namespace Swarm\Services;

use Swarm\Models\Instance;

/**
 * SubdomainGenerator — Create URL-safe slugs from business names.
 *
 * Given "Sable & Lune Perfume Studio" → "sable-lune-perfume-studio"
 * Handles collisions by appending -2, -3, etc.
 */
class SubdomainGenerator
{
    /** Slugs that must never be used for instances. */
    private const RESERVED = [
        'www', 'app', 'api', 'mail', 'admin', 'dashboard',
        'swarm', 'operator', 'static', 'assets', 'demo',
        'status', 'health', 'login', 'logout',
        'signup', 'install', 'support', 'help', 'billing', 'account',
    ];

    /** Common transliteration map for non-ASCII characters. */
    private const TRANSLITERATIONS = [
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ñ' => 'n', 'ç' => 'c', 'ß' => 'ss', 'ø' => 'o', 'æ' => 'ae',
        'À' => 'a', 'Á' => 'a', 'Â' => 'a', 'Ã' => 'a', 'Ä' => 'a', 'Å' => 'a',
        'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e',
        'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i',
        'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o',
        'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u',
        'Ñ' => 'n', 'Ç' => 'c', 'Ø' => 'o', 'Æ' => 'ae',
    ];

    /**
     * Generate a unique slug from a business name.
     */
    public static function generate(string $name): string
    {
        // 1. Lowercase
        $slug = mb_strtolower($name);

        // 2. Transliterate non-ASCII
        $slug = strtr($slug, self::TRANSLITERATIONS);

        // 3. Replace non-alphanumeric with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // 4. Collapse consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);

        // 5. Strip leading/trailing hyphens
        $slug = trim($slug, '-');

        // 6. Truncate to 40 characters at a word boundary
        if (strlen($slug) > 40) {
            $slug = substr($slug, 0, 40);
            $lastHyphen = strrpos($slug, '-');
            if ($lastHyphen !== false && $lastHyphen > 10) {
                $slug = substr($slug, 0, $lastHyphen);
            }
        }

        // 7. Check reserved list
        if (in_array($slug, self::RESERVED, true)) {
            $slug .= '-site';
        }

        // 8. Check collision — append -2, -3 etc.
        $base = $slug;
        $counter = 1;
        while (Instance::slugExists($slug)) {
            $counter++;
            $slug = $base . '-' . $counter;
        }

        return $slug;
    }

    /**
     * Check whether a manually supplied slug conflicts with control/system routes.
     */
    public static function isReserved(string $slug): bool
    {
        return in_array(strtolower($slug), self::RESERVED, true);
    }
}
