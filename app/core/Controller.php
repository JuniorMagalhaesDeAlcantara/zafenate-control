<?php

namespace App\Core;

abstract class Controller
{
    // ----------------------------------------------------------------
    // View
    // ----------------------------------------------------------------

    /**
     * Renderiza uma view passando variáveis.
     *
     * Uso no controller:
     *   $this->view('produtos/index', ['produtos' => $lista]);
     *
     * Caminho resolvido: app/views/{$view}.php
     */
    protected function view(string $view, array $data = []): void
    {
        // 1. Aceita tanto ponto (auth.login) quanto barras (auth/login ou auth\login)
        $viewSanitizada = str_replace(['.', '/', '\\'], DIRECTORY_SEPARATOR, $view);

        // 2. Monta o caminho usando o separador correto do sistema operacional
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $viewSanitizada . '.php';

        if (!file_exists($path)) {
            throw new \RuntimeException("View '{$view}' não encontrada em: {$path}");
        }

        // Extrai variáveis para o escopo da view
        extract($data, EXTR_SKIP);

        // Helpers disponíveis em todas as views
        $csrf  = Csrf::field();
        $token = Csrf::token();

        require $path;
    }

    /**
     * Renderiza view dentro de um layout.
     *
     * Uso:
     *   $this->viewWithLayout('produtos/index', ['produtos' => $lista], 'main');
     *
     * O layout fica em app/views/layouts/{$layout}.php
     * Dentro do layout use: <?= $content ?>
     */
    protected function viewWithLayout(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewPath = dirname(__DIR__) . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View '{$view}' não encontrada.");
        }

        extract($data, EXTR_SKIP);

        $csrf  = Csrf::field();
        $token = Csrf::token();

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $layoutPath = dirname(__DIR__) . '/views/layouts/' . $layout . '.php';

        if (!file_exists($layoutPath)) {
            throw new \RuntimeException("Layout '{$layout}' não encontrado.");
        }

        require $layoutPath;
    }

    // ----------------------------------------------------------------
    // Redirect
    // ----------------------------------------------------------------

    /**
     * Redireciona para uma URI.
     *
     * Uso:
     *   return $this->redirect('/produtos');
     *   return $this->redirect('/produtos')->with('sucesso', 'Produto salvo!');
     */
    protected function redirect(string $uri): static
    {
        $this->_redirectUri = $uri;
        return $this;
    }

    private ?string $_redirectUri = null;

    /**
     * Adiciona flash message e executa o redirect.
     */
    public function with(string $key, mixed $value): never
    {
        Session::flash($key, $value);
        $this->doRedirect();
    }

    public function __destruct()
    {
        if ($this->_redirectUri !== null) {
            $this->doRedirect();
        }
    }

    private function doRedirect(): never
    {
        header('Location: ' . $this->_redirectUri);
        exit;
    }

    // ----------------------------------------------------------------
    // JSON (APIs e requisições AJAX)
    // ----------------------------------------------------------------

    /**
     * Responde com JSON.
     *
     * Uso:
     *   return $this->json(['ok' => true, 'data' => $produto]);
     *   return $this->json(['erro' => 'Não encontrado'], 404);
     */
    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // ----------------------------------------------------------------
    // Validação
    // ----------------------------------------------------------------

    /**
     * Valida campos obrigatórios e retorna array de erros.
     *
     * Uso:
     *   $erros = $this->validar($request->all(), [
     *       'nome'  => 'required|min:3|max:150',
     *       'email' => 'required|email',
     *       'preco' => 'required|numeric',
     *   ]);
     */
    protected function validar(array $dados, array $regras): array
    {
        $erros = [];

        foreach ($regras as $campo => $regrasStr) {
            $valor      = $dados[$campo] ?? null;
            $listaRegras = explode('|', $regrasStr);

            foreach ($listaRegras as $regra) {
                [$nome, $param] = array_pad(explode(':', $regra, 2), 2, null);

                $erro = match ($nome) {
                    'required' => (empty($valor) && $valor !== '0')
                        ? "O campo '{$campo}' é obrigatório."
                        : null,

                    'min' => (strlen((string)$valor) < (int)$param)
                        ? "O campo '{$campo}' deve ter no mínimo {$param} caracteres."
                        : null,

                    'max' => (strlen((string)$valor) > (int)$param)
                        ? "O campo '{$campo}' deve ter no máximo {$param} caracteres."
                        : null,

                    'email' => (!filter_var($valor, FILTER_VALIDATE_EMAIL))
                        ? "O campo '{$campo}' deve ser um e-mail válido."
                        : null,

                    'numeric' => (!is_numeric($valor))
                        ? "O campo '{$campo}' deve ser numérico."
                        : null,

                    'min_value' => ((float)$valor < (float)$param)
                        ? "O campo '{$campo}' deve ser maior ou igual a {$param}."
                        : null,

                    default => null,
                };

                if ($erro) {
                    $erros[$campo][] = $erro;
                    break; // Para no primeiro erro do campo
                }
            }
        }

        return $erros;
    }

    /**
     * Verifica CSRF e redireciona/aborta se inválido.
     */
    protected function verificarCsrf(Request $request): void
    {
        if (!Csrf::validate($request->csrfToken())) {
            http_response_code(419);
            Session::flash('erro', 'Token de segurança inválido. Tente novamente.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/')->with('erro', 'Token inválido.');
        }
    }

    // ----------------------------------------------------------------
    // Abort
    // ----------------------------------------------------------------

    protected function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        echo $message ?: "Erro {$code}";
        exit;
    }

    protected function notFound(): never
    {
        $this->abort(404, 'Página não encontrada.');
    }
}
