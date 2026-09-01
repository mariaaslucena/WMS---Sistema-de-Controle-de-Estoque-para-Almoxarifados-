<?php
declare(strict_types=1);

$e = static fn (mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

function rotuloFiltro(array $filtros): string
{
    $partes = [];
    if (($filtros['data_inicial'] ?? '') !== '') {
        $partes[] = 'de ' . date('d/m/Y', strtotime((string) $filtros['data_inicial']));
    }
    if (($filtros['data_final'] ?? '') !== '') {
        $partes[] = 'até ' . date('d/m/Y', strtotime((string) $filtros['data_final']));
    }
    if (($filtros['tipo'] ?? '') !== '') {
        $partes[] = 'tipo ' . $filtros['tipo'];
    }
    return $partes ? implode(' · ', $partes) : 'Todos os registros';
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Relatório WMS</title>
<style>
*{box-sizing:border-box}
body{font-family:Arial,Helvetica,sans-serif;margin:0;color:#111;background:#eceff3}
.sheet{width:210mm;min-height:297mm;margin:18px auto;padding:16mm;background:#fff}
.header{display:flex;justify-content:space-between;gap:20px;border-bottom:2px solid #111;padding-bottom:12px}
.header h1{margin:0 0 6px;font-size:24px}.header p{margin:2px 0;font-size:12px;color:#444}
.meta{text-align:right;font-size:11px}.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:16px 0}
.stat{border:1px solid #bbb;padding:10px}.stat strong{display:block;font-size:20px}.stat span{font-size:10px;text-transform:uppercase}
h2{font-size:16px;margin:20px 0 8px}.summary{width:100%;border-collapse:collapse;font-size:10px}
.summary th,.summary td{border:1px solid #bbb;padding:6px;text-align:left}.summary th{background:#f1f1f1}
.detail{width:100%;border-collapse:collapse;font-size:8.5px}.detail th,.detail td{border-bottom:1px solid #ccc;padding:5px 4px;text-align:left;vertical-align:top}
.detail th{background:#f1f1f1}.type{font-weight:bold}.footer-note{margin-top:18px;font-size:9px;color:#555}
.print-actions{width:210mm;margin:12px auto;display:flex;gap:8px}.print-actions button,.print-actions a{border:0;padding:10px 14px;background:#315fb3;color:#fff;text-decoration:none;font-weight:bold;cursor:pointer}
@media print{
 body{background:#fff}
 .sheet{width:auto;min-height:auto;margin:0;padding:10mm}
 .print-actions{display:none}
 @page{size:A4 landscape;margin:8mm}
 .sheet{page-break-after:auto}
}
</style>
</head>
<body>
<div class="print-actions">
    <button onclick="window.print()">Imprimir / Salvar como PDF</button>
    <a href="?route=/relatorios">Voltar</a>
</div>

<main class="sheet">
    <header class="header">
        <div>
            <h1>RELATÓRIO DE MOVIMENTAÇÕES — WMS</h1>
            <p>Sistema de Controle de Estoque — Almoxarifado Escolar</p>
            <p><?= $e(rotuloFiltro($filtros)) ?></p>
        </div>
        <div class="meta">
            <strong>Gerado em:</strong><br>
            <?= $e(date('d/m/Y H:i')) ?><br><br>
            <strong>Responsável:</strong><br>
            <?= $e($usuario['nome'] ?? '') ?><br>
            <?= $e($usuario['login'] ?? '') ?>
        </div>
    </header>

    <section class="stats">
        <div class="stat"><strong><?= (int) $indicadores['total'] ?></strong><span>Movimentações</span></div>
        <div class="stat"><strong><?= (int) $indicadores['entradas'] ?></strong><span>Entradas</span></div>
        <div class="stat"><strong><?= (int) $indicadores['saidas'] ?></strong><span>Saídas</span></div>
        <div class="stat"><strong><?= (int) $indicadores['escolas_atendidas'] ?></strong><span>Escolas atendidas</span></div>
    </section>

    <h2>Resumo por produto</h2>
    <table class="summary">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Entradas</th>
                <th>Saídas</th>
                <th>Unidade</th>
                <th>Movimentações</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$resumoProdutos): ?>
            <tr><td colspan="5">Sem dados no período.</td></tr>
        <?php else: ?>
            <?php foreach ($resumoProdutos as $r): ?>
            <tr>
                <td><?= $e($r['produto_nome']) ?></td>
                <td><?= $e($formatarQuantidade((float) $r['entradas_quantidade'])) ?></td>
                <td><?= $e($formatarQuantidade((float) $r['saidas_quantidade'])) ?></td>
                <td><?= $e($r['unidade_medida']) ?></td>
                <td><?= (int) $r['movimentacoes'] ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <h2>Movimentações detalhadas</h2>
    <table class="detail">
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Produto</th>
                <th>Pos.</th>
                <th>Qtd.</th>
                <th>Destino</th>
                <th>Usuário</th>
                <th>Guia</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$movimentacoes): ?>
            <tr><td colspan="8">Nenhuma movimentação encontrada.</td></tr>
        <?php else: ?>
            <?php foreach ($movimentacoes as $m): ?>
            <tr>
                <td><?= $e(date('d/m/Y H:i', strtotime((string) $m['criado_em']))) ?></td>
                <td class="type"><?= $e($m['tipo']) ?></td>
                <td><?= $e($m['produto_nome']) ?></td>
                <td><?= $e($m['rua'] . '-' . $m['numero']) ?></td>
                <td><?= $e($formatarQuantidade((float) $m['quantidade']) . ' ' . $m['unidade_medida']) ?></td>
                <td><?= $e($m['escola_nome'] ?: '—') ?></td>
                <td><?= $e($m['usuario_nome']) ?></td>
                <td><?= $e($m['numero_guia'] ?: '—') ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <p class="footer-note">
        Relatório gerado diretamente a partir dos registros de movimentação do WMS para fins de conferência, auditoria e prestação de contas.
    </p>
</main>
</body>
</html>
