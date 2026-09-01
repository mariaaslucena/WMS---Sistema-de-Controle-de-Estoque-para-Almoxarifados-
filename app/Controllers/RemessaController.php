<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\SimplePdf;
use App\Models\Remessa;

final class RemessaController extends Controller
{
    public function sucesso(): void
    {
        Auth::requireLogin();
        $id = (int) ($_GET['movimentacao_id'] ?? 0);
        $rua = strtoupper(trim((string) ($_GET['rua'] ?? 'A')));
        $remessa = (new Remessa())->garantirParaMovimentacao($id);

        if (!$remessa) {
            http_response_code(404);
            $this->view('remessas/sucesso', [
                'title' => 'Guia de Remessa - WMS',
                'remessa' => null,
                'rua' => $rua,
            ]);
            return;
        }

        $this->view('remessas/sucesso', [
            'title' => 'Saída concluída - WMS',
            'remessa' => $remessa,
            'rua' => $rua,
        ]);
    }

    public function guia(): void
    {
        Auth::requireLogin();
        $id = (int) ($_GET['movimentacao_id'] ?? 0);
        $remessa = (new Remessa())->garantirParaMovimentacao($id);

        if (!$remessa) {
            http_response_code(404);
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'Guia de remessa não encontrada.';
            return;
        }

        $pdf = $this->montarPdf($remessa);
        $arquivo = 'Guia_Remessa_' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $remessa['numero_guia']) . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $arquivo . '"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, max-age=0, must-revalidate');
        echo $pdf;
    }

    private function montarPdf(array $r): string
    {
        $pdf = new SimplePdf();
        $pdf->rect(42, 68, 511, 724, 1.1);
        $pdf->text(60, 760, 18, 'GUIA DE REMESSA', true);
        $pdf->text(60, 738, 10, 'Sistema de Controle de Estoque - Almoxarifado Escolar');
        $pdf->text(410, 760, 10, 'Nº da guia', true);
        $pdf->text(410, 744, 12, (string) $r['numero_guia'], true);
        $pdf->line(60, 720, 535, 720, 1.0);

        $data = date('d/m/Y H:i', strtotime((string) $r['criado_em']));
        $destino = trim(((string) ($r['escola_codigo'] ?? '') !== '' ? (string) $r['escola_codigo'] . ' - ' : '') . (string) $r['escola_nome']);
        $quantidade = $this->formatarQuantidade((float) $r['quantidade']) . ' ' . (string) $r['unidade_medida'];
        $posicao = 'Rua ' . (string) $r['rua'] . ' - Posição ' . (string) $r['numero'];

        $pdf->text(60, 692, 10, 'Data e hora da saída', true);
        $pdf->text(60, 676, 11, $data);
        $pdf->text(315, 692, 10, 'Origem / posição', true);
        $pdf->text(315, 676, 11, $posicao);
        $pdf->line(60, 654, 535, 654, 0.5);

        $pdf->text(60, 630, 10, 'Unidade escolar de destino', true);
        $pdf->wrappedText(60, 612, 12, $destino, 460, 16, false);
        $pdf->line(60, 580, 535, 580, 0.5);

        $pdf->text(60, 554, 10, 'Material', true);
        $pdf->wrappedText(60, 536, 12, (string) $r['produto_nome'], 300, 16, false);
        $pdf->text(390, 554, 10, 'Quantidade', true);
        $pdf->text(390, 536, 12, $quantidade, true);
        $pdf->line(60, 504, 535, 504, 0.5);

        $pdf->text(60, 478, 10, 'Responsável pela movimentação', true);
        $responsavel = (string) $r['usuario_nome'] . ' (' . (string) $r['usuario_login'] . ')';
        $pdf->text(60, 460, 11, $responsavel);
        $pdf->line(60, 430, 535, 430, 0.5);

        $pdf->text(60, 404, 10, 'Observação', true);
        $observacao = trim((string) ($r['observacao'] ?? ''));
        $pdf->wrappedText(60, 384, 10.5, $observacao !== '' ? $observacao : 'Sem observações.', 460, 15, false);

        $pdf->line(85, 210, 265, 210, 0.7);
        $pdf->text(95, 194, 9, 'Responsável pela expedição');
        $pdf->line(330, 210, 510, 210, 0.7);
        $pdf->text(347, 194, 9, 'Responsável pelo recebimento');

        $pdf->line(60, 142, 535, 142, 0.5);
        $pdf->text(60, 124, 8.5, 'Documento gerado pelo WMS a partir da movimentação registrada no estoque.');
        $pdf->text(60, 108, 8.5, 'Movimentação #' . (string) $r['movimentacao_id'] . ' | Guia ' . (string) $r['numero_guia']);

        return $pdf->output();
    }

    private function formatarQuantidade(float $valor): string
    {
        if (abs($valor - round($valor)) < 0.000001) {
            return number_format($valor, 0, ',', '.');
        }
        return rtrim(rtrim(number_format($valor, 3, ',', '.'), '0'), ',');
    }
}
