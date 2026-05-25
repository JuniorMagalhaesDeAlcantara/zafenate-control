<?php

// Carrega o .env manualmente (sem dependência externa)
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        die('Arquivo .env não encontrado.');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Ignora comentários e linhas vazias
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

// Carrega o .env (dois níveis acima de config/)
loadEnv(dirname(__DIR__) . '/.env');

// Helper global para pegar variáveis de ambiente com valor padrão
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    return ($value !== false && $value !== null && $value !== '') ? $value : $default;
}

// Configurações da aplicação
define('APP_NAME',    env('APP_NAME', 'Zafenate Control'));
define('APP_URL',     env('APP_URL',  'http://zafenate.local'));
define('APP_ENV',     env('APP_ENV',  'production'));
define('APP_DEBUG',   env('APP_DEBUG', 'false') === 'true');
define('APP_KEY',     env('APP_KEY',  ''));

// Caminhos base
define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH',ROOT_PATH . '/storage');
define('VIEW_PATH',   APP_PATH  . '/views');

// Timezone e locale
define('TIMEZONE', env('TIMEZONE', 'America/Sao_Paulo'));
define('LOCALE',   env('LOCALE',   'pt_BR'));

date_default_timezone_set(TIMEZONE);
setlocale(LC_ALL, LOCALE . '.UTF-8', LOCALE);

// Exibição de erros conforme ambiente
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Log de erros sempre ativo
ini_set('log_errors', 1);
ini_set('error_log', STORAGE_PATH . '/logs/app.log');