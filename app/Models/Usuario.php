<?php
declare(strict_types=1);

namespace App\Models;

final class Usuario extends BaseModel
{
    protected string $table = 'usuarios';

    public function buscarPorLogin(string $login): ?array
    {
        $sql = 'SELECT id, nome, login, senha_hash, perfil, ativo
                FROM usuarios
                WHERE login = :login
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['login' => $login]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }
}
