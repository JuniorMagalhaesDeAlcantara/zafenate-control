<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;
use App\Core\Middleware;

class GuestMiddleware
{
    /**
     * Usuário já logado não deve ver login/registro.
     * Redireciona para o dashboard.
     */
    public function handle(Request $request): void
    {
        if (Session::has('usuario_id')) {
            header('Location: /dashboard');
            exit;
        }
    }
}