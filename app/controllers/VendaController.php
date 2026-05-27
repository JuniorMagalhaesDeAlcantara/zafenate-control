<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\VendaModulo;

class VendaController extends Controller
{
    private VendaModulo $model;
    private int $usuarioId;
    private string $nivelUsuario;

    public function __construct()
    {
        if (!Session::get('usuario_id')) {
            redirect('/login');
        }
        $this->model        = new VendaModulo();
        $this->usuarioId    = (int) Session::get('usuario_id');
        $this->nivelUsuario = Session::get('usuario_nivel') ?? 'operador';
    }

    // ----------------------------------------------------------------
    // GET /vendas — Listagem com filtros
    // ----------------------------------------------------------------
    public function index(Request $request): void
    {
        $filtros = [
            'numero'          => $request->input('numero', ''),
            'status'          => $request->input('status', ''),
            'data_de'         => $request->input('data_de', date('Y-m-01')),
            'data_ate'        => $request->input('data_ate', date('Y-m-d')),
            'usuario_id'      => $request->input('usuario_id', ''),
            'cliente_id'      => $request->input('cliente_id', ''),
            'forma_pagamento' => $request->input('forma_pagamento', ''),
        ];

        $pagina   = max(1, (int) $request->input('pagina', 1));
        $resultado = $this->model->listar($filtros, $pagina);
        $totais    = $this->model->totaisPorPeriodo($filtros['data_de'], $filtros['data_ate']);

        $this->view('vendas/index', [
            'title'      => 'Vendas',
            'resultado'  => $resultado,
            'totais'     => $totais,
            'filtros'    => $filtros,
            'operadores' => $this->model->listarOperadores(),
            'nivel'      => $this->nivelUsuario,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /vendas/{id} — Detalhe
    // ----------------------------------------------------------------
    public function show(Request $request, int $id): void
    {
        $venda = $this->model->buscarPorId($id);

        if (!$venda) {
            Session::flash('error', 'Venda não encontrada.');
            redirect('/vendas');
        }

        $this->view('vendas/show', [
            'title' => "Venda #{$venda['numero']}",
            'venda' => $venda,
            'nivel' => $this->nivelUsuario,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /vendas/{id}/cupom — Impressão / reimpressão
    // ----------------------------------------------------------------
    public function cupom(Request $request, int $id): void
    {
        $venda = $this->model->buscarPorId($id);

        if (!$venda) {
            $this->abort(404, 'Venda não encontrada.');
        }

        // Configurações da empresa para o cabeçalho do cupom
        $config = $this->carregarConfig();

        $this->view('vendas/cupom', [
            'venda'  => $venda,
            'config' => $config,
        ]);
    }

    // ----------------------------------------------------------------
    // POST /vendas/{id}/cancelar
    // ----------------------------------------------------------------
    public function cancelar(Request $request, int $id): void
    {
        // Apenas gerente ou admin podem cancelar
        if (!in_array($this->nivelUsuario, ['admin', 'gerente'])) {
            Session::flash('error', 'Sem permissão para cancelar vendas.');
            redirect("/vendas/{$id}");
        }

        $motivo = trim($request->input('motivo_cancelamento', ''));

        if (strlen($motivo) < 5) {
            Session::flash('error', 'Informe o motivo do cancelamento (mínimo 5 caracteres).');
            redirect("/vendas/{$id}");
        }

        try {
            $this->model->cancelar($id, $this->usuarioId, $motivo);
            Session::flash('success', 'Venda cancelada. Estoque estornado com sucesso.');
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('[Vendas] Cancelamento: ' . $e->getMessage());
            Session::flash('error', 'Erro interno ao cancelar. Tente novamente.');
        }

        redirect("/vendas/{$id}");
    }

    // ----------------------------------------------------------------
    // GET /vendas/relatorio — Relatório por período
    // ----------------------------------------------------------------
    public function relatorio(Request $request): void
    {
        $de  = $request->input('data_de',  date('Y-m-01'));
        $ate = $request->input('data_ate', date('Y-m-d'));

        $this->view('vendas/relatorio', [
            'title'           => 'Relatório de Vendas',
            'data_de'         => $de,
            'data_ate'        => $ate,
            'totais'          => $this->model->totaisPorPeriodo($de, $ate),
            'por_dia'         => $this->model->faturamentoPorDia($de, $ate),
            'top_produtos'    => $this->model->topProdutos($de, $ate),
            'por_forma'       => $this->model->vendasPorForma($de, $ate),
        ]);
    }

    // ----------------------------------------------------------------
    // Helper
    // ----------------------------------------------------------------
    private function carregarConfig(): array
    {
        try {
            $db   = \App\Core\Database::getInstance();
            $rows = $db->fetchAll("SELECT chave, valor FROM configuracoes");
            return array_column($rows, 'valor', 'chave');
        } catch (\Throwable) {
            return [];
        }
    }
}
