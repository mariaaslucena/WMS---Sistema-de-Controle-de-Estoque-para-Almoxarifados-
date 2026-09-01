<?php
declare(strict_types=1);
namespace App\Core;
abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        if (!is_file($viewFile)) { throw new \RuntimeException("View não encontrada: {$view}"); }
        $flash = $_SESSION['wms_flash'] ?? null;
        unset($_SESSION['wms_flash']);
        $data['flash'] = $data['flash'] ?? $flash;
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../Views/layouts/header.php';
        require $viewFile;
        require __DIR__ . '/../Views/layouts/footer.php';
    }
    protected function redirect(string $route, array $query = []): never
    {
        $params = array_merge(['route' => $route], $query);
        header('Location: ?' . http_build_query($params), true, 302);
        exit;
    }
    protected function flash(string $tipo, string $mensagem): void
    {
        $_SESSION['wms_flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
    }
}
