<?php

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\Middleware;

class CsrfMiddleware
{
    /**
     * Valida o token CSRF em requisições POST/PUT/DELETE.
     * Requisições GET/HEAD/OPTIONS são ignoradas.
     */
    public function handle(Request $request): void
    {
        $method = $request->method();

        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return;
        }

        if (!Csrf::validate($request->csrfToken())) {
            http_response_code(419);

            if ($request->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['erro' => 'Token CSRF inválido. Recarregue a página.']);
                exit;
            }

            Session::flash('erro', 'Token de segurança expirado. Tente novamente.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }

        // Rotaciona o token após validação bem-sucedida
        Csrf::refresh();
    }
}