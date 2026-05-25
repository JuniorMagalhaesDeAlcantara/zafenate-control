<?php

namespace App\Core;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        // Configurações de segurança antes de iniciar
        ini_set('session.use_strict_mode',    '1');
        ini_set('session.use_only_cookies',   '1');
        ini_set('session.use_trans_sid',      '0');
        ini_set('session.cookie_httponly',    '1');
        ini_set('session.cookie_samesite',    'Strict');

        // HTTPS em produção
        if (APP_ENV === 'production') {
            ini_set('session.cookie_secure', '1');
        }

        $lifetime = (int) env('SESSION_LIFETIME', 7200);
        $name     = env('SESSION_NAME', 'zafenate_session');

        session_name($name);
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => APP_ENV === 'production',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        session_start();
        self::$started = true;

        // Regenera ID periodicamente (anti-fixation)
        if (!self::has('_last_regenerated')) {
            self::regenerate();
        } elseif ((time() - self::get('_last_regenerated')) > 300) {
            self::regenerate();
        }
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
        self::set('_last_regenerated', time());
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
        self::$started = false;
    }
}