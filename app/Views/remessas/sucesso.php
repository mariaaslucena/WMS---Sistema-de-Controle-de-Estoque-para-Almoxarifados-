<?php
$formatQtd = static function (float $valor): string {
    if (abs($valor - round($valor)) < 0.000001) { return number_format($valor, 0, ',', '.'); }
    return rtrim(rtrim(number_format($valor, 3, ',', '.'), '0'), ',');
};
?>
<?php if (!$remessa): ?>
<section class="card remessa-success-card"><div class="success-icon error-icon">!</div><h1>Guia de remessa não encontrada</h1><p>Não foi possível localizar a saída informada.</p><a class="button" href="?route=/mapa&amp;rua=<?= urlencode($rua) ?>">Voltar ao mapa</a></section>
<?php else: ?>
<section class="card remessa-success-card">
    <div class="success-icon">✓</div>
    <span class="badge">Guia <?= htmlspecialchars((string) $remessa['numero_guia'], ENT_QUOTES, 'UTF-8') ?></span>
    <h1>SAÍDA EFETIVADA COM SUCESSO!</h1>
    <p class="success-subtitle">O estoque foi atualizado e a guia de remessa foi gerada.</p>

    <div class="remessa-summary">
        <div><span>Material</span><strong><?= htmlspecialchars((string) $remessa['produto_nome'], ENT_QUOTES, 'UTF-8') ?></strong></div>
        <div><span>Quantidade</span><strong><?= htmlspecialchars($formatQtd((float) $remessa['quantidade']) . ' ' . (string) $remessa['unidade_medida'], ENT_QUOTES, 'UTF-8') ?></strong></div>
        <div><span>Destino</span><strong><?= htmlspecialchars(((string) ($remessa['escola_codigo'] ?? '') !== '' ? (string) $remessa['escola_codigo'] . ' - ' : '') . (string) $remessa['escola_nome'], ENT_QUOTES, 'UTF-8') ?></strong></div>
        <div><span>Origem</span><strong><?= htmlspecialchars('Rua ' . (string) $remessa['rua'] . ' - Posição ' . (string) $remessa['numero'], ENT_QUOTES, 'UTF-8') ?></strong></div>
    </div>

    <div class="success-actions">
        <a class="button remessa-print-button" target="_blank" rel="noopener" href="?route=/remessas/guia&amp;movimentacao_id=<?= (int) $remessa['movimentacao_id'] ?>">IMPRIMIR GUIA DE REMESSA</a>
        <a class="button ghost" href="?route=/mapa&amp;rua=<?= urlencode((string) $remessa['rua']) ?>">Voltar ao Mapa</a>
        <a class="text-link" href="?route=/movimentacoes">Ver histórico de movimentações</a>
    </div>
</section>
<?php endif; ?>
