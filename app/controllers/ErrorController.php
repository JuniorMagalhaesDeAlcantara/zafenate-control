<?php

namespace App\Controllers;

use App\Core\Controller;

class ErrorController extends Controller
{
    public function notFound(): void
    {
        http_response_code(404);
        // Quando tiver a view criada: $this->viewWithLayout('errors/404');
        echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>404 — Página não encontrada | Zafenate Control</title>
    <style>
        body { font-family: system-ui, sans-serif; display: flex; align-items: center;
               justify-content: center; height: 100vh; margin: 0; background: #f8fafc; }
        .box { text-align: center; }
        h1   { font-size: 6rem; margin: 0; color: #e2e8f0; font-weight: 900; }
        h2   { margin: 0 0 1rem; color: #334155; }
        a    { color: #3b82f6; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="box">
        <h1>404</h1>
        <h2>Página não encontrada</h2>
        <p>A página que você procura não existe ou foi movida.</p>
        <a href="/">← Voltar ao início</a>
    </div>
</body>
</html>';
        exit;
    }
}
