<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;
use App\Core\Middleware;

class AuthMiddleware
{
    /**
     * Verifica se o usuário está autenticado.
     * Se não estiver, salva a URL de destino e redireciona para /login.
     */
    public function handle(Request $request): void
    {
        if (!Session::has('usuario_id')) {
            // Guarda a URL que o usuário queria acessar
            Session::flash('redirect_after_login', $request->uri());
            header('Location: /login');
            exit;
        }

        // Verificação extra: session não pode ser de outro IP (proteção básica)
        $ipAtual    = $_SERVER['REMOTE_ADDR'] ?? '';
        $ipSessao   = Session::get('_ip', $ipAtual);

        if ($ipAtual !== $ipSessao) {
            Session::destroy();
            Session::start();
            header('Location: /login');
            exit;
        }
    }
}