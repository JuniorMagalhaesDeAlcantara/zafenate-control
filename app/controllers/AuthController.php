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
    public function login(\App\Core\Request $request): void // <-- Adicione o parâmetro aqui!
    {
        // ❌ Remova a linha " $request = new \App\Core\Request(); " se tiver colocado ela aqui dentro antes.

        // 1. Captura os dados do formulário usando o objeto injetado pelo roteador
        $email = trim($request->input('email', ''));
        $senha = $request->input('senha', '');

        // 2. Validação simples de campos vazios
        if (empty($email) || empty($senha)) {
            flash('error', 'Por favor, preencha todos os campos.');
            redirect('/login');
        }

        // 3. Procura o usuário no banco de dados instanciando o Model no seu padrão
        $modelUsuario = new \App\Models\Usuario();
        $usuario = $modelUsuario->buscarPorEmail($email);

        // 4. Se o usuário existir, verifica se a senha bate com o hash criptografado
        if ($usuario && password_verify($senha, $usuario['senha'])) {

            // Registra as variáveis na Sessão utilizando a sua classe Core Session
            \App\Core\Session::set('usuario_id', $usuario['id']);
            \App\Core\Session::set('usuario_nome', $usuario['nome']);
            \App\Core\Session::set('usuario_nivel', $usuario['nivel']);
            \App\Core\Session::set('usuario', $usuario);

            // Login feito com sucesso! Redireciona para o Dashboard
            redirect('/dashboard');
        }

        // 5. Se falhar o e-mail ou a senha, devolve para o login com mensagem de erro
        flash('error', 'E-mail ou senha incorretos.');
        redirect('/login');
    }

    /**
     * Faz o logout do usuário
     */
    public function logout(): void
    {
        // Limpa a sessão usando o helper/core e joga para o login
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        redirect('/login');
    }
}
