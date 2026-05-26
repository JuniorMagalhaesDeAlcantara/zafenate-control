<?php

namespace App\Core;

class Csrf
{
    private const KEY        = 'csrf_token';
    private const FIELD_NAME = '_csrf';        // nome do input hidden nos forms
    private const HEADER     = 'X-CSRF-TOKEN'; // header para requisições AJAX

    // ----------------------------------------------------------------
    // Geração
    // ----------------------------------------------------------------

    /**
     * Retorna o token atual da sessão, criando se não existir.
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::KEY];
    }

    /**
     * Retorna o input hidden pronto para colocar nos forms.
     * Uso na view: <?= $csrf ?> ou <?= Csrf::field() ?>
     */
    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::FIELD_NAME,
            htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
        );
    }

    // ----------------------------------------------------------------
    // Validação
    // ----------------------------------------------------------------

    /**
     * Valida o token recebido contra o da sessão.
     * Usa hash_equals para prevenir timing attacks.
     */
    public static function validate(string $token): bool
    {
        $sessionToken = $_SESSION[self::KEY] ?? '';
        return $sessionToken !== '' && hash_equals($sessionToken, $token);
    }

    // ----------------------------------------------------------------
    // Rotação (chame após validação bem-sucedida)
    // ----------------------------------------------------------------

    /**
     * Gera um novo token, invalidando o anterior.
     * Evita replay attacks em fluxos sensíveis.
     */
    public static function refresh(): void
    {
        $_SESSION[self::KEY] = bin2hex(random_bytes(32));
    }

    // ----------------------------------------------------------------
    // Helper para meta tag (AJAX via JS)
    // ----------------------------------------------------------------

    /**
     * Retorna a meta tag para uso no <head> do layout.
     * No JS: fetch(url, { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
     */
    public static function metaTag(): string
    {
        return sprintf(
            '<meta name="csrf-token" content="%s">',
            htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
        );
    }
}
