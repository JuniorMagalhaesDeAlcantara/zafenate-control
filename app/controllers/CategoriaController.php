<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    private Categoria $model;

    public function __construct()
    {
        if (!Session::get('usuario_id')) {
            redirect('/login');
        }
        $this->model = new Categoria();
    }

    // GET /categorias
    public function index(Request $request): void
    {
        $filtros = [
            'busca' => $request->input('busca', ''),
            'ativo' => $request->input('ativo', ''),
        ];

        $this->view('categorias/index', [
            'categorias' => $this->model->listar($filtros),
            'totais'     => $this->model->totais(),
            'filtros'    => $filtros,
            'pageTitle'  => 'Categorias de Produtos',
            'breadcrumb' => [
                ['label' => 'Dashboard',  'url' => '/dashboard'],
                ['label' => 'Categorias', 'url' => '#'],
            ],
        ]);
    }

    // GET /categorias/criar
    public function create(): void
    {
        $this->view('categorias/form', [
            'raiz'       => $this->model->listarRaiz(),
            'pageTitle'  => 'Nova Categoria',
            'breadcrumb' => [
                ['label' => 'Dashboard',  'url' => '/dashboard'],
                ['label' => 'Categorias', 'url' => '/categorias'],
                ['label' => 'Nova',       'url' => '#'],
            ],
        ]);
    }

    // POST /categorias/criar
    public function store(Request $request): void
    {
        try {
            $dados = [
                'nome'      => trim($request->input('nome', '')),
                'descricao' => trim($request->input('descricao', '')),
                'parent_id' => $request->input('parent_id') ?: null,
                'ativo'     => 1,
            ];

            $this->model->criar($dados);
            Session::flash('success', 'Categoria criada com sucesso.');
            $this->redirect('/categorias');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/categorias/criar');
        }
    }

    // GET /categorias/{id}/editar
    public function edit(Request $request, int $id): void
    {
        $categoria = $this->model->buscarPorId($id);
        if (!$categoria) {
            Session::flash('error', 'Categoria não encontrada.');
            $this->redirect('/categorias');
        }

        $this->view('categorias/form', [
            'categoria'  => $categoria,
            'raiz'       => $this->model->listarRaiz(),
            'pageTitle'  => 'Editar Categoria',
            'breadcrumb' => [
                ['label' => 'Dashboard',  'url' => '/dashboard'],
                ['label' => 'Categorias', 'url' => '/categorias'],
                ['label' => 'Editar',     'url' => '#'],
            ],
        ]);
    }

    // POST /categorias/{id}/editar
    public function update(Request $request, int $id): void
    {
        try {
            $dados = [
                'nome'      => trim($request->input('nome', '')),
                'descricao' => trim($request->input('descricao', '')),
                'parent_id' => $request->input('parent_id') ?: null,
            ];

            $this->model->atualizar($id, $dados);
            Session::flash('success', 'Categoria atualizada com sucesso.');
            $this->redirect('/categorias');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect("/categorias/{$id}/editar");
        }
    }

    // POST /categorias/{id}/status
    public function toggleStatus(Request $request, int $id): void
    {
        try {
            $this->model->alternarStatus($id);
            Session::flash('success', 'Status da categoria alterado.');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/categorias');
    }

    // POST /categorias/ajax — criação rápida via modal no form de produto
    public function storeAjax(Request $request): void
    {
        header('Content-Type: application/json');
        try {
            $dados = [
                'nome'      => trim($request->input('nome', '')),
                'descricao' => trim($request->input('descricao', '')),
                'parent_id' => $request->input('parent_id') ?: null,
                'ativo'     => 1,
            ];

            if (empty($dados['nome'])) {
                throw new \InvalidArgumentException('Nome é obrigatório.');
            }

            $id  = $this->model->criar($dados);
            $cat = $this->model->buscarPorId($id);

            echo json_encode(['success' => true, 'categoria' => ['id' => $cat['id'], 'nome' => $cat['nome']]]);
        } catch (\Exception $e) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
