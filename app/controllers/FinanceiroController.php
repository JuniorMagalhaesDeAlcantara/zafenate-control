<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Financeiro as FinanceiroModel;

class FinanceiroController extends Controller
{
    private FinanceiroModel $model;

    public function __construct()
    {
        $this->model = new FinanceiroModel();
    }

    // ================================================================
    // CONTAS A PAGAR
    // ================================================================

    public function pagar(): void
    {
        $filtros = [
            'busca'        => $_GET['busca']        ?? '',
            'status'       => $_GET['status']       ?? '',
            'categoria_id' => $_GET['categoria_id'] ?? '',
            'de'           => $_GET['de']           ?? '',
            'ate'          => $_GET['ate']          ?? '',
        ];

        $contas     = $this->model->listarPagar($filtros);
        $totais     = $this->model->totaisPagar();
        $categorias = $this->model->listarCategorias('despesa');

        $fornecedores = \App\Core\Database::getInstance()->fetchAll(
            "SELECT id, razao_social FROM fornecedores WHERE ativo = 1 ORDER BY razao_social"
        );

        $this->view('financeiro/pagar', compact(
            'contas',
            'totais',
            'categorias',
            'fornecedores',
            'filtros',
        ) + [
            'pageTitle'  => 'Contas a Pagar',
            'breadcrumb' => [
                ['label' => 'Dashboard',       'url' => '/dashboard'],
                ['label' => 'Contas a Pagar',  'url' => '#'],
            ],
        ]);
    }

    public function pagarCreate(): void
    {
        $categorias   = $this->model->listarCategorias('despesa');
        $fornecedores = \App\Core\Database::getInstance()->fetchAll(
            "SELECT id, razao_social FROM fornecedores WHERE ativo = 1 ORDER BY razao_social"
        );

        $this->view('financeiro/pagar_form', compact('categorias', 'fornecedores') + [
            'pageTitle'  => 'Nova Conta a Pagar',
            'breadcrumb' => [
                ['label' => 'Dashboard',      'url' => '/dashboard'],
                ['label' => 'Contas a Pagar', 'url' => '/financeiro/pagar'],
                ['label' => 'Nova',           'url' => '#'],
            ],
        ]);
    }

    public function pagarStore(Request $request): void
    {
        try {
            $this->model->criarPagar([
                'categoria_id'   => $request->input('categoria_id') ?: null,
                'fornecedor_id'  => $request->input('fornecedor_id') ?: null,
                'usuario_id'     => (int)Session::get('usuario_id'),
                'descricao'      => $request->input('descricao'),
                'valor'          => (float)str_replace(',', '.', $request->input('valor')),
                'vencimento'     => $request->input('vencimento'),
                'documento'      => $request->input('documento'),
                'forma_pagamento' => $request->input('forma_pagamento') ?: null,
                'observacao'     => $request->input('observacao'),
            ]);

            Session::flash('success', 'Conta a pagar cadastrada com sucesso.');
            $this->redirect('/financeiro/pagar');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/financeiro/pagar/criar');
        }
    }

    public function pagarBaixar(Request $request, int $id): void
    {
        try {
            $this->model->baixarPagar(
                $id,
                (float)str_replace(',', '.', $request->input('valor_pago')),
                $request->input('forma_pagamento'),
                $request->input('data_pagamento') ?: date('Y-m-d')
            );
            Session::flash('success', 'Baixa registrada com sucesso.');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/financeiro/pagar');
    }

    public function pagarCancelar(Request $request, int $id): void
    {
        try {
            $this->model->cancelarPagar($id, $request->input('motivo', 'Cancelado pelo usuário.'));
            Session::flash('success', 'Conta cancelada.');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/financeiro/pagar');
    }

    // ================================================================
    // CONTAS A RECEBER
    // ================================================================

    public function receber(): void
    {
        $filtros = [
            'busca'  => $_GET['busca']  ?? '',
            'status' => $_GET['status'] ?? '',
            'de'     => $_GET['de']     ?? '',
            'ate'    => $_GET['ate']    ?? '',
        ];

        $contas     = $this->model->listarReceber($filtros);
        $totais     = $this->model->totaisReceber();
        $categorias = $this->model->listarCategorias('receita');

        $this->view('financeiro/receber', compact('contas', 'totais', 'categorias', 'filtros') + [
            'pageTitle'  => 'Contas a Receber',
            'breadcrumb' => [
                ['label' => 'Dashboard',          'url' => '/dashboard'],
                ['label' => 'Contas a Receber',   'url' => '#'],
            ],
        ]);
    }

    public function receberCreate(): void
    {
        $categorias = $this->model->listarCategorias('receita');
        $clientes   = \App\Core\Database::getInstance()->fetchAll(
            "SELECT id, nome FROM clientes WHERE ativo = 1 ORDER BY nome"
        );

        $this->view('financeiro/receber_form', compact('categorias', 'clientes') + [
            'pageTitle'  => 'Nova Conta a Receber',
            'breadcrumb' => [
                ['label' => 'Dashboard',        'url' => '/dashboard'],
                ['label' => 'Contas a Receber', 'url' => '/financeiro/receber'],
                ['label' => 'Nova',             'url' => '#'],
            ],
        ]);
    }

    public function receberStore(Request $request): void
    {
        try {
            $this->model->criarReceber([
                'categoria_id'     => $request->input('categoria_id') ?: null,
                'cliente_id'       => $request->input('cliente_id') ?: null,
                'usuario_id'       => (int)Session::get('usuario_id'),
                'descricao'        => $request->input('descricao'),
                'valor'            => (float)str_replace(',', '.', $request->input('valor')),
                'vencimento'       => $request->input('vencimento'),
                'documento'        => $request->input('documento'),
                'forma_recebimento' => $request->input('forma_recebimento') ?: null,
                'observacao'       => $request->input('observacao'),
            ]);

            Session::flash('success', 'Conta a receber cadastrada.');
            $this->redirect('/financeiro/receber');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/financeiro/receber/criar');
        }
    }

    public function receberBaixar(Request $request, int $id): void
    {
        try {
            $this->model->baixarReceber(
                $id,
                (float)str_replace(',', '.', $request->input('valor_recebido')),
                $request->input('forma_recebimento'),
                $request->input('data_recebimento') ?: date('Y-m-d')
            );
            Session::flash('success', 'Recebimento registrado com sucesso.');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/financeiro/receber');
    }

    public function receberCancelar(Request $request, int $id): void
    {
        try {
            $usuarioId = (int)Session::get('usuario_id');
            $this->model->cancelarReceber(
                $id,
                $request->input('motivo', 'Cancelado pelo usuário.'),
                $usuarioId  // ← estava faltando isso
            );
            Session::flash('success', 'Conta cancelada. Venda e estoque estornados se aplicável.');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirect('/financeiro/receber');
    }

    // ================================================================
    // FLUXO DE CAIXA
    // ================================================================

    public function fluxo(): void
    {
        $de  = $_GET['de']  ?? date('Y-m-01');
        $ate = $_GET['ate'] ?? date('Y-m-t');

        $dados = $this->model->fluxoCaixa($de, $ate);

        $this->view('financeiro/fluxo', $dados + [
            'de'         => $de,
            'ate'        => $ate,
            'pageTitle'  => 'Fluxo de Caixa',
            'breadcrumb' => [
                ['label' => 'Dashboard',     'url' => '/dashboard'],
                ['label' => 'Fluxo de Caixa', 'url' => '#'],
            ],
        ]);
    }
}
