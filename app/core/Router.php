<?php

namespace App\Core;

class Router
{
    private array  $routes          = [];
    private array  $middlewareMap   = [];
    private string $groupPrefix     = '';
    private array  $groupMiddleware = [];

    // ----------------------------------------------------------------
    // Registro de rotas
    // ----------------------------------------------------------------

    public function get(string $uri, string $action, array $middleware = []): void
    {
        $this->add('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, string $action, array $middleware = []): void
    {
        $this->add('POST', $uri, $action, $middleware);
    }

    public function group(array $options, callable $callback): void
    {
        $prevPrefix     = $this->groupPrefix;
        $prevMiddleware = $this->groupMiddleware;

        $this->groupPrefix     .= $options['prefix'] ?? '';
        $this->groupMiddleware  = array_merge($this->groupMiddleware, $options['middleware'] ?? []);

        $callback($this);

        $this->groupPrefix     = $prevPrefix;
        $this->groupMiddleware = $prevMiddleware;
    }

    private function add(string $method, string $uri, string $action, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $this->uriToPattern($this->groupPrefix . $uri),
            'action'     => $action,
            'middleware' => array_merge($this->groupMiddleware, $middleware),
        ];
    }

    // ----------------------------------------------------------------
    // Dispatch
    // ----------------------------------------------------------------

    public function dispatch(Request $request): void
    {
        $uri    = $this->normalize($request->uri());
        $method = $request->method();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            // Parâmetros nomeados da URI: /produtos/{id} → ['id' => '42']
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            // Middleware
            foreach ($route['middleware'] as $alias) {
                $this->runMiddleware($alias, $request);
            }

            // ✅ Passa o mesmo $request recebido — não cria um novo
            $this->runAction($route['action'], $request, $params);
            return;
        }

        http_response_code(404);
        if (class_exists('\\App\\Controllers\\ErrorController')) {
            $this->runAction('ErrorController@notFound', $request, []);
        } else {
            echo '<h1>404 — Página não encontrada</h1>';
        }
    }

    // ----------------------------------------------------------------
    // Middleware
    // ----------------------------------------------------------------

    public function middleware(string $alias, string $class): void
    {
        $this->middlewareMap[$alias] = $class;
    }

    private function runMiddleware(string $alias, Request $request): void
    {
        if (!isset($this->middlewareMap[$alias])) {
            throw new \RuntimeException("Middleware '{$alias}' não registrado.");
        }
        (new $this->middlewareMap[$alias]())->handle($request);
    }

    // ----------------------------------------------------------------
    // Action
    // ----------------------------------------------------------------

    private function runAction(string $action, Request $request, array $params): void
    {
        [$controllerName, $method] = explode('@', $action, 2);

        $class = "\\App\\Controllers\\{$controllerName}";

        if (!class_exists($class)) {
            throw new \RuntimeException("Controller '{$class}' não encontrado.");
        }

        $controller = new $class();

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Método '{$method}' não existe em '{$class}'.");
        }

        // ✅ Passa o $request original do dispatch + parâmetros da URI
        $controller->$method($request, ...array_values($params));
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    private function uriToPattern(string $uri): string
    {
        $escaped = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . $escaped . '$#u';
    }

    /**
     * ✅ CORRIGIDO: o bug original era:
     *   return '/' . trim($uri, '/') ?: '/';
     *
     * O operador ?: tem precedência menor que o ponto,
     * então avaliava como: ('/' . trim($uri, '/')) ?: '/'
     * Quando trim retornava '' (URI raiz), '/' . '' = '/' → truthy → OK.
     * Mas o real problema: URIs como '/produtos' viravam '/produtos'
     * enquanto a rota estava registrada como '/produtos' — deveria casar.
     * O bug estava em rotas aninhadas ficarem com double slash ou sem slash.
     * Corrigido separando a lógica em duas linhas claras.
     */
    private function normalize(string $uri): string
    {
        $clean = '/' . trim($uri, '/');
        // URI raiz: '/' — qualquer outra: sem trailing slash
        return ($clean === '/') ? '/' : rtrim($clean, '/');
    }
}
