<?php

use App\Core\Session;


// ----------------------------------------------------------------
// Redirecionamento
// ----------------------------------------------------------------

if (!function_exists('redirect')) {
    function redirect(string $uri, int $code = 302): never
    {
        http_response_code($code);
        header("Location: {$uri}");
        exit;
    }
}

// ----------------------------------------------------------------
// Flash messages
// ----------------------------------------------------------------

if (!function_exists('flash')) {
    function flash(string $key, mixed $value): void
    {
        Session::flash($key, $value);
    }
}

if (!function_exists('old_flash')) {
    function old_flash(string $key, mixed $default = null): mixed
    {
        return Session::getFlash($key, $default);
    }
}

// ----------------------------------------------------------------
// Usuário autenticado
// ----------------------------------------------------------------

if (!function_exists('auth_user')) {
    /**
     * Retorna dados do usuário logado ou null.
     */
    function auth_user(): ?array
    {
        $data = Session::get('usuario');
        return is_array($data) ? $data : null;
    }
}

if (!function_exists('auth_check')) {
    function auth_check(): bool
    {
        return Session::has('usuario_id');
    }
}

if (!function_exists('auth_id')) {
    function auth_id(): ?int
    {
        $id = Session::get('usuario_id');
        return $id ? (int) $id : null;
    }
}

// ----------------------------------------------------------------
// Formatação
// ----------------------------------------------------------------

if (!function_exists('money')) {
    /**
     * Formata valor como moeda brasileira.
     * money(1234.5) → "R$ 1.234,50"
     */
    function money(float $value, bool $prefix = true): string
    {
        $formatted = number_format($value, 2, ',', '.');
        return $prefix ? "R$ {$formatted}" : $formatted;
    }
}

if (!function_exists('date_br')) {
    /**
     * Converte data do banco (Y-m-d) para formato brasileiro (d/m/Y).
     */
    function date_br(?string $date, bool $withTime = false): string
    {
        if (!$date) return '-';
        $format = $withTime ? 'd/m/Y H:i' : 'd/m/Y';
        $dt     = DateTime::createFromFormat('Y-m-d H:i:s', $date)
            ?: DateTime::createFromFormat('Y-m-d', $date);
        return $dt ? $dt->format($format) : $date;
    }
}

if (!function_exists('number_br')) {
    /**
     * Formata número no padrão PT-BR.
     * number_br(1234.5, 3) → "1.234,500"
     */
    function number_br(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', '.');
    }
}

// ----------------------------------------------------------------
// Segurança / Output
// ----------------------------------------------------------------

if (!function_exists('e')) {
    /**
     * Escapa HTML para output seguro nas views.
     * Substitui o htmlspecialchars verboso.
     */
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('asset')) {
    /**
     * Gera URL para assets públicos com cache-busting.
     * Adaptado para a public_html da HostGator.
     */
    function asset(string $path): string
    {
        // Alinhado para a pasta public_html da HostGator
        $fullPath = dirname(__DIR__, 2) . '/public_html/' . ltrim($path, '/');

        $version  = file_exists($fullPath) ? substr(md5_file($fullPath), 0, 8) : '0';

        // Retorna a URL correta apontando para a public_html (ou direto da raiz do domínio)
        return '/public_html/' . ltrim($path, '/') . '?v=' . $version;
    }
}

// ----------------------------------------------------------------
// Strings
// ----------------------------------------------------------------

if (!function_exists('str_limit')) {
    /**
     * Trunca string com reticências.
     * str_limit('Texto muito longo...', 20) → "Texto muito longo..."
     */
    function str_limit(string $str, int $limit, string $end = '…'): string
    {
        return mb_strlen($str) <= $limit ? $str : mb_substr($str, 0, $limit) . $end;
    }
}

if (!function_exists('slugify')) {
    function slugify(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[áàãâä]/u', 'a', $text);
        $text = preg_replace('/[éèêë]/u', 'e', $text);
        $text = preg_replace('/[íìîï]/u', 'i', $text);
        $text = preg_replace('/[óòõôö]/u', 'o', $text);
        $text = preg_replace('/[úùûü]/u', 'u', $text);
        $text = preg_replace('/[ç]/u', 'c', $text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}

// ----------------------------------------------------------------
// Debug (apenas em desenvolvimento)
// ----------------------------------------------------------------

if (!function_exists('dd')) {
    /**
     * Dump & Die — apenas em desenvolvimento.
     */
    function dd(mixed ...$vars): never
    {
        if (defined('APP_ENV') && APP_ENV === 'production') {
            exit;
        }
        echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:16px;border-radius:8px;font-size:13px;overflow:auto;">';
        foreach ($vars as $var) {
            var_dump($var);
            echo "\n";
        }
        echo '</pre>';
        exit;
    }

    if (!function_exists('csrf_field')) {
        /**
         * Gera o input hidden com o token CSRF para os formulários
         */
        function csrf_field(): string
        {
            $token = \App\Core\Csrf::token();
            return '<input type="hidden" name="_token" value="' . $token . '">';
        }
    }
    /**
     * Verifica se a rota atual corresponde ao link para aplicar a classe CSS 'active'
     */
    function active(string $rota): string
    {
        // Pega a URL atual (ex: /produtos ou /dashboard)
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Se a rota bater com o começo da URI, devolve a palavra 'active'
        return str_starts_with($uri, $rota) ? 'active' : '';
    }
}
