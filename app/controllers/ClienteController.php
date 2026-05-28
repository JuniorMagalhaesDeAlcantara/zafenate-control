<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Cliente;

class ClienteController extends Controller
{
    private Cliente $model;

    public function __construct()
    {
        $this->model = new Cliente();
    }

    // ----------------------------------------------------------------
    // GET /clientes
    // ----------------------------------------------------------------
    public function index(Request $request): void
    {
        $filtros = [
            'q'           => $request->query('q', ''),
            'tipo_pessoa' => $request->query('tipo_pessoa', ''),
            'ativo'       => $request->query('ativo', ''),
            'pagina'      => $request->query('pagina', 1),
        ];

        $resultado = $this->model->listar($filtros);

        $this->view('clientes/index', compact('filtros', 'resultado'));
    }

    // ----------------------------------------------------------------
    // GET /clientes/criar
    // ----------------------------------------------------------------
    public function create(Request $request): void
    {
        $this->view('clientes/create', [
            'cliente' => [],
            'erros'   => [],
        ]);
    }

    // ----------------------------------------------------------------
    // POST /clientes/criar
    // ----------------------------------------------------------------
    public function store(Request $request): void
    {
        $dados = $request->all();
        $erros = $this->validar($dados, [
            'nome' => 'required|max:150',
            'email' => !empty($dados['email']) ? 'email' : '',
        ]);

        if ($erros) {
            $this->view('clientes/create', [
                'cliente' => $dados,
                'erros'   => $erros,
            ]);
            return;
        }

        try {
            $id = $this->model->criar($dados);
            Session::flash('success', 'Cliente cadastrado com sucesso!');
            $this->redirect("/clientes/{$id}");
        } catch (\InvalidArgumentException $e) {
            $this->view('clientes/create', [
                'cliente' => $dados,
                'erros'   => ['geral' => [$e->getMessage()]],
            ]);
        }
    }

    // ----------------------------------------------------------------
    // GET /clientes/{id}
    // ----------------------------------------------------------------
    public function show(Request $request, string $id): void
    {
        $cliente = $this->model->buscarPorId((int) $id);

        if (!$cliente) {
            $this->notFound();
        }

        $vendas = $this->model->ultimasVendas((int) $id);

        $this->view('clientes/show', compact('cliente', 'vendas'));
    }

    // ----------------------------------------------------------------
    // GET /clientes/{id}/editar
    // ----------------------------------------------------------------
    public function edit(Request $request, string $id): void
    {
        $cliente = $this->model->buscarPorId((int) $id);

        if (!$cliente || $cliente['id'] == 1) {
            $this->notFound();
        }

        $this->view('clientes/edit', [
            'cliente' => $cliente,
            'erros'   => [],
        ]);
    }

    // ----------------------------------------------------------------
    // POST /clientes/{id}/editar
    // ----------------------------------------------------------------
    public function update(Request $request, string $id): void
    {
        $cliente = $this->model->buscarPorId((int) $id);
        if (!$cliente || $cliente['id'] == 1) {
            $this->notFound();
        }

        $dados = $request->all();
        $erros = $this->validar($dados, [
            'nome' => 'required|max:150',
        ]);

        if ($erros) {
            $this->view('clientes/edit', [
                'cliente' => array_merge($cliente, $dados),
                'erros'   => $erros,
            ]);
            return;
        }

        try {
            $this->model->atualizar((int) $id, $dados);
            Session::flash('success', 'Cliente atualizado com sucesso!');
            $this->redirect("/clientes/{$id}");
        } catch (\InvalidArgumentException $e) {
            $this->view('clientes/edit', [
                'cliente' => array_merge($cliente, $dados),
                'erros'   => ['geral' => [$e->getMessage()]],
            ]);
        }
    }

    // ----------------------------------------------------------------
    // POST /clientes/{id}/status
    // ----------------------------------------------------------------
    public function toggleStatus(Request $request, string $id): void
    {
        $this->model->alternarStatus((int) $id);
        Session::flash('success', 'Status do cliente atualizado.');
        $this->redirect('/clientes');
    }

    // ----------------------------------------------------------------
    // GET /clientes/buscar?q=termo  (AJAX)
    // ----------------------------------------------------------------
    public function buscar(Request $request): void
    {
        $termo = $request->query('q', '');

        if (strlen($termo) < 2) {
            $this->json([]);
            return;
        }

        $this->json($this->model->buscarRapido($termo));
    }
}
