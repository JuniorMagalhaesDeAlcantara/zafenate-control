<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Caixa as CaixaModel;

class CaixaController extends Controller
{
    private CaixaModel $caixaModel;
    private int $usuarioId;

    public function __construct()
    {
        if (!Session::get('usuario_id')) {
            $this->redirect('/login');
            exit;
        }
        $this->caixaModel = new CaixaModel();
        $this->usuarioId = (int)Session::get('usuario_id');
    }

    /**
     * GET /caixa
     * Exibe a tela de abertura ou o painel de gestão
     */
    public function index(Request $request): void
    {
        $caixa = $this->caixaModel->buscarAbertoPorUsuario($this->usuarioId);

        if (!$caixa) {
            $this->view('caixas/abrir', [
                'title' => 'Abrir Caixa - Zafenate Control'
            ]);
            return;
        }

        $this->view('caixas/gestao', [
            'title' => 'Gestão do Caixa - Zafenate Control',
            'caixa' => $caixa
        ]);
    }

    /**
     * POST /caixa/abrir
     */
    public function abrir(Request $request): void
    {
        try {
            $idUsuarioLogado = (int) Session::get('usuario_id');
            if (!$idUsuarioLogado) {
                throw new \Exception("Sessão inválida. Faça login novamente.");
            }

            $saldoAbertura = $request->input('saldo_abertura', '0,00');
            $saldoAbertura = str_replace(['.', ','], ['', '.'], $saldoAbertura);

            $this->caixaModel->abrir($idUsuarioLogado, $saldoAbertura);

            Session::flash('success', 'Caixa aberto com sucesso!');
            $this->redirect('/caixa');
            return;
        } catch (\Exception $e) {
            Session::flash('error', 'Erro ao abrir caixa: ' . $e->getMessage());
            $this->redirect('/caixa');
            return;
        }
    }

    /**
     * POST /caixa/fechar
     * Processa o fechamento do caixa atual
     */
    public function fechar(Request $request): void
    {
        try {
            $caixaId = (int)$request->input('caixa_id');

            // Pega o valor que o operador contou na gaveta
            $saldoInformado = $request->input('saldo_informado', '0,00');
            // Trata o formato de moeda brasileira para o padrão americano do banco
            $saldoInformado = str_replace(['.', ','], ['', '.'], $saldoInformado);

            $observacao = $request->input('observacao_fechamento', null);

            if (!$caixaId) {
                throw new \Exception("ID do caixa não fornecido.");
            }

            // Dispara a lógica matemática e o UPDATE na Model
            $this->caixaModel->fechar($caixaId, $saldoInformado, $observacao);

            Session::flash('success', 'Caixa fechado com sucesso! Relatório atualizado.');
            $this->redirect('/caixa');
            return;
        } catch (\Exception $e) {
            Session::flash('error', 'Erro ao fechar caixa: ' . $e->getMessage());
            $this->redirect('/caixa');
            return;
        }
    }
}
