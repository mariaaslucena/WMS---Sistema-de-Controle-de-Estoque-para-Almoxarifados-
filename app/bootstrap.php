<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

date_default_timezone_set('America/Sao_Paulo');

if (session_status() !== PHP_SESSION_ACTIVE) {
    $httpsAtivo = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_name('WMSSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $httpsAtivo,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
