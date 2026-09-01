<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\CategoriaController;
use App\Controllers\DashboardController;
use App\Controllers\EscolaController;
use App\Controllers\HomeController;
use App\Controllers\MapaController;
use App\Controllers\MovimentacaoController;
use App\Controllers\ProdutoController;
use App\Controllers\RelatorioController;
use App\Controllers\RemessaController;
use App\Core\Router;

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/diagnostico', [HomeController::class, 'diagnostico']);

$router->get('/mapa', [MapaController::class, 'index']);

$router->get('/movimentacoes', [MovimentacaoController::class, 'index']);
$router->post('/movimentacoes/entrada', [MovimentacaoController::class, 'entrada']);
$router->post('/movimentacoes/saida', [MovimentacaoController::class, 'saida']);

$router->get('/remessas/sucesso', [RemessaController::class, 'sucesso']);
$router->get('/remessas/guia', [RemessaController::class, 'guia']);

$router->get('/relatorios', [RelatorioController::class, 'index']);
$router->get('/relatorios/csv', [RelatorioController::class, 'csv']);
$router->get('/relatorios/imprimir', [RelatorioController::class, 'imprimir']);

$router->get('/categorias', [CategoriaController::class, 'index']);
$router->post('/categorias/salvar', [CategoriaController::class, 'salvar']);
$router->post('/categorias/status', [CategoriaController::class, 'alterarStatus']);

$router->get('/produtos', [ProdutoController::class, 'index']);
$router->post('/produtos/salvar', [ProdutoController::class, 'salvar']);
$router->post('/produtos/status', [ProdutoController::class, 'alterarStatus']);

$router->get('/escolas', [EscolaController::class, 'index']);
$router->post('/escolas/salvar', [EscolaController::class, 'salvar']);
$router->post('/escolas/status', [EscolaController::class, 'alterarStatus']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_GET['route'] ?? '/');
