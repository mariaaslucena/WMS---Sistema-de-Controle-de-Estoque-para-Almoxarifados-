<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Usuario;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login', [
            'title' => 'Entrar - WMS',
            'erro' => null,
            'loginInformado' => '',
        ]);
    }

    public function login(): void
    {
        $login = trim((string) ($_POST['login'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');

        if ($login === '' || $senha === '') {
            $this->renderLoginComErro('Preencha o usuário e a senha.', $login);
            return;
        }

        try {
            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->buscarPorLogin($login);
        } catch (\Throwable $e) {
            error_log('[WMS] Falha ao consultar usuário no login: ' . $e->getMessage());
            $this->renderLoginComErro('Não foi possível validar o acesso agora. Tente novamente.', $login);
            return;
        }

        $credenciaisValidas = $usuario
            && (bool) $usuario['ativo']
            && password_verify($senha, (string) $usuario['senha_hash']);

        if (!$credenciaisValidas) {
            $this->renderLoginComErro('Usuário ou senha incorretos.', $login);
            return;
        }

        Auth::login($usuario);
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }

    private function renderLoginComErro(string $mensagem, string $loginInformado): void
    {
        http_response_code(401);

        $this->view('auth/login', [
            'title' => 'Entrar - WMS',
            'erro' => $mensagem,
            'loginInformado' => $loginInformado,
        ]);
    }
}
