<?php

/**
 * ZAFENATE CONTROL — Definição de Rotas
 *
 * Sintaxe:
 * $router->get('/uri', 'Controller@método');
 * $router->post('/uri', 'Controller@método');
 *
 * Parâmetros de URI:
 * $router->get('/produtos/{id}', 'ProdutoController@show');
 *
 * Grupos com middleware:
 * $router->group(['prefix' => '/admin', 'middleware' => ['auth']], function ($r) { ... });
 */

use App\Core\Router;

// ----------------------------------------------------------------
// Rotas públicas (guest - apenas visitantes deslogados)
// ----------------------------------------------------------------
$router->group(['middleware' => ['guest']], function (Router $r) {
    $r->get('/login',    'AuthController@showLogin');
    $r->post('/login',   'AuthController@login');
});

$router->get('/logout', 'AuthController@logout');

// ----------------------------------------------------------------
// Rotas protegidas (requerem autenticação E proteção CSRF)
// ----------------------------------------------------------------
//$router->group(['middleware' => ['auth', 'csrf']], function (Router $r) {
$router->group(['middleware' => ['auth']], function (Router $r) {
    // Dashboard
    $r->get('/',          'DashboardController@index');
    $r->get('/dashboard', 'DashboardController@index');

    // ---- Produtos ----
    $r->get('/produtos',               'ProdutoController@index');
    $r->get('/produtos/criar',         'ProdutoController@create');
    $r->post('/produtos/criar',        'ProdutoController@store');
    $r->get('/produtos/{id}',          'ProdutoController@show');
    $r->get('/produtos/{id}/editar',   'ProdutoController@edit');
    $r->post('/produtos/{id}/editar',  'ProdutoController@update');
    $r->post('/produtos/{id}/status',  'ProdutoController@toggleStatus');
    $r->post('/produtos/rapido', 'ProdutoController@storeRapido');

    // ---- Fornecedores ----
    $r->get('/fornecedores',               'FornecedorController@index');
    $r->get('/fornecedores/criar',         'FornecedorController@create');
    $r->post('/fornecedores/criar',        'FornecedorController@store');
    $r->get('/fornecedores/{id}',          'FornecedorController@show');
    $r->get('/fornecedores/{id}/editar',   'FornecedorController@edit');
    $r->post('/fornecedores/{id}/editar',  'FornecedorController@update');
    $r->post('/fornecedores/{id}/status',  'FornecedorController@toggleStatus');

    // ---- Clientes ----
    $r->get('/clientes',               'ClienteController@index');
    $r->get('/clientes/criar',         'ClienteController@create');
    $r->post('/clientes/criar',        'ClienteController@store');
    $r->get('/clientes/{id}',          'ClienteController@show');
    $r->get('/clientes/{id}/editar',   'ClienteController@edit');
    $r->post('/clientes/{id}/editar',  'ClienteController@update');
    $r->post('/clientes/{id}/status',  'ClienteController@toggleStatus');

    // ── Compras ─────────────────────────────────────────────────
    $r->get('/compras',                 'CompraController@index');
    $r->get('/compras/criar',           'CompraController@create');
    $r->post('/compras/criar',          'CompraController@store');
    $r->get('/compras/buscar-produto',  'CompraController@buscarProduto'); // AJAX
    $r->get('/compras/{id}',            'CompraController@show');
    $r->get('/compras/{id}/editar',     'CompraController@edit');
    $r->post('/compras/{id}/editar',    'CompraController@update');
    $r->post('/compras/{id}/confirmar', 'CompraController@confirmar');
    $r->post('/compras/{id}/cancelar',  'CompraController@cancelar');

    // ---- Vendas ----
    $r->get('/vendas',                  'VendaController@index');
    $r->get('/vendas/relatorio',        'VendaController@relatorio');
    $r->get('/vendas/{id}',             'VendaController@show');
    $r->get('/vendas/{id}/cupom',       'VendaController@cupom');
    $r->post('/vendas/{id}/cancelar',   'VendaController@cancelar');

    // ---- Estoque ----
    $r->get('/estoque',               'EstoqueController@index');
    $r->get('/estoque/movimentar',    'EstoqueController@create');
    $r->post('/estoque/movimentar',   'EstoqueController@store');
    $r->get('/estoque/{id}/historico', 'EstoqueController@historico');

    // ---- Caixa ----
    $r->get('/caixa',              'CaixaController@index');
    $r->get('/caixa/gestao',       'CaixaController@gestao');
    $r->get('/caixa/sangria',      'CaixaController@sangria');
    $r->post('/caixa/sangria',     'CaixaController@salvarSangria');
    $r->post('/caixa/abrir',       'CaixaController@abrir');
    $r->post('/caixa/fechar',      'CaixaController@fechar');
    $r->get('/caixa/{id}',         'CaixaController@index');

    // ---- Relatórios ----
    $r->get('/relatorios/estoque',     'RelatorioController@estoque');
    $r->get('/relatorios/movimentos',  'RelatorioController@movimentos');
    $r->get('/relatorios/caixa',       'RelatorioController@caixa');

    // ---- PDV (Frente de Caixa) ----
    $r->get('/pdv',            'PdvController@index');
    $r->get('/pdv/buscar',     'PdvController@buscar');
    $r->post('/pdv/finalizar', 'PdvController@finalizar');
    $r->get('/pdv/buscar-rapido', 'PdvController@buscarRapidoEndpoint');

    // ---- Estoque ----
    $r->get('/estoque',                'EstoqueController@index');
    $r->get('/estoque/movimentar',     'EstoqueController@create');
    $r->post('/estoque/movimentar',    'EstoqueController@store');
    $r->get('/estoque/{id}/historico', 'EstoqueController@historico');
});
