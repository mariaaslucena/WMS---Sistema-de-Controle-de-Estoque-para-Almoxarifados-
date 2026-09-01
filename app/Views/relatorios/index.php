<?php
$e = static fn (mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$fmtQtd = static function (float $v): string {
    if (abs($v - round($v)) < 0.000001) {
        return number_format($v, 0, ',', '.');
    }
    return rtrim(rtrim(number_format($v, 3, ',', '.'), '0'), ',');
};

$queryExport = http_build_query(array_merge(['route' => '/relatorios/csv'], $filtros));
$queryPrint = http_build_query(array_merge(['route' => '/relatorios/imprimir'], $filtros));
?>

<section class="page-head report-page-head">
    <div>
        <span class="badge">Gestão e auditoria</span>
        <h1>Relatórios e Auditoria</h1>
        <p>Consulte as movimentações por período e refine os resultados pelos filtros operacionais.</p>
    </div>
    <div class="page-head-actions">
        <a class="button secondary" href="?<?= $e($queryPrint) ?>" target="_blank" rel="noopener">Imprimir / PDF</a>
        <a class="button" href="?<?= $e($queryExport) ?>">Exportar CSV</a>
    </div>
</section>

<?php if ($erro): ?>
<div class="flash error"><?= $e($erro) ?></div>
<?php endif; ?>

<section class="card report-filter-card">
    <form method="get" class="report-filter-form">
        <input type="hidden" name="route" value="/relatorios">

        <div class="report-filter-grid">
            <label>
                Data inicial
                <input type="date" name="data_inicial" value="<?= $e($filtros['data_inicial']) ?>">
            </label>

            <label>
                Data final
                <input type="date" name="data_final" value="<?= $e($filtros['data_final']) ?>">
            </label>

            <label>
                Tipo
                <select name="tipo">
                    <option value="">Entrada e saída</option>
                    <option value="ENTRADA" <?= $filtros['tipo'] === 'ENTRADA' ? 'selected' : '' ?>>Entrada</option>
                    <option value="SAIDA" <?= $filtros['tipo'] === 'SAIDA' ? 'selected' : '' ?>>Saída</option>
                </select>
            </label>

            <label>
                Escola
                <select name="escola_id">
                    <option value="0">Todas</option>
                    <?php foreach ($opcoes['escolas'] as $escola): ?>
                        <option value="<?= (int) $escola['id'] ?>" <?= (int) $filtros['escola_id'] === (int) $escola['id'] ? 'selected' : '' ?>>
                            <?= $e(($escola['codigo'] ? $escola['codigo'] . ' - ' : '') . $escola['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Produto
                <select name="produto_id">
                    <option value="0">Todos</option>
                    <?php foreach ($opcoes['produtos'] as $produto): ?>
                        <option value="<?= (int) $produto['id'] ?>" <?= (int) $filtros['produto_id'] === (int) $produto['id'] ? 'selected' : '' ?>>
                            <?= $e($produto['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Usuário
                <select name="usuario_id">
                    <option value="0">Todos</option>
                    <?php foreach ($opcoes['usuarios'] as $usuario): ?>
                        <option value="<?= (int) $usuario['id'] ?>" <?= (int) $filtros['usuario_id'] === (int) $usuario['id'] ? 'selected' : '' ?>>
                            <?= $e($usuario['nome'] . ' (' . $usuario['login'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Rua
                <select name="rua">
                    <option value="">Todas</option>
                    <?php foreach (['A','B','C','D','E','F','G'] as $rua): ?>
                        <option value="<?= $rua ?>" <?= $filtros['rua'] === $rua ? 'selected' : '' ?>>Rua <?= $rua ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Posição
                <select name="posicao">
                    <option value="0">Todas</option>
                    <?php for ($i = 1; $i <= 40; $i++): ?>
                        <option value="<?= $i ?>" <?= (int) $filtros['posicao'] === $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </label>
        </div>

        <div class="report-filter-actions">
            <button class="button" type="submit">Aplicar filtros</button>
            <a class="text-link" href="?route=/relatorios">Limpar filtros</a>
        </div>
    </form>
</section>

<section class="report-stats">
    <article class="report-stat">
        <strong><?= (int) $indicadores['total'] ?></strong>
        <span>Movimentações</span>
    </article>
    <article class="report-stat entrada-stat">
        <strong><?= (int) $indicadores['entradas'] ?></strong>
        <span>Entradas</span>
    </article>
    <article class="report-stat saida-stat">
        <strong><?= (int) $indicadores['saidas'] ?></strong>
        <span>Saídas</span>
    </article>
    <article class="report-stat">
        <strong><?= (int) $indicadores['escolas_atendidas'] ?></strong>
        <span>Escolas atendidas</span>
    </article>
</section>

<section class="card list-card report-summary-card">
    <div class="list-title">
        <div>
            <h2>Resumo por produto</h2>
            <p>Quantidades consolidadas conforme os filtros aplicados.</p>
        </div>
        <span><?= count($resumoProdutos) ?> produto(s)</span>
    </div>

    <?php if (!$resumoProdutos): ?>
        <div class="empty-state">Nenhum produto encontrado no período selecionado.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
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
                <?php foreach ($resumoProdutos as $r): ?>
                    <tr>
                        <td><strong><?= $e($r['produto_nome']) ?></strong></td>
                        <td><?= $fmtQtd((float) $r['entradas_quantidade']) ?></td>
                        <td><?= $fmtQtd((float) $r['saidas_quantidade']) ?></td>
                        <td><?= $e($r['unidade_medida']) ?></td>
                        <td><?= (int) $r['movimentacoes'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="card list-card report-detail-card">
    <div class="list-title">
        <div>
            <h2>Movimentações detalhadas</h2>
            <p>Rastreabilidade dos registros encontrados.</p>
        </div>
        <span><?= count($movimentacoes) ?> registro(s)</span>
    </div>

    <?php if (!$movimentacoes): ?>
        <div class="empty-state">Nenhuma movimentação encontrada com esses filtros.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Produto</th>
                        <th>Posição</th>
                        <th>Quantidade</th>
                        <th>Destino</th>
                        <th>Usuário</th>
                        <th>Guia</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($movimentacoes as $m): ?>
                    <tr>
                        <td><?= $e(date('d/m/Y H:i', strtotime((string) $m['criado_em']))) ?></td>
                        <td>
                            <span class="movement-pill <?= $m['tipo'] === 'ENTRADA' ? 'entrada' : 'saida' ?>">
                                <?= $e($m['tipo']) ?>
                            </span>
                        </td>
                        <td>
                            <strong><?= $e($m['produto_nome']) ?></strong>
                            <?php if (trim((string) ($m['observacao'] ?? '')) !== ''): ?>
                                <small><?= $e($m['observacao']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= $e($m['rua'] . '-' . $m['numero']) ?></td>
                        <td><?= $fmtQtd((float) $m['quantidade']) ?> <?= $e($m['unidade_medida']) ?></td>
                        <td><?= $e($m['escola_nome'] ?: '—') ?></td>
                        <td><?= $e($m['usuario_nome']) ?></td>
                        <td>
                            <?php if ($m['numero_guia']): ?>
                                <a class="pdf-link" target="_blank" rel="noopener"
                                   href="?route=/remessas/guia&movimentacao_id=<?= (int) $m['id'] ?>">
                                    <?= $e($m['numero_guia']) ?>
                                </a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
