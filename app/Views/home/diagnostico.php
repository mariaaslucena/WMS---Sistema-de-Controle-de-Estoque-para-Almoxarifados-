<section class="card diagnostic">
    <h1>Diagnóstico da instalação</h1>

    <dl>
        <div>
            <dt>PHP</dt>
            <dd><?= htmlspecialchars($phpVersion, ENT_QUOTES, 'UTF-8') ?></dd>
        </div>
        <div>
            <dt>Banco MySQL</dt>
            <dd><?= htmlspecialchars($statusBanco, ENT_QUOTES, 'UTF-8') ?></dd>
        </div>
    </dl>

    <p class="detail"><?= htmlspecialchars($detalhe, ENT_QUOTES, 'UTF-8') ?></p>

    <a class="button secondary" href="?route=/">Voltar</a>
</section>
