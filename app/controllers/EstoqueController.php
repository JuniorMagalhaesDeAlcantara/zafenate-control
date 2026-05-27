<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Models\MovimentacaoEstoque;

class EstoqueController extends Controller
{
    private Database $db;
    private MovimentacaoEstoque $model;
    private int $usuarioId;

    public function __construct()
    {
        if (!Session::get('usuario_id')) {
            $this->redirect('/login');
            exit;
        }
        $this->db        = Database::getInstance();
        $this->model     = new MovimentacaoEstoque();
        $this->usuarioId = (int) Session::get('usuario_id');
    }

    // ----------------------------------------------------------------
    // GET /estoque
    // Listagem com filtros
    // ----------------------------------------------------------------
    public function index(Request $request): void
    {
        $filtros = [
            'q'          => trim($request->input('q', '')),
            'tipo'       => $request->input('tipo', ''),
            'motivo'     => $request->input('motivo', ''),
            'usuario_id' => (int) $request->input('usuario_id', 0) ?: null,
            'de'         => $request->input('de', ''),
            'ate'        => $request->input('ate', ''),
        ];

        $pagina   = max(1, (int) $request->input('pagina', 1));
        $resultado = $this->model->listar(array_filter($filtros), $pagina);

        // Para os selects dos filtros
        $usuarios = $this->db->fetchAll(
            "SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome"
        );

        $this->view('estoque/index', [
            'title'      => 'Movimentações de Estoque',
            'pageTitle'  => 'Movimentações de Estoque',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => '/dashboard'],
                ['label' => 'Estoque'],
            ],
            'resultado'  => $resultado,
            'filtros'    => $filtros,
            'usuarios'   => $usuarios,
            'tipoLabels'  => MovimentacaoEstoque::TIPO_LABELS,
            'motivoLabels' => MovimentacaoEstoque::MOTIVO_LABELS,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /estoque/movimentar
    // Formulário de nova movimentação manual
    // ----------------------------------------------------------------
    public function create(Request $request): void
    {
        $produtos = $this->db->fetchAll(
            "SELECT p.id, p.nome, p.codigo, p.estoque_atual, un.sigla AS unidade_sigla
             FROM produtos p
             LEFT JOIN unidades un ON un.id = p.unidade_id
             WHERE p.ativo = 1
             ORDER BY p.nome ASC"
        );

        $fornecedores = $this->db->fetchAll(
            "SELECT id, razao_social FROM fornecedores WHERE ativo = 1 ORDER BY razao_social"
        );

        $this->view('estoque/movimentar', [
            'title'        => 'Nova Movimentação',
            'pageTitle'    => 'Nova Movimentação de Estoque',
            'breadcrumb'   => [
                ['label' => 'Dashboard', 'url' => '/dashboard'],
                ['label' => 'Estoque', 'url' => '/estoque'],
                ['label' => 'Nova Movimentação'],
            ],
            'produtos'     => $produtos,
            'fornecedores' => $fornecedores,
            'motivoTipo'   => MovimentacaoEstoque::MOTIVO_TIPO,
            'motivoLabels' => MovimentacaoEstoque::MOTIVO_LABELS,
        ]);
    }

    // ----------------------------------------------------------------
    // POST /estoque/movimentar
    // ----------------------------------------------------------------
    public function store(Request $request): void
    {
        try {
            $motivo = $request->input('motivo', '');

            $tipo = MovimentacaoEstoque::MOTIVO_TIPO[$motivo] ?? '';

            if (!$tipo) {
                throw new \InvalidArgumentException("Motivo inválido selecionado.");
            }

            $qtdRaw     = str_replace(',', '.', $request->input('quantidade', '0'));
            $quantidade = (float) $qtdRaw;

            $custoRaw = $request->input('preco_custo_unitario', '');
            $custo    = $custoRaw !== ''
                ? (float) str_replace(['.', ','], ['', '.'], $custoRaw)
                : null;

            $this->model->registrar([
                'produto_id'           => (int) $request->input('produto_id'),
                'tipo'                 => $tipo,
                'motivo'               => $motivo,
                'quantidade'           => $quantidade,
                'fornecedor_id'        => (int) $request->input('fornecedor_id', 0) ?: null,
                'preco_custo_unitario' => $custo,
                'numero_nf'            => trim($request->input('numero_nf', '')) ?: null,
                'observacao'           => trim($request->input('observacao', '')) ?: null,
                'usuario_id'           => $this->usuarioId,
                'origem'               => 'MANUAL',
            ]);

            Session::flash('success', 'Movimentação registrada com sucesso!');
            $this->redirect('/estoque');
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/estoque/movimentar');
        } catch (\Throwable $e) {
            error_log('[Estoque] Erro: ' . $e->getMessage());
            Session::flash('error', 'Erro interno ao registrar movimentação. Tente novamente.');
            $this->redirect('/estoque/movimentar');
        }
    }

    // ----------------------------------------------------------------
    // GET /estoque/{id}/historico
    // ----------------------------------------------------------------
    public function historico(Request $request, int $id): void
    {
        $produto = $this->db->fetchOne(
            "SELECT p.*, un.sigla AS unidade_sigla
             FROM produtos p
             LEFT JOIN unidades un ON un.id = p.unidade_id
             WHERE p.id = :id",
            ['id' => $id]
        );

        if (!$produto) {
            Session::flash('error', 'Produto não encontrado.');
            $this->redirect('/estoque');
            return;
        }

        $historico = $this->model->historicoPorProduto($id, 100);

        $this->view('estoque/historico', [
            'title'        => 'Histórico — ' . $produto['nome'],
            'pageTitle'    => 'Histórico de Estoque',
            'breadcrumb'   => [
                ['label' => 'Dashboard',  'url' => '/dashboard'],
                ['label' => 'Estoque',    'url' => '/estoque'],
                ['label' => $produto['nome']],
            ],
            'produto'      => $produto,
            'historico'    => $historico,
            'tipoLabels'   => MovimentacaoEstoque::TIPO_LABELS,
            'motivoLabels' => MovimentacaoEstoque::MOTIVO_LABELS,
        ]);
    }
}
