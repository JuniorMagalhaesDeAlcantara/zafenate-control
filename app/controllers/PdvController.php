<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Models\Venda as VendaModel;
use App\Models\Cliente as ClienteModel;

class PdvController extends Controller
{
    private Database $db;
    private VendaModel $vendaModel;
    private ClienteModel $clienteModel;
    public function __construct()
    {
        if (!Session::get('usuario_id')) {
            redirect('/login');
        }
        $this->db         = Database::getInstance();
        $this->vendaModel = new VendaModel();
        $this->clienteModel = new ClienteModel();
    }

    // ----------------------------------------------------------------
    // GET /pdv
    // ----------------------------------------------------------------
    public function index(): void
    {
        $usuarioId = Session::get('usuario_id');

        $caixa = $this->db->fetchOne(
            "SELECT * FROM caixas WHERE usuario_id = :uid AND status = 'aberto'",
            ['uid' => $usuarioId]
        );

        if (!$caixa) {
            Session::flash('error', 'Abra o caixa antes de acessar o PDV.');
            $this->redirect('/caixa');
            return;
        }

        $produtos = $this->db->fetchAll(
            "SELECT p.id, p.nome, p.preco_venda, p.estoque_atual,
                    p.codigo, u.sigla AS unidade_sigla
             FROM produtos p
             LEFT JOIN unidades u ON u.id = p.unidade_id
             WHERE p.ativo = 1
             ORDER BY p.nome ASC
             LIMIT 16"
        );

        $clientes = $this->db->fetchAll(
            "SELECT id, nome FROM clientes WHERE ativo = 1 ORDER BY nome ASC"
        );

        $vendasHoje = $this->db->fetchOne(
            "SELECT COALESCE(SUM(total), 0) AS total_dia
             FROM vendas
             WHERE caixa_id = :cid AND status = 'finalizada'",
            ['cid' => $caixa['id']]
        );

        $this->view('pdv/index', [
            'title'          => 'PDV — Frente de Caixa',
            'caixa'          => $caixa,
            'produtos'       => $produtos,
            'clientes'       => $clientes,
            'totalVendasDia' => $vendasHoje['total_dia'] ?? 0.00,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /pdv/buscar?q=...  — retorna JSON
    // ----------------------------------------------------------------
    public function buscar(Request $request): void
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            $this->json(['produtos' => []]);
        }

        $termo = "%{$q}%";

        $produtos = $this->db->fetchAll(
            "SELECT p.id, p.nome, p.preco_venda, p.estoque_atual,
                    p.codigo, p.codigo_barras, u.sigla AS unidade_sigla
             FROM produtos p
             LEFT JOIN unidades u ON u.id = p.unidade_id
             WHERE p.ativo = 1
               AND (p.nome           LIKE :busca_nome
                OR  p.codigo         LIKE :busca_codigo
                OR  p.codigo_barras  LIKE :busca_barras)
             ORDER BY p.nome ASC
             LIMIT 10",
            [
                'busca_nome'    => $termo,
                'busca_codigo'  => $termo,
                'busca_barras'  => $termo,
            ]
        );

        $this->json(['produtos' => $produtos]);
    }

    // ----------------------------------------------------------------
    // POST /pdv/finalizar
    // ----------------------------------------------------------------
    public function finalizar(Request $request): void
    {
        try {
            // Itens vêm como JSON serializado no campo 'itens'
            $itens = json_decode($request->input('itens', '[]'), true);

            if (empty($itens)) {
                Session::flash('error', 'Carrinho vazio. Adicione produtos antes de finalizar.');
                $this->redirect('/pdv');
                return;
            }

            $subtotal      = (float) $request->input('subtotal', 0);
            $descontoValor = (float) $request->input('desconto_valor', 0);
            $total         = (float) $request->input('total', 0);
            $descontoTipo  = $request->input('desconto_tipo', 'valor');
            $descontoPerc  = ($descontoTipo === 'percentual' && $subtotal > 0)
                ? round($descontoValor / $subtotal * 100, 4)
                : 0.00;

            $caixaId   = (int) $request->input('caixa_id');
            $clienteIdInput = $request->input('cliente_id');

            error_log('CLIENTE INPUT RAW: ' . print_r($clienteIdInput, true));

            $clienteId = !empty($clienteIdInput)
                ? (int) $clienteIdInput
                : null;

            error_log('CLIENTE FINAL: ' . print_r($clienteId, true));
            $usuarioId = (int) Session::get('usuario_id');

            // Suporta pagamento misto: array JSON de {forma, valor, troco}
            $pagamentosRaw = json_decode($request->input('pagamentos', '[]'), true);

            if (empty($pagamentosRaw) || !is_array($pagamentosRaw)) {
                Session::flash('error', 'Nenhum pagamento informado.');
                $this->redirect('/pdv');
                return;
            }

            $formasValidas = ['dinheiro', 'pix', 'cartao_debito', 'cartao_credito'];
            $pagamentos    = [];
            $totalPago     = 0.0;

            foreach ($pagamentosRaw as $p) {
                $forma = $p['forma'] ?? '';
                $valor = (float) ($p['valor'] ?? 0);
                $troco = (float) ($p['troco'] ?? 0);

                if (!in_array($forma, $formasValidas, true) || $valor <= 0) {
                    Session::flash('error', "Pagamento inválido: forma ou valor incorreto.");
                    $this->redirect('/pdv');
                    return;
                }

                $pagamentos[] = ['forma' => $forma, 'valor' => $valor, 'troco' => $troco];
                $totalPago   += $valor - $troco; // valor líquido recebido
            }

            error_log(print_r([
                'cliente_id_antes_salvar' => $clienteId
            ], true));
            // Validação: total pago (líquido) deve cobrir o total da venda
            if (round($totalPago, 2) < round($total, 2)) {
                Session::flash('error', sprintf(
                    'Total pago (R$ %.2f) inferior ao total da venda (R$ %.2f).',
                    $totalPago,
                    $total
                ));
                $this->redirect('/pdv');
                return;
            }


            $this->vendaModel->salvarVenda(
                dados: [
                    'caixa_id'       => $caixaId,
                    'cliente_id'     => $clienteId,
                    'usuario_id'     => $usuarioId,
                    'subtotal'       => $subtotal,
                    'desconto_tipo'  => $descontoTipo,
                    'desconto_valor' => $descontoValor,
                    'desconto_perc'  => $descontoPerc,
                    'total'          => $total,
                ],
                itens: $itens,
                pagamentos: $pagamentos
            );

            Session::flash('success', 'Venda finalizada com sucesso!');
            $this->redirect('/pdv');
        } catch (\RuntimeException $e) {
            // Erros de negócio (estoque insuficiente etc.) — mostra para o operador
            Session::flash('error', $e->getMessage());
            $this->redirect('/pdv');
        } catch (\Throwable $e) {
            error_log('[PDV] Erro ao finalizar venda: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            Session::flash('error', defined('APP_DEBUG') && APP_DEBUG
                ? $e->getMessage()
                : 'Erro interno ao processar venda. Tente novamente.');
            $this->redirect('/pdv');
        }
    }

    public function buscarRapidoEndpoint()
    {
        $termo = $_GET['termo'] ?? '';
        // Chama a função que você me mostrou
        $clientes = $this->clienteModel->buscarRapido($termo);

        header('Content-Type: application/json');
        echo json_encode($clientes);
        exit;
    }
}
