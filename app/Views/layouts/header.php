<?php
use App\Core\Auth;

$title = $title ?? 'WMS';
$usuarioLogado = Auth::user();
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="assets/css/app.css?v=0.7.0">
</head>
<body>
<header class="topbar">
    <a class="brand" href="?route=/">WMS</a>
    <div class="topbar-title">
        <strong>Almoxarifado Escolar</strong>
        <span class="version">v0.7</span>
    </div>

    <?php if ($usuarioLogado): ?>
        <nav class="topnav" aria-label="Navegação principal">
            <a href="?route=/dashboard">Painel</a>
            <a href="?route=/mapa">Mapa</a>
            <a href="?route=/movimentacoes">Movimentações</a>
            <?php if (Auth::isGestor()): ?>
                <a href="?route=/relatorios">Relatórios</a>
                <a href="?route=/categorias">Categorias</a>
                <a href="?route=/produtos">Produtos</a>
                <a href="?route=/escolas">Escolas</a>
            <?php endif; ?>
        </nav>

        <div class="user-area">
            <div class="user-meta">
                <strong><?= htmlspecialchars($usuarioLogado['nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars($usuarioLogado['perfil'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <a class="logout-link" href="?route=/logout">Sair</a>
        </div>
    <?php endif; ?>
</header>

<main class="container <?= $usuarioLogado ? '' : 'login-container' ?>">
