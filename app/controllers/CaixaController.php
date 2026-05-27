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
        $this->usuarioId  = (int) Session::get('usuario_id');
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
                'title' => 'Abrir Caixa - Zafenate Control',
                'csrf'  => csrf_field(),
            ]);
            return;
        }

        $movimentos = $this->caixaModel->movimentos($caixa['id']);

        $this->view('caixas/gestao', [
            'title'      => 'Gestão do Caixa - Zafenate Control',
            'caixa'      => $caixa,
            'movimentos' => $movimentos,
            'csrf'       => csrf_field(),
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
        } catch (\Exception $e) {
            Session::flash('error', 'Erro ao abrir caixa: ' . $e->getMessage());
            $this->redirect('/caixa');
        }
    }

    /**
     * POST /caixa/fechar
     */
    public function fechar(Request $request): void
    {
        try {
            $caixaId = (int) $request->input('caixa_id');

            $saldoInformado = $request->input('saldo_informado', '0,00');
            $saldoInformado = str_replace(['.', ','], ['', '.'], $saldoInformado);

            $observacao = $request->input('observacao_fechamento', null);

            if (!$caixaId) {
                throw new \Exception("ID do caixa não fornecido.");
            }

            $this->caixaModel->fechar($caixaId, $saldoInformado, $observacao);

            Session::flash('success', 'Caixa fechado com sucesso! Relatório atualizado.');
            $this->redirect('/caixa');
        } catch (\Exception $e) {
            Session::flash('error', 'Erro ao fechar caixa: ' . $e->getMessage());
            $this->redirect('/caixa');
        }
    }

    /**
     * GET /caixa/sangria
     * Carrega a tela (view) de sangria
     */
    // No seu CaixaController.php, método sangria():
    public function sangria(): void
    {
        $caixa = $this->caixaModel->buscarAbertoPorUsuario($this->usuarioId);

        if (!$caixa) {
            Session::flash('error', 'Nenhum caixa aberto.');
            $this->redirect('/caixa');
            return;
        }

        // Passamos o array $caixa para a view
        $this->view('caixas/sangria', [
            'title'    => 'Realizar Sangria',
            'caixa'    => $caixa, // <--- O erro morre aqui
            'caixa_id' => $caixa['id']
        ]);
    }

    /**
     * POST /caixa/sangria
     * Processa a lógica de salvamento
     */
    public function salvarSangria(Request $request): void
    {
        try {
            $caixaId = (int) $request->input('caixa_id');

            if (!$caixaId) {
                throw new \InvalidArgumentException("ID do caixa não fornecido.");
            }

            // Converte "1.234,56" → 1234.56
            $valorRaw = $request->input('valor', '0,00');
            $valor    = (float) str_replace(['.', ','], ['', '.'], $valorRaw);

            if ($valor <= 0) {
                throw new \InvalidArgumentException("Informe um valor maior que zero para a sangria.");
            }

            $motivo = trim($request->input('motivo', ''));
            if (strlen($motivo) < 3) {
                throw new \InvalidArgumentException("O motivo é obrigatório (mínimo 3 caracteres).");
            }

            $this->caixaModel->registrarSangria($caixaId, $valor, $motivo, $this->usuarioId);

            Session::flash('success', sprintf(
                'Sangria de R$ %s registrada com sucesso.',
                number_format($valor, 2, ',', '.')
            ));

            $this->redirect('/caixa/sangria');
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/caixa/sangria'); // Volta para a tela de sangria em caso de erro
        } catch (\Throwable $e) {
            error_log('[Caixa] Erro na sangria: ' . $e->getMessage());
            Session::flash('error', 'Erro interno ao registrar sangria. Tente novamente.');
            $this->redirect('/caixa/sangria');
        }
    }
}
