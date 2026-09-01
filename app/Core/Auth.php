<?php
declare(strict_types=1);
namespace App\Core;
final class Auth
{
    private const SESSION_KEY = 'wms_usuario';
    public static function login(array $usuario): void
    {
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = [
            'id' => (int) $usuario['id'],
            'nome' => (string) $usuario['nome'],
            'login' => (string) $usuario['login'],
            'perfil' => (string) $usuario['perfil'],
        ];
    }
    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
    }
    public static function check(): bool { return isset($_SESSION[self::SESSION_KEY]['id']); }
    public static function user(): ?array { return self::check() ? $_SESSION[self::SESSION_KEY] : null; }
    public static function isGestor(): bool { return (self::user()['perfil'] ?? null) === 'GESTOR'; }
    public static function requireLogin(): void
    {
        if (!self::check()) { header('Location: ?route=/login', true, 302); exit; }
    }
    public static function requireGestor(): void
    {
        self::requireLogin();
        if (!self::isGestor()) {
            http_response_code(403);
            echo '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><title>Acesso negado</title>';
            echo '<body style="font-family:Arial;padding:40px"><h1>Acesso negado</h1>';
            echo '<p>Este cadastro está disponível apenas para o perfil GESTOR.</p>';
            echo '<p><a href="?route=/dashboard">Voltar ao painel</a></p></body></html>';
            exit;
        }
    }
    private function __construct() {}
}
