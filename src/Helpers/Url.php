<?php

declare(strict_types=1);

namespace Swarm\Helpers;

use Swarm\Models\Setting;

/**
 * Url - Centralized URL construction for control app and tenant workspaces.
 */
class Url
{
    public static function control(string $path = ''): string
    {
        return self::join(self::controlAppUrl(), $path);
    }

    public static function controlAppUrl(): string
    {
        $configured = self::setting('control_app_url')
            ?: ($_ENV['CONTROL_APP_URL'] ?? getenv('CONTROL_APP_URL') ?: '');

        if ($configured !== '') {
            return self::normalizeAbsoluteUrl($configured);
        }

        if (php_sapi_name() !== 'cli' && !empty($_SERVER['HTTP_HOST'])) {
            return self::currentOrigin();
        }

        return 'https://' . self::baseDomain();
    }

    public static function workspace(string|array $instanceOrSlug, string $path = ''): string
    {
        if (is_array($instanceOrSlug)) {
            $host = (string) ($instanceOrSlug['subdomain'] ?? '');
            if ($host === '' && isset($instanceOrSlug['slug'])) {
                $host = $instanceOrSlug['slug'] . '.' . self::baseDomain();
            }
        } else {
            $host = $instanceOrSlug . '.' . self::baseDomain();
        }

        return self::join('https://' . $host, $path);
    }

    public static function baseDomain(): string
    {
        $configured = self::setting('base_domain')
            ?: ($_ENV['BASE_DOMAIN'] ?? getenv('BASE_DOMAIN') ?: 'localhost');

        return self::normalizeDomain($configured) ?: 'localhost';
    }

    public static function normalizeDomain(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (!preg_match('#^[a-z][a-z0-9+\-.]*://#i', $value)) {
            $value = 'https://' . $value;
        }

        $host = parse_url($value, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return '';
        }

        $port = parse_url($value, PHP_URL_PORT);
        return strtolower($host) . (is_int($port) ? ':' . $port : '');
    }

    public static function normalizeAbsoluteUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (!preg_match('#^[a-z][a-z0-9+\-.]*://#i', $value)) {
            $value = 'https://' . $value;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
        $host = parse_url($value, PHP_URL_HOST);
        if (!in_array($scheme, ['http', 'https'], true) || !is_string($host) || $host === '') {
            return '';
        }

        $port = parse_url($value, PHP_URL_PORT);
        $authority = strtolower($host) . (is_int($port) ? ':' . $port : '');

        return $scheme . '://' . $authority;
    }

    public static function currentOrigin(): string
    {
        $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO']
            ?? $_SERVER['REQUEST_SCHEME']
            ?? ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');

        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scheme = strtolower(trim(explode(',', (string) $scheme)[0]));
        $host = strtolower(trim(explode(',', (string) $host)[0]));

        return $scheme . '://' . $host;
    }

    public static function currentRequestPath(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public static function isCurrentControlRequest(): bool
    {
        $control = self::normalizeAbsoluteUrl(self::controlAppUrl());
        $current = self::normalizeAbsoluteUrl(self::currentOrigin());

        return $control !== '' && $current !== '' && $control === $current;
    }

    private static function join(string $base, string $path): string
    {
        $base = rtrim($base, '/');
        if ($path === '') {
            return $base;
        }

        return $base . '/' . ltrim($path, '/');
    }

    private static function setting(string $key): string
    {
        try {
            return (string) (Setting::get($key, '') ?: '');
        } catch (\Throwable) {
            return '';
        }
    }
}
