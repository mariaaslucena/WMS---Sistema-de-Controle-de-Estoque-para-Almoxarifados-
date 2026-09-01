<?php
$formatQtd = static function (float $valor): string {
    if (abs($valor - round($valor)) < 0.000001) { return number_format($valor, 0, ',', '.'); }
    return rtrim(rtrim(number_format($valor, 3, ',', '.'), '0'), ',');
};
?>
<section class="page-head map-page-head">
    <div><span class="badge">Mapa operacional</span><h1>Mapa de Estoque</h1><p>Registre entradas e saídas diretamente nas posições do almoxarifado.</p></div>
    <div class="page-head-actions"><a class="button ghost" href="?route=/movimentacoes">Histórico</a><a class="button ghost" href="?route=/dashboard">Voltar ao painel</a></div>
</section>

<?php if (!empty($flash)): ?>
<div class="alert <?= ($flash['tipo'] ?? '') === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars((string) ($flash['mensagem'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<section class="map-summary" aria-label="Resumo do mapa">
    <article class="map-stat"><strong><?= (int) $resumo['total'] ?></strong><span>Posições</span></article>
    <article class="map-stat free-stat"><strong><?= (int) $resumo['livres'] ?></strong><span>Livres</span></article>
    <article class="map-stat occupied-stat"><strong><?= (int) $resumo['ocupadas'] ?></strong><span>Ocupadas</span></article>
</section>

<section class="card map-card">
    <div class="map-toolbar">
        <div><span class="map-toolbar-label">Filtros de Rua</span><nav class="street-tabs" aria-label="Ruas do almoxarifado">
            <?php foreach ($ruas as $rua): ?><a class="street-tab <?= $rua === $ruaAtual ? 'active' : '' ?>" href="?route=/mapa&amp;rua=<?= urlencode($rua) ?>"><?= htmlspecialchars($rua, ENT_QUOTES, 'UTF-8') ?></a><?php endforeach; ?>
        </nav></div>
        <div class="map-legend"><span><i class="legend-box free"></i>Livre</span><span><i class="legend-box occupied"></i>Ocupado</span></div>
    </div>

    <div class="map-title-row"><div><span class="map-kicker">Mapa de Estoque</span><h2>Rua <?= htmlspecialchars($ruaAtual, ENT_QUOTES, 'UTF-8') ?></h2></div><p>Clique numa posição para movimentar o estoque.</p></div>

    <?php if (!$posicoes): ?>
        <div class="empty-state">Nenhuma posição ativa encontrada para esta rua.</div>
    <?php else: ?>
        <div class="stock-grid">
        <?php foreach ($posicoes as $posicao):
            $ocupada = (bool) $posicao['ocupada'];
            $label = $ruaAtual . '-' . (int) $posicao['numero'];
            $itensJson = htmlspecialchars(json_encode($posicao['itens'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
        ?>
            <button type="button" class="stock-position <?= $ocupada ? 'occupied' : 'free' ?>"
                data-position-id="<?= (int) $posicao['id'] ?>"
                data-position="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>"
                data-status="<?= $ocupada ? 'Ocupado' : 'Livre' ?>"
                data-items="<?= $itensJson ?>">
                <span class="position-number"><?= (int) $posicao['numero'] ?></span>
                <small><?= $ocupada ? ((int) $posicao['qtd_produtos'] . ((int) $posicao['qtd_produtos'] === 1 ? ' item' : ' itens')) : 'Livre' ?></small>
            </button>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<dialog class="position-dialog movement-dialog" id="positionDialog">
    <div class="dialog-head"><div><span class="badge" id="dialogStatus">Livre</span><h2 id="dialogTitle">Posição</h2></div><button type="button" class="dialog-close" id="dialogClose" aria-label="Fechar">×</button></div>
    <div id="dialogBody" class="movement-dialog-body"></div>

    <div class="movement-panels">
        <section class="movement-panel">
            <div class="movement-panel-title"><span class="movement-type entrada">Entrada</span><h3>Armazenar material</h3></div>
            <?php if (!$produtos): ?>
                <div class="alert error">Não há produtos ativos cadastrados.</div>
            <?php else: ?>
            <form method="post" action="?route=/movimentacoes/entrada" id="entradaForm">
                <input type="hidden" name="posicao_id" id="entradaPosicaoId">
                <input type="hidden" name="rua" value="<?= htmlspecialchars($ruaAtual, ENT_QUOTES, 'UTF-8') ?>">
                <label for="entradaProduto">Produto</label>
                <select name="produto_id" id="entradaProduto" required><option value="">Selecione...</option><?php foreach ($produtos as $produto): ?><option value="<?= (int) $produto['id'] ?>"><?= htmlspecialchars($produto['nome'] . ' (' . $produto['unidade_medida'] . ')', ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
                <label for="entradaQuantidade">Quantidade</label><input type="number" name="quantidade" id="entradaQuantidade" min="0.001" step="0.001" required>
                <label for="entradaObservacao">Observação <span class="optional">opcional</span></label><input type="text" name="observacao" id="entradaObservacao" maxlength="500">
                <button class="button full" type="submit">Registrar entrada</button>
            </form>
            <?php endif; ?>
        </section>

        <section class="movement-panel saida-panel" id="saidaPanel">
            <div class="movement-panel-title"><span class="movement-type saida">Saída</span><h3>Expedir para escola</h3></div>
            <div id="saidaEmpty" class="dialog-empty"><strong>Sem material para retirar</strong><p>Registre uma entrada nesta posição primeiro.</p></div>
            <form method="post" action="?route=/movimentacoes/saida" id="saidaForm" hidden>
                <input type="hidden" name="posicao_id" id="saidaPosicaoId">
                <input type="hidden" name="rua" value="<?= htmlspecialchars($ruaAtual, ENT_QUOTES, 'UTF-8') ?>">
                <label for="saidaProduto">Produto</label><select name="produto_id" id="saidaProduto" required></select>
                <div class="balance-hint" id="saidaSaldo"></div>
                <label for="saidaQuantidade">Quantidade a retirar</label><input type="number" name="quantidade" id="saidaQuantidade" min="0.001" step="0.001" required>
                <label for="saidaEscola">Escola de destino</label>
                <select name="escola_id" id="saidaEscola" required><option value="">Selecione...</option><?php foreach ($escolas as $escola): ?><option value="<?= (int) $escola['id'] ?>"><?= htmlspecialchars(($escola['codigo'] ? $escola['codigo'] . ' - ' : '') . $escola['nome'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
                <label for="saidaObservacao">Observação <span class="optional">opcional</span></label><input type="text" name="observacao" id="saidaObservacao" maxlength="500">
                <button class="button full danger-button" type="submit" <?= !$escolas ? 'disabled' : '' ?>>Registrar saída</button>
                <?php if (!$escolas): ?><p class="form-warning">Cadastre ao menos uma escola ativa para registrar saídas.</p><?php endif; ?>
            </form>
        </section>
    </div>
    <div class="dialog-actions"><button type="button" class="button secondary" id="dialogCloseBottom">Fechar</button></div>
</dialog>

<script>
(function () {
    const dialog = document.getElementById('positionDialog');
    const title = document.getElementById('dialogTitle');
    const status = document.getElementById('dialogStatus');
    const body = document.getElementById('dialogBody');
    const entradaPosicaoId = document.getElementById('entradaPosicaoId');
    const saidaPosicaoId = document.getElementById('saidaPosicaoId');
    const saidaForm = document.getElementById('saidaForm');
    const saidaEmpty = document.getElementById('saidaEmpty');
    const saidaProduto = document.getElementById('saidaProduto');
    const saidaQuantidade = document.getElementById('saidaQuantidade');
    const saidaSaldo = document.getElementById('saidaSaldo');
    const closeButtons = [document.getElementById('dialogClose'), document.getElementById('dialogCloseBottom')];
    let currentItems = [];

    function escapeHtml(value) { const div = document.createElement('div'); div.textContent = value == null ? '' : String(value); return div.innerHTML; }
    function formatQuantity(value) { return new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 3 }).format(Number(value)); }

    function updateExitBalance() {
        const id = Number(saidaProduto.value || 0);
        const item = currentItems.find(function (i) { return Number(i.produto_id) === id; });
        if (!item) { saidaSaldo.textContent = ''; saidaQuantidade.removeAttribute('max'); return; }
        saidaSaldo.textContent = 'Saldo disponível: ' + formatQuantity(item.quantidade) + ' ' + item.unidade;
        saidaQuantidade.max = String(item.quantidade);
    }
    saidaProduto.addEventListener('change', updateExitBalance);

    document.querySelectorAll('.stock-position').forEach(function (button) {
        button.addEventListener('click', function () {
            const positionId = button.dataset.positionId || '';
            const position = button.dataset.position || '';
            const currentStatus = button.dataset.status || 'Livre';
            try { currentItems = JSON.parse(button.dataset.items || '[]'); } catch (e) { currentItems = []; }

            entradaPosicaoId.value = positionId;
            saidaPosicaoId.value = positionId;
            title.textContent = 'Posição ' + position;
            status.textContent = currentStatus;
            status.className = 'badge ' + (currentStatus === 'Ocupado' ? 'badge-occupied' : 'badge-free');

            if (!currentItems.length) {
                body.innerHTML = '<div class="dialog-empty"><strong>Posição livre</strong><p>Escolha um produto e registre a entrada para ocupar esta posição.</p></div>';
                saidaForm.hidden = true;
                saidaEmpty.hidden = false;
                saidaProduto.innerHTML = '';
                saidaSaldo.textContent = '';
            } else {
                body.innerHTML = '<p class="dialog-intro">Material armazenado:</p>' + currentItems.map(function (item) {
                    return '<div class="dialog-item"><div><strong>' + escapeHtml(item.produto) + '</strong><span>' + escapeHtml(item.unidade) + '</span></div><b>' + formatQuantity(item.quantidade) + '</b></div>';
                }).join('');
                saidaProduto.innerHTML = currentItems.map(function (item) {
                    return '<option value="' + Number(item.produto_id) + '">' + escapeHtml(item.produto) + '</option>';
                }).join('');
                saidaForm.hidden = false;
                saidaEmpty.hidden = true;
                saidaQuantidade.value = '';
                updateExitBalance();
            }

            if (typeof dialog.showModal === 'function') { dialog.showModal(); } else { dialog.setAttribute('open', 'open'); }
        });
    });

    closeButtons.forEach(function (button) { button.addEventListener('click', function () { dialog.close(); }); });
    dialog.addEventListener('click', function (event) {
        const rect = dialog.getBoundingClientRect();
        const inside = event.clientX >= rect.left && event.clientX <= rect.right && event.clientY >= rect.top && event.clientY <= rect.bottom;
        if (!inside) { dialog.close(); }
    });
})();
</script>
