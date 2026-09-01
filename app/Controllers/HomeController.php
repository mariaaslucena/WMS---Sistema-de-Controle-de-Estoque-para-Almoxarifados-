<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->redirect(Auth::check() ? '/dashboard' : '/login');
    }

    public function diagnostico(): void
    {
        $statusBanco = 'Não configurado';
        $detalhe = 'Crie config/database.php quando tivermos os dados do MySQL da hospedagem.';

        try {
            $pdo = Database::connection();
            $pdo->query('SELECT 1');
            $statusBanco = 'Conectado';
            $detalhe = 'Conexão com o MySQL realizada com sucesso.';
        } catch (\Throwable $e) {
            $detalhe = $e->getMessage();
        }

        $this->view('home/diagnostico', [
            'title' => 'Diagnóstico',
            'statusBanco' => $statusBanco,
            'detalhe' => $detalhe,
            'phpVersion' => PHP_VERSION,
        ]);
    }
}
