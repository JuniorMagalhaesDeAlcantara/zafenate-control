<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Models\Compra as CompraModel;
use App\Models\Produto as ProdutoModel;

class CompraController extends Controller
{
    private Database $db;
    private CompraModel $compraModel;
    private ProdutoModel $produtoModel;

    public function __construct()
    {
        $this->db           = Database::getInstance();
        $this->compraModel  = new CompraModel();
        $this->produtoModel = new ProdutoModel();
    }

    // ----------------------------------------------------------------
    // LISTAGEM
    // ----------------------------------------------------------------

    public function index(Request $request): void
    {
        $filtros = [
            'busca'         => $request->query('busca', ''),
            'status'        => $request->query('status', ''),
            'fornecedor_id' => $request->query('fornecedor_id', ''),
            'de'            => $request->query('de', ''),
            'ate'           => $request->query('ate', ''),
        ];

        $compras      = $this->compraModel->listar($filtros);
        $totais       = $this->compraModel->totais();
        $fornecedores = $this->db->fetchAll(
            "SELECT id, razao_social FROM fornecedores WHERE ativo = 1 ORDER BY razao_social"
        );

        $pageTitle  = 'Compras';
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Compras',   'url' => '#'],
        ];

        $this->view('compras/index', compact(
            'compras',
            'totais',
            'fornecedores',
            'filtros',
            'pageTitle',
            'breadcrumb'
        ));
    }

    // ----------------------------------------------------------------
    // NOVA COMPRA — Formulário
    // ----------------------------------------------------------------

    public function create(Request $request): void
    {
        $fornecedores = $this->db->fetchAll(
            "SELECT id, razao_social, nome_fantasia, prazo_pagamento, forma_pagamento
             FROM fornecedores WHERE ativo = 1 ORDER BY razao_social"
        );

        $produtos = $this->db->fetchAll(
            "SELECT p.id, p.nome, p.codigo, p.preco_custo,
                    COALESCE(u.sigla, 'UN') AS unidade_sigla
             FROM produtos p
             LEFT JOIN unidades u ON u.id = p.unidade_id
             WHERE p.ativo = 1
             ORDER BY p.nome ASC"
        );

        $categorias = $this->produtoModel->listarCategoriasForm();
        $unidades   = $this->produtoModel->listarUnidadesForm();

        $pageTitle  = 'Nova Compra';
        $breadcrumb = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Compras',   'url' => '/compras'],
            ['label' => 'Nova',      'url' => '#'],
        ];

        $this->view('compras/create', compact(
            'fornecedores',
            'produtos',
            'categorias',
            'unidades',
            'pageTitle',
            'breadcrumb'
        ));
    }

    // ----------------------------------------------------------------
    // SALVAR RASCUNHO (POST /compras/criar)
    // ----------------------------------------------------------------

    public function store(Request $request): void
    {
        try {
            $dados = [
                'fornecedor_id'   => (int)$request->input('fornecedor_id'),
                'usuario_id'      => (int)Session::get('usuario_id'),
                'numero_nf'       => $request->input('numero_nf'),
                'serie_nf'        => $request->input('serie_nf'),
                'forma_pagamento' => $request->input('forma_pagamento'),
                'prazo_pagamento' => $request->input('prazo_pagamento') ?: null,
                'vencimento'      => $request->input('vencimento')      ?: null,
                'data_emissao'    => $request->input('data_emissao')    ?: date('Y-m-d'),
                'frete'           => (float)str_replace(',', '.', $request->input('frete', '0')),
                'desconto_valor'  => (float)str_replace(',', '.', $request->input('desconto_valor', '0')),
                'observacao'      => $request->input('observacao'),
            ];

            if (!$dados['fornecedor_id']) {
                throw new \InvalidArgumentException('Selecione um fornecedor.');
            }

            $itens = $_POST['itens'] ?? [];

            if (empty($itens)) {
                throw new \InvalidArgumentException('Adicione ao menos um produto antes de salvar.');
            }

            $compraId = $this->compraModel->criar($dados);
            $this->compraModel->salvarItens($compraId, $itens);

            Session::flash('success', 'Compra salva como rascunho. Revise e confirme para atualizar o estoque.');
            $this->redirect('/compras/' . $compraId);
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/compras/criar');
        }
    }

    // ----------------------------------------------------------------
    // DETALHE / VISUALIZAÇÃO
    // ----------------------------------------------------------------

    public function show(Request $request, string $id): void
    {
        $compra = $this->compraModel->buscarPorId((int)$id);

        if (!$compra) {
            Session::flash('error', 'Compra não encontrada.');
            $this->redirect('/compras');
            return;
        }

        $itens = $this->compraModel->buscarItens((int)$id);

        $pageTitle  = 'Compra ' . $compra['numero'];
        $breadcrumb = [
            ['label' => 'Dashboard',       'url' => '/dashboard'],
            ['label' => 'Compras',         'url' => '/compras'],
            ['label' => $compra['numero'], 'url' => '#'],
        ];

        $this->view('compras/show', compact('compra', 'itens', 'pageTitle', 'breadcrumb'));
    }

    // ----------------------------------------------------------------
    // EDITAR — Formulário (GET /compras/{id}/editar)
    // ----------------------------------------------------------------

    public function edit(Request $request, string $id): void
    {
        $compra = $this->compraModel->buscarPorId((int)$id);

        if (!$compra) {
            Session::flash('error', 'Compra não encontrada.');
            $this->redirect('/compras');
            return;
        }

        if ($compra['status'] !== 'rascunho') {
            Session::flash('error', 'Apenas compras em rascunho podem ser editadas.');
            $this->redirect('/compras/' . $id);
            return;
        }

        $itens = $this->compraModel->buscarItens((int)$id);

        $fornecedores = $this->db->fetchAll(
            "SELECT id, razao_social, nome_fantasia, prazo_pagamento, forma_pagamento
             FROM fornecedores WHERE ativo = 1 ORDER BY razao_social"
        );

        $produtos = $this->db->fetchAll(
            "SELECT p.id, p.nome, p.codigo, p.preco_custo,
                    COALESCE(u.sigla, 'UN') AS unidade_sigla
             FROM produtos p
             LEFT JOIN unidades u ON u.id = p.unidade_id
             WHERE p.ativo = 1
             ORDER BY p.nome ASC"
        );

        $categorias = $this->produtoModel->listarCategoriasForm();
        $unidades   = $this->produtoModel->listarUnidadesForm();

        $pageTitle  = 'Editar Compra ' . $compra['numero'];
        $breadcrumb = [
            ['label' => 'Dashboard',       'url' => '/dashboard'],
            ['label' => 'Compras',         'url' => '/compras'],
            ['label' => $compra['numero'], 'url' => '/compras/' . $id],
            ['label' => 'Editar',          'url' => '#'],
        ];

        $this->view('compras/edit', compact(
            'compra',
            'itens',
            'fornecedores',
            'produtos',
            'categorias',
            'unidades',
            'pageTitle',
            'breadcrumb'
        ));
    }

    // ----------------------------------------------------------------
    // SALVAR EDIÇÃO (POST /compras/{id}/editar)
    // ----------------------------------------------------------------

    public function update(Request $request, string $id): void
    {
        try {
            $compra = $this->compraModel->buscarPorId((int)$id);

            if (!$compra) {
                throw new \Exception('Compra não encontrada.');
            }

            if ($compra['status'] !== 'rascunho') {
                throw new \Exception('Só é possível editar compras em rascunho.');
            }

            $dados = [
                'fornecedor_id'   => (int)$request->input('fornecedor_id'),
                'numero_nf'       => $request->input('numero_nf'),
                'serie_nf'        => $request->input('serie_nf'),
                'forma_pagamento' => $request->input('forma_pagamento'),
                'prazo_pagamento' => $request->input('prazo_pagamento') ?: null,
                'vencimento'      => $request->input('vencimento')      ?: null,
                'data_emissao'    => $request->input('data_emissao')    ?: date('Y-m-d'),
                'frete'           => (float)str_replace(',', '.', $request->input('frete', '0')),
                'desconto_valor'  => (float)str_replace(',', '.', $request->input('desconto_valor', '0')),
                'observacao'      => $request->input('observacao'),
            ];

            if (!$dados['fornecedor_id']) {
                throw new \Exception('Selecione um fornecedor.');
            }

            $itens = $_POST['itens'] ?? [];

            if (empty($itens)) {
                throw new \Exception('Adicione ao menos um produto.');
            }

            $this->compraModel->atualizar((int)$id, $dados);
            $this->compraModel->removerItens((int)$id);
            $this->compraModel->salvarItens((int)$id, $itens);

            Session::flash('success', 'Compra atualizada com sucesso!');
            $this->redirect('/compras/' . $id);
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/compras/' . $id . '/editar');
        }
    }

    // ----------------------------------------------------------------
    // CONFIRMAR → atualiza estoque (POST /compras/{id}/confirmar)
    // ----------------------------------------------------------------

    public function confirmar(Request $request, string $id): void
    {
        try {
            $usuarioId = (int)Session::get('usuario_id');
            $this->compraModel->confirmar((int)$id, $usuarioId);
            Session::flash('success', 'Compra confirmada! Estoque atualizado com sucesso.');
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        $this->redirect('/compras/' . $id);
    }

    // ----------------------------------------------------------------
    // CANCELAR (POST /compras/{id}/cancelar)
    // ----------------------------------------------------------------

    public function cancelar(Request $request, string $id): void
    {
        try {
            $motivo    = $request->input('motivo') ?: 'Cancelado pelo usuário.';
            $estornar  = (bool)$request->input('estornar');
            $usuarioId = (int)Session::get('usuario_id');

            $this->compraModel->cancelar((int)$id, $motivo, $usuarioId);

            Session::flash('success', 'Compra cancelada.' . ($estornar ? ' Estoque estornado.' : ''));
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
        }

        $this->redirect('/compras/' . $id);
    }

    // ----------------------------------------------------------------
    // BUSCA RÁPIDA DE PRODUTO (JSON — autocomplete)
    // ----------------------------------------------------------------

    public function buscarProduto(Request $request): void
    {
        $q = trim($request->query('q', ''));

        if ($q === '') {
            $this->json(['produtos' => []]);
        }

        $sql = "
            SELECT
                p.id, p.nome, p.codigo, p.codigo_barras,
                p.preco_custo, p.estoque_atual,
                COALESCE(u.sigla, 'UN') AS unidade_sigla
            FROM produtos p
            LEFT JOIN unidades u ON u.id = p.unidade_id
            WHERE p.ativo = 1
              AND (p.nome LIKE :like OR p.codigo LIKE :like OR p.codigo_barras = :exact)
            ORDER BY p.nome ASC
            LIMIT 10
        ";

        $this->json([
            'produtos' => $this->db->fetchAll($sql, ['like' => "%{$q}%", 'exact' => $q])
        ]);
    }
}
