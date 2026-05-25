<?php

/**
 * ZAFENATE CONTROL — Bootstrap
 *
 * Carregado pelo public/index.php antes de qualquer coisa.
 * Responsabilidades:
 *   1. Constantes e ambiente
 *   2. Autoload
 *   3. Helpers
 *   4. Sessão
 *   5. Router + Rotas
 *   6. Dispatch
 */

// ----------------------------------------------------------------
// 1. Ambiente
// ----------------------------------------------------------------

define('APP_ROOT', dirname(__DIR__));
define('APP_ENV',  $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', APP_ENV !== 'production');

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

date_default_timezone_set('America/Sao_Paulo');

// ----------------------------------------------------------------
// 2. Autoload simples (PSR-4 manual)
//    Mapeia App\* → app/*  e  raiz do projeto
// ----------------------------------------------------------------

spl_autoload_register(function (string $class): void {
    // Remove namespace raiz App\
    $relative = str_replace('\\', '/', $class);

    // Tenta em app/ (namespace App\)
    if (str_starts_with($relative, 'App/')) {
        $file = APP_ROOT . '/' . lcfirst($relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    // Fallback: caminho direto
    $file = APP_ROOT . '/' . $relative . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ----------------------------------------------------------------
// 3. Helpers globais
// ----------------------------------------------------------------

require_once APP_ROOT . '/app/helpers/functions.php';

// ----------------------------------------------------------------
// 4. Carrega .env (simples, sem dependência externa)
// ----------------------------------------------------------------

$envFile = APP_ROOT . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\"'");
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

// ----------------------------------------------------------------
// 5. Sessão
// ----------------------------------------------------------------

use App\Core\Session;

Session::start();

// ----------------------------------------------------------------
// 6. Router — registra middlewares e rotas
// ----------------------------------------------------------------

use App\Core\Router;
use App\Core\Request;

$router = new Router();

// Aliases de middleware
$router->middleware('auth',  \App\Middleware\AuthMiddleware::class);
$router->middleware('guest', \App\Middleware\GuestMiddleware::class);
$router->middleware('csrf',  \App\Middleware\CsrfMiddleware::class);

require_once APP_ROOT . '/routes/web.php';

// ----------------------------------------------------------------
// 7. Dispatch
// ----------------------------------------------------------------

$request = new Request();

try {
    $router->dispatch($request);
} catch (\Throwable $e) {
    if (APP_DEBUG) {
        echo '<pre style="background:#1e1e1e;color:#ef4444;padding:20px;font-family:monospace;">';
        echo '<strong>' . get_class($e) . '</strong>: ' . htmlspecialchars($e->getMessage()) . "\n\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        http_response_code(500);
        // Em produção: renderize uma view de erro genérica
        echo 'Ocorreu um erro interno. Tente novamente mais tarde.';
    }
    error_log('[App] ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
}