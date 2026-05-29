<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Fornecedor;

class FornecedorController extends Controller
{
    private Fornecedor $model;

    public function __construct()
    {
        if (!Session::get('usuario_id')) {
            redirect('/login');
        }
        $this->model = new Fornecedor();
    }

    // ----------------------------------------------------------------
    // GET /fornecedores
    // ----------------------------------------------------------------
    public function index(Request $request): void
    {
        $filtros = [
            'busca'       => $request->query('busca', ''),
            'ativo'       => $request->query('ativo', ''),
            'tipo_pessoa' => $request->query('tipo_pessoa', ''),
        ];

        $this->view('fornecedores/index', [
            'title'       => 'Fornecedores',
            'fornecedores' => $this->model->listar($filtros),
            'totais'      => $this->model->totais(),
            'filtros'     => $filtros,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /fornecedores/criar
    // ----------------------------------------------------------------
    public function create(): void
    {
        $this->view('fornecedores/form', [
            'title'      => 'Novo Fornecedor',
            'fornecedor' => null,
            'modo'       => 'criar',
        ]);
    }

    // ----------------------------------------------------------------
    // POST /fornecedores/criar
    // ----------------------------------------------------------------
    public function store(Request $request): void
    {
        try {
            $dados         = $this->normalizarDados($request->all());
            $dados['ativo'] = 1;

            $id = $this->model->criar($dados);

            Session::flash('success', 'Fornecedor cadastrado com sucesso!');
            redirect('/fornecedores/' . $id);
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            redirect('/fornecedores/criar');
        } catch (\Exception $e) {
            Session::flash('error', APP_DEBUG ? $e->getMessage() : 'Erro ao salvar fornecedor.');
            redirect('/fornecedores/criar');
        }
    }

    // ----------------------------------------------------------------
    // GET /fornecedores/{id}
    // ----------------------------------------------------------------
    public function show(Request $request, int $id): void
    {
        $fornecedor = $this->model->buscarPorId($id);

        if (!$fornecedor) {
            Session::flash('error', 'Fornecedor não encontrado.');
            redirect('/fornecedores');
        }

        $this->view('fornecedores/show', [
            'title'      => $fornecedor['razao_social'],
            'fornecedor' => $fornecedor,
            'historico'  => $this->model->historicoCompras($id),
        ]);
    }

    // ----------------------------------------------------------------
    // GET /fornecedores/{id}/editar
    // ----------------------------------------------------------------
    public function edit(Request $request, int $id): void
    {
        $fornecedor = $this->model->buscarPorId($id);

        if (!$fornecedor) {
            Session::flash('error', 'Fornecedor não encontrado.');
            redirect('/fornecedores');
        }

        $this->view('fornecedores/form', [
            'title'      => 'Editar Fornecedor',
            'fornecedor' => $fornecedor,
            'modo'       => 'editar',
        ]);
    }

    // ----------------------------------------------------------------
    // POST /fornecedores/{id}/editar
    // ----------------------------------------------------------------
    public function update(Request $request, int $id): void
    {
        try {
            $dados = $this->normalizarDados($request->all());
            $this->model->atualizar($id, $dados);

            Session::flash('success', 'Fornecedor atualizado com sucesso!');
            redirect('/fornecedores/' . $id);
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            redirect("/fornecedores/{$id}/editar");
        } catch (\Exception $e) {
            Session::flash('error', APP_DEBUG ? $e->getMessage() : 'Erro ao atualizar fornecedor.');
            redirect("/fornecedores/{$id}/editar");
        }
    }

    // ----------------------------------------------------------------
    // POST /fornecedores/{id}/status
    // ----------------------------------------------------------------
    public function toggleStatus(Request $request, int $id): void
    {
        try {
            $this->model->alternarStatus($id);
            Session::flash('success', 'Status atualizado com sucesso.');
        } catch (\Exception $e) {
            Session::flash('error', 'Erro ao alterar status.');
        }
        redirect('/fornecedores');
    }

    // ----------------------------------------------------------------
    // Helper privado
    // ----------------------------------------------------------------
    private function normalizarDados(array $dados): array
    {
        // Remove pontuação de CNPJ/CPF para armazenar limpo
        if (!empty($dados['cnpj_cpf'])) {
            $dados['cnpj_cpf'] = preg_replace('/\D/', '', $dados['cnpj_cpf']);
        }

        $toFloat = fn($v) => $v !== '' && $v !== null
            ? (float) str_replace(['.', ','], ['', '.'], (string)$v)
            : null;

        $dados['limite_credito']  = $toFloat($dados['limite_credito']  ?? '') ?? 0.00;
        $dados['prazo_pagamento'] = !empty($dados['prazo_pagamento']) ? (int)$dados['prazo_pagamento'] : null;
        $dados['prazo_entrega']   = !empty($dados['prazo_entrega'])   ? (int)$dados['prazo_entrega']   : null;

        foreach (['avaliacao_prazo', 'avaliacao_qualidade', 'avaliacao_atendimento'] as $campo) {
            $dados[$campo] = !empty($dados[$campo]) ? (int)$dados[$campo] : null;
        }

        return $dados;
    }
}
