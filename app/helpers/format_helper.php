<?php

/**
 * format_helper.php
 * Funções utilitárias de formatação disponíveis em todas as views.
 */

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('formatarDocumento')) {
    /**
     * Formata CPF ou CNPJ (aceita com ou sem pontuação).
     * CPF:  000.000.000-00
     * CNPJ: 00.000.000/0000-00
     */
    function formatarDocumento(string $doc): string
    {
        $doc = preg_replace('/\D/', '', $doc);

        if (strlen($doc) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $doc);
        }

        if (strlen($doc) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $doc);
        }

        return $doc; // retorna como veio se não reconhecer
    }
}

if (!function_exists('formatarTelefone')) {
    function formatarTelefone(string $tel): string
    {
        $t = preg_replace('/\D/', '', $tel);

        if (strlen($t) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $t);
        }

        if (strlen($t) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $t);
        }

        return $tel;
    }
}

if (!function_exists('formatarMoeda')) {
    function formatarMoeda(float $valor, string $prefixo = 'R$ '): string
    {
        return $prefixo . number_format($valor, 2, ',', '.');
    }
}

if (!function_exists('active')) {
    /**
     * Retorna 'active' se a URI atual contiver o segmento informado.
     * Usado na sidebar para marcar o item de menu ativo.
     */
    function active(string $segmento): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return str_contains($uri, '/' . $segmento) ? 'active' : '';
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return \App\Core\Csrf::field();
    }
}

if (!function_exists('redirect')) {
    function redirect(string $uri): never
    {
        header('Location: ' . $uri);
        exit;
    }
}
