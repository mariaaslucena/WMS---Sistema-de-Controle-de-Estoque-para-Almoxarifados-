<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

final class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $this->view('dashboard/index', [
            'title' => 'Painel - WMS',
            'usuario' => Auth::user(),
        ]);
    }
}
