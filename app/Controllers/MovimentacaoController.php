<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Movimentacao;
use DomainException;
use Throwable;

final class MovimentacaoController extends Controller
{
    private const RUAS = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

    public function index(): void
    {
        Auth::requireLogin();
        $this->view('movimentacoes/index', [
            'title' => 'Movimentações - WMS',
            'movimentacoes' => (new Movimentacao())->listarRecentes(100),
        ]);
    }

    public function entrada(): void
    {
        Auth::requireLogin();
        $rua = $this->ruaRetorno();
        $usuario = Auth::user();

        try {
            (new Movimentacao())->registrarEntrada(
                (int) ($_POST['produto_id'] ?? 0),
                (int) ($_POST['posicao_id'] ?? 0),
                $this->quantidade($_POST['quantidade'] ?? null),
                (int) ($usuario['id'] ?? 0),
                (string) ($_POST['observacao'] ?? '')
            );
            $this->flash('success', 'Entrada registrada. O saldo e o mapa foram atualizados.');
        } catch (DomainException $e) {
            $this->flash('error', $e->getMessage());
        } catch (Throwable $e) {
            error_log('[WMS] Erro na entrada de estoque: ' . $e->getMessage());
            $this->flash('error', 'Não foi possível registrar a entrada. Tente novamente.');
        }

        $this->redirect('/mapa', ['rua' => $rua]);
    }

    public function saida(): void
    {
        Auth::requireLogin();
        $rua = $this->ruaRetorno();
        $usuario = Auth::user();

        try {
            $movimentacaoId = (new Movimentacao())->registrarSaida(
                (int) ($_POST['produto_id'] ?? 0),
                (int) ($_POST['posicao_id'] ?? 0),
                (int) ($_POST['escola_id'] ?? 0),
                $this->quantidade($_POST['quantidade'] ?? null),
                (int) ($usuario['id'] ?? 0),
                (string) ($_POST['observacao'] ?? '')
            );
            $this->redirect('/remessas/sucesso', ['movimentacao_id' => $movimentacaoId, 'rua' => $rua]);
        } catch (DomainException $e) {
            $this->flash('error', $e->getMessage());
        } catch (Throwable $e) {
            error_log('[WMS] Erro na saída de estoque: ' . $e->getMessage());
            $this->flash('error', 'Não foi possível registrar a saída. Tente novamente.');
        }

        $this->redirect('/mapa', ['rua' => $rua]);
    }

    private function quantidade(mixed $valor): float
    {
        $texto = trim((string) $valor);
        $texto = str_replace(',', '.', $texto);
        if ($texto === '' || !is_numeric($texto)) { return 0.0; }
        return round((float) $texto, 3);
    }

    private function ruaRetorno(): string
    {
        $rua = strtoupper(trim((string) ($_POST['rua'] ?? 'A')));
        return in_array($rua, self::RUAS, true) ? $rua : 'A';
    }
}
