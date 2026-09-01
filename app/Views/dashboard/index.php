<section class="dashboard-head">
    <div>
        <span class="badge">Acesso autenticado</span>
        <h1>Olá, <?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?>.</h1>
        <p>Perfil ativo: <strong><?= htmlspecialchars($usuario['perfil'], ENT_QUOTES, 'UTF-8') ?></strong></p>
    </div>
</section>

<?php if (($usuario['perfil'] ?? '') === 'GESTOR'): ?>
<section class="section-head">
    <div>
        <h2>Cadastros</h2>
        <p>Base de dados utilizada pelo estoque e pelas movimentações.</p>
    </div>
</section>

<section class="grid dashboard-grid">
    <a class="card feature-card feature-link" href="?route=/categorias">
        <div class="feature-icon">▤</div>
        <h2>Categorias</h2>
        <p>Organize os materiais por categoria.</p>
        <span class="status">Disponível</span>
    </a>

    <a class="card feature-card feature-link" href="?route=/produtos">
        <div class="feature-icon">□</div>
        <h2>Produtos</h2>
        <p>Cadastre materiais, unidade e estoque mínimo.</p>
        <span class="status">Disponível</span>
    </a>

    <a class="card feature-card feature-link" href="?route=/escolas">
        <div class="feature-icon">⌂</div>
        <h2>Escolas</h2>
        <p>Cadastre as unidades escolares de destino.</p>
        <span class="status">Disponível</span>
    </a>
</section>
<?php endif; ?>

<section class="section-head spaced">
    <div>
        <h2>Operação do WMS</h2>
        <p>Fluxo operacional do almoxarifado.</p>
    </div>
</section>

<section class="grid dashboard-grid">
    <a class="card feature-card feature-link" href="?route=/mapa">
        <div class="feature-icon">▦</div>
        <h2>Mapa de Estoque</h2>
        <p>Entrada e saída diretamente nas posições A–G / 1–40.</p>
        <span class="status">Operacional</span>
    </a>

    <a class="card feature-card feature-link" href="?route=/movimentacoes">
        <div class="feature-icon">⇄</div>
        <h2>Movimentações + Guias</h2>
        <p>Histórico de entradas e saídas, com guia de remessa em PDF.</p>
        <span class="status">Operacional</span>
    </a>

    <?php if (($usuario['perfil'] ?? '') === 'GESTOR'): ?>
    <a class="card feature-card feature-link" href="?route=/relatorios">
        <div class="feature-icon">▥</div>
        <h2>Relatórios e Auditoria</h2>
        <p>Filtros, consolidação por produto, exportação e impressão.</p>
        <span class="status">Operacional</span>
    </a>
    <?php endif; ?>
</section>
