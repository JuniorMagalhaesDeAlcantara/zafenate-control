<?php

namespace App\Core;

class Request
{
    private array $get;
    private array $post;
    private array $files;
    private array $server;

    public function __construct()
    {
        $this->get    = $_GET    ?? [];
        $this->post   = $_POST   ?? [];
        $this->files  = $_FILES  ?? [];
        $this->server = $_SERVER ?? [];
    }

    // Método HTTP
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }
    public function isAjax(): bool
    {
        return ($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    // URI atual sem query string
    public function uri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $pos = strpos($uri, '?');
        return $pos !== false ? substr($uri, 0, $pos) : $uri;
    }

    // GET (query string) — lê exclusivamente $_GET
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->sanitize($this->get[$key] ?? $default);
    }

    /**
     * Lê POST primeiro, cai em GET se não encontrar.
     * Funciona para filtros via query string (?busca=x) e forms POST.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->sanitize($this->post[$key] ?? $this->get[$key] ?? $default);
    }

    /**
     * Alias semântico de input() — mesma lógica POST → GET.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->input($key, $default);
    }

    /**
     * Retorna todos os campos sanitizados (POST merged com GET, POST tem precedência).
     */
    public function all(): array
    {
        $merged = array_merge($this->get, $this->post); // POST sobrescreve GET
        return array_map([$this, 'sanitize'], $merged);
    }

    // Upload de arquivo
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    // Token CSRF do POST
    public function csrfToken(): string
    {
        // Aceita tanto '_csrf' (padrão do sistema) quanto '_csrf_token' (legado)
        return $this->post['_csrf'] ?? $this->post['_csrf_token'] ?? $this->server['HTTP_X_CSRF_TOKEN'] ?? '';
    }

    // Sanitização básica — strips tags e espaços
    private function sanitize(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }

        if (is_string($value)) {
            return trim(strip_tags($value));
        }

        return $value;
    }
}
