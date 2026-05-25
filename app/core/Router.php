<?php

namespace App\Core;

class Router
{
    private array $routes     = [];
    private array $middleware = [];
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

    /**
     * Grupo de rotas com prefixo e/ou middleware compartilhado.
     *
     * Exemplo:
     *   $router->group(['prefix' => '/admin', 'middleware' => ['auth']], function ($r) {
     *       $r->get('/dashboard', 'DashboardController@index');
     *   });
     */
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
        $fullUri        = $this->groupPrefix . $uri;
        $allMiddleware  = array_merge($this->groupMiddleware, $middleware);

        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $this->uriToPattern($fullUri),
            'action'     => $action,
            'middleware' => $allMiddleware,
        ];
    }

    // ----------------------------------------------------------------
    // Dispatch
    // ----------------------------------------------------------------

    /**
     * Resolve a requisição atual e executa o controller.
     * Lança NotFoundException se nenhuma rota casar.
     */
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

            // Parâmetros da URI (ex: /produtos/{id} → ['id' => '42'])
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            // Executa middleware em cadeia
            foreach ($route['middleware'] as $alias) {
                $this->runMiddleware($alias, $request);
            }

            // Executa o controller
            $this->runAction($route['action'], $params);
            return;
        }

        // Nenhuma rota casou
        http_response_code(404);
        $this->runAction('ErrorController@notFound', []);
    }

    // ----------------------------------------------------------------
    // Middleware
    // ----------------------------------------------------------------

    /**
     * Registra um alias de middleware.
     * $router->middleware('auth', \App\Middleware\AuthMiddleware::class);
     */
    public function middleware(string $alias, string $class): void
    {
        $this->middleware[$alias] = $class;
    }

    private function runMiddleware(string $alias, Request $request): void
    {
        if (!isset($this->middleware[$alias])) {
            throw new \RuntimeException("Middleware '{$alias}' não registrado.");
        }

        $class = $this->middleware[$alias];
        (new $class())->handle($request);
    }

    // ----------------------------------------------------------------
    // Action
    // ----------------------------------------------------------------

    private function runAction(string $action, array $params): void
    {
        [$controllerName, $method] = explode('@', $action);

        $class = "\\App\\Controllers\\{$controllerName}";

        if (!class_exists($class)) {
            throw new \RuntimeException("Controller '{$class}' não encontrado.");
        }

        $controller = new $class();

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Método '{$method}' não existe em '{$class}'.");
        }

        // Passa os parâmetros da URI como argumentos para o método do controller
        $requestInstance = new \App\Core\Request();
        $controller->$method($requestInstance, ...array_values($params));
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    /**
     * Converte URI com parâmetros em regex nomeada.
     * /produtos/{id}  →  #^/produtos/(?P<id>[^/]+)$#
     */
    private function uriToPattern(string $uri): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    private function normalize(string $uri): string
    {
        return '/' . trim($uri, '/') ?: '/';
    }
}