<?php

namespace App\Core;

class Csrf
{
    private const TOKEN_KEY = '_csrf_token';
    private const TOKEN_LEN = 32;

    public static function generate(): string
    {
        if (!Session::has(self::TOKEN_KEY)) {
            Session::set(self::TOKEN_KEY, bin2hex(random_bytes(self::TOKEN_LEN)));
        }

        return Session::get(self::TOKEN_KEY);
    }

    public static function validate(string $token): bool
    {
        $stored = Session::get(self::TOKEN_KEY, '');

        if (empty($stored) || empty($token)) {
            return false;
        }

        // hash_equals previne timing attacks
        return hash_equals($stored, $token);
    }

    public static function refresh(): void
    {
        Session::set(self::TOKEN_KEY, bin2hex(random_bytes(self::TOKEN_LEN)));
    }

    // Retorna o campo hidden para usar nos formulários
    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function token(): string
    {
        return self::generate();
    }
}