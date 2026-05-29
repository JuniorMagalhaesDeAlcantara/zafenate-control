<?php

/**
 * ZAFENATE CONTROL — Bootstrap
 * (Caminho: C:\www\Zafenate_Control\bootstrap.php)
 */

// ----------------------------------------------------------------
// 1. Definições de Ambiente e Constantes
// ----------------------------------------------------------------
define('APP_ROOT', __DIR__);

// 2. Carrega as Configurações do Projeto e o .env de uma vez só
if (file_exists(APP_ROOT . '/config/config.php')) {
    require_once APP_ROOT . '/config/config.php';
}

// 3. Ativa exibição de erros baseado no APP_DEBUG configurado
if (defined('APP_DEBUG') && APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

date_default_timezone_set('America/Sao_Paulo');


// ----------------------------------------------------------------
// 4. Autoload (PSR-4)
// ----------------------------------------------------------------
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $relative = substr($class, 4);
        $file = APP_ROOT . '/app/' . str_replace('\\', '/', $relative) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
});


// ----------------------------------------------------------------
// 5. Helpers Globais (Funções utilitárias como money, date_br, dd)
// ----------------------------------------------------------------
if (file_exists(APP_ROOT . '/app/helpers/functions.php')) {
    require_once APP_ROOT . '/app/helpers/functions.php';
}

if (file_exists(APP_ROOT . '/app/helpers/format_helper.php')) {
    require_once APP_ROOT . '/app/helpers/format_helper.php';
}


// ----------------------------------------------------------------
// 6. Inicialização do Sistema (Sessão de forma segura e Rotas)
// ----------------------------------------------------------------
use App\Core\Session;
use App\Core\Router;
use App\Core\Request;

// Agora o Session::start() sabe o que é env() porque o config.php rodou na linha 13!
Session::start();

// Cria o roteador do sistema
$router = new Router();

// Registra os aliases dos middlewares
$router->middleware('auth',  \App\Middleware\AuthMiddleware::class);
$router->middleware('guest', \App\Middleware\GuestMiddleware::class);
$router->middleware('csrf',  \App\Middleware\CsrfMiddleware::class);

// Carrega as rotas protegidas e públicas
if (file_exists(APP_ROOT . '/routes/web.php')) {
    require_once APP_ROOT . '/routes/web.php';
}


// ----------------------------------------------------------------
// 7. Execução (Dispatch)
// ----------------------------------------------------------------
$request = new Request();

try {
    $router->dispatch($request);
} catch (\Throwable $e) {
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo '<pre style="background:#1e1e1e;color:#ef4444;padding:20px;font-family:monospace;border-radius:8px;overflow:auto;">';
        echo '<strong>' . get_class($e) . '</strong>: ' . htmlspecialchars($e->getMessage()) . "\n\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        http_response_code(500);
        echo 'Ocorreu um erro interno no servidor.';
    }
}
