<?php
$formatQtd = static function (float $valor): string {
    if (abs($valor - round($valor)) < 0.000001) { return number_format($valor, 0, ',', '.'); }
    return rtrim(rtrim(number_format($valor, 3, ',', '.'), '0'), ',');
};
?>
<section class="page-head"><div><span class="badge">Rastreabilidade</span><h1>Movimentações</h1><p>Últimas entradas e saídas registradas no almoxarifado.</p></div><a class="button ghost" href="?route=/mapa">Abrir mapa</a></section>

<?php if (!$movimentacoes): ?>
<section class="card"><div class="empty-state"><strong>Nenhuma movimentação registrada.</strong><p>Use o mapa para fazer a primeira entrada de estoque.</p></div></section>
<?php else: ?>
<section class="card list-card"><div class="list-title"><h2>Histórico recente</h2><span><?= count($movimentacoes) ?> registro(s)</span></div><div class="table-wrap"><table>
<thead><tr><th>Data</th><th>Tipo</th><th>Produto</th><th>Posição</th><th>Quantidade</th><th>Destino</th><th>Usuário</th><th>Guia</th></tr></thead>
<tbody><?php foreach ($movimentacoes as $mov): ?><tr>
<td><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string) $mov['criado_em'])), ENT_QUOTES, 'UTF-8') ?></td>
<td><span class="movement-pill <?= $mov['tipo'] === 'ENTRADA' ? 'entrada' : 'saida' ?>"><?= htmlspecialchars((string) $mov['tipo'], ENT_QUOTES, 'UTF-8') ?></span></td>
<td><strong><?= htmlspecialchars((string) $mov['produto_nome'], ENT_QUOTES, 'UTF-8') ?></strong><?php if (!empty($mov['observacao'])): ?><small><?= htmlspecialchars((string) $mov['observacao'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></td>
<td><?= htmlspecialchars((string) $mov['rua'] . '-' . (string) $mov['numero'], ENT_QUOTES, 'UTF-8') ?></td>
<td><?= htmlspecialchars($formatQtd((float) $mov['quantidade']) . ' ' . (string) $mov['unidade_medida'], ENT_QUOTES, 'UTF-8') ?></td>
<td><?= $mov['tipo'] === 'SAIDA' ? htmlspecialchars((string) ($mov['escola_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') : '—' ?></td>
<td><?= htmlspecialchars((string) $mov['usuario_nome'], ENT_QUOTES, 'UTF-8') ?></td>
<td><?php if ($mov['tipo'] === 'SAIDA'): ?><a class="pdf-link" target="_blank" rel="noopener" href="?route=/remessas/guia&amp;movimentacao_id=<?= (int) $mov['id'] ?>">PDF<?= !empty($mov['numero_guia']) ? ' · ' . htmlspecialchars((string) $mov['numero_guia'], ENT_QUOTES, 'UTF-8') : '' ?></a><?php else: ?>—<?php endif; ?></td>
</tr><?php endforeach; ?></tbody></table></div></section>
<?php endif; ?>
