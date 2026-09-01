<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Relatorio;
use DateTimeImmutable;
use Throwable;

final class RelatorioController extends Controller
{
    public function index(): void
    {
        Auth::requireGestor();

        $filtros = $this->filtros();
        $model = new Relatorio();

        try {
            $movimentacoes = $model->movimentacoes($filtros);
            $indicadores = $model->indicadores($filtros);
            $resumoProdutos = $model->resumoProdutos($filtros);
            $opcoes = $model->opcoes();
            $erro = null;
        } catch (Throwable $e) {
            error_log('[WMS] Erro no relatório: ' . $e->getMessage());
            $movimentacoes = [];
            $indicadores = ['total' => 0, 'entradas' => 0, 'saidas' => 0, 'escolas_atendidas' => 0];
            $resumoProdutos = [];
            $opcoes = ['escolas' => [], 'produtos' => [], 'usuarios' => []];
            $erro = 'Não foi possível carregar o relatório.';
        }

        $this->view('relatorios/index', [
            'title' => 'Relatórios e Auditoria - WMS',
            'filtros' => $filtros,
            'movimentacoes' => $movimentacoes,
            'indicadores' => $indicadores,
            'resumoProdutos' => $resumoProdutos,
            'opcoes' => $opcoes,
            'erro' => $erro,
        ]);
    }

    public function csv(): void
    {
        Auth::requireGestor();

        $filtros = $this->filtros();
        $movimentacoes = (new Relatorio())->movimentacoes($filtros);

        $nome = 'Relatorio_WMS_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nome . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $out = fopen('php://output', 'wb');
        if ($out === false) {
            return;
        }

        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'Data e hora',
            'Tipo',
            'Produto',
            'Quantidade',
            'Unidade',
            'Posição',
            'Escola de destino',
            'Usuário',
            'Guia',
            'Observação',
        ], ';');

        foreach ($movimentacoes as $m) {
            fputcsv($out, [
                date('d/m/Y H:i', strtotime((string) $m['criado_em'])),
                (string) $m['tipo'],
                (string) $m['produto_nome'],
                $this->formatarQuantidade((float) $m['quantidade']),
                (string) $m['unidade_medida'],
                (string) $m['rua'] . '-' . (string) $m['numero'],
                (string) ($m['escola_nome'] ?? ''),
                (string) $m['usuario_nome'],
                (string) ($m['numero_guia'] ?? ''),
                (string) ($m['observacao'] ?? ''),
            ], ';');
        }

        fclose($out);
    }

    public function imprimir(): void
    {
        Auth::requireGestor();

        $filtros = $this->filtros();
        $model = new Relatorio();

        $movimentacoes = $model->movimentacoes($filtros);
        $indicadores = $model->indicadores($filtros);
        $resumoProdutos = $model->resumoProdutos($filtros);
        $usuario = Auth::user();

        $formatarQuantidade = fn (float $v): string => $this->formatarQuantidade($v);

        require __DIR__ . '/../Views/relatorios/imprimir.php';
    }

    private function filtros(): array
    {
        $hoje = new DateTimeImmutable('today');
        $primeiroDiaMes = $hoje->modify('first day of this month');

        return [
            'data_inicial' => $this->dataValida((string) ($_GET['data_inicial'] ?? ''))
                ?: $primeiroDiaMes->format('Y-m-d'),
            'data_final' => $this->dataValida((string) ($_GET['data_final'] ?? ''))
                ?: $hoje->format('Y-m-d'),
            'tipo' => in_array(strtoupper((string) ($_GET['tipo'] ?? '')), ['ENTRADA', 'SAIDA'], true)
                ? strtoupper((string) $_GET['tipo'])
                : '',
            'escola_id' => max(0, (int) ($_GET['escola_id'] ?? 0)),
            'produto_id' => max(0, (int) ($_GET['produto_id'] ?? 0)),
            'usuario_id' => max(0, (int) ($_GET['usuario_id'] ?? 0)),
            'rua' => in_array(strtoupper((string) ($_GET['rua'] ?? '')), ['A', 'B', 'C', 'D', 'E', 'F', 'G'], true)
                ? strtoupper((string) $_GET['rua'])
                : '',
            'posicao' => ($p = (int) ($_GET['posicao'] ?? 0)) >= 1 && $p <= 40 ? $p : 0,
        ];
    }

    private function dataValida(string $data): ?string
    {
        $data = trim($data);
        if ($data === '') {
            return null;
        }

        $d = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
        return $d && $d->format('Y-m-d') === $data ? $data : null;
    }

    private function formatarQuantidade(float $valor): string
    {
        if (abs($valor - round($valor)) < 0.000001) {
            return number_format($valor, 0, ',', '.');
        }

        return rtrim(rtrim(number_format($valor, 3, ',', '.'), '0'), ',');
    }
}
