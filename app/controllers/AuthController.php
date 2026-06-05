<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;

class AuthController extends Controller
{
    /**
     * Exibe a tela de login
     */
    public function showLogin(): void
    {
        // Se o usuário já estiver logado, joga direto para o dashboard
        if (auth_check()) {
            redirect('/dashboard');
        }

        // Renderiza a view de login (vamos criar a pasta e o arquivo HTML/PHP depois)
        // Isso vai buscar em app/views/auth/login.php
        $this->view('auth/login', [
            'title' => 'Login — Zafenate Control'
        ]);
    }

    /**
     * Processa o envio do formulário de login (POST)
     */
    public function login(\App\Core\Request $request): void
    {
        // 1. Captura os dados do formulário
        $email = trim($request->input('email', ''));
        $senha = $request->input('senha', '');

        // 2. Validação simples
        if (empty($email) || empty($senha)) {
            flash('error', 'Por favor, preencha todos os campos.');
            redirect('/login');
        }

        // 3. Busca usuário
        $modelUsuario = new \App\Models\Usuario();
        $usuario = $modelUsuario->buscarPorEmail($email);

        // 4. Valida senha
        if ($usuario && password_verify($senha, $usuario['senha'])) {

            // Busca perfil do usuário
            $perfil = null;

            if (!empty($usuario['perfil_id'])) {
                $perfilModel = new \App\Models\Perfil();
                $perfil = $perfilModel->buscarPorId((int)$usuario['perfil_id']);
            }

            // Dados básicos
            \App\Core\Session::set('usuario_id', $usuario['id']);
            \App\Core\Session::set('usuario_nome', $usuario['nome']);
            \App\Core\Session::set('usuario_nivel', $usuario['nivel']);
            \App\Core\Session::set('usuario', $usuario);

            // Dados do perfil
            \App\Core\Session::set('perfil_id', $usuario['perfil_id'] ?? null);
            \App\Core\Session::set('perfil_nome', $perfil['nome'] ?? null);

            // Permissões do perfil
            \App\Core\Session::set(
                'permissoes',
                $perfil['permissoes'] ?? []
            );
          
            redirect('/dashboard');
        }

        // 5. Falha no login
        flash('error', 'E-mail ou senha incorretos.');
        redirect('/login');
    }
    /**
     * Faz o logout do usuário
     */
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        redirect('/login');
    }
}
