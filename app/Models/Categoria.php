<?php
declare(strict_types=1);
namespace App\Models;
final class Categoria extends BaseModel
{
    public function listar(): array
    {
        return $this->db->query('SELECT id, nome, ativo, criado_em FROM categorias ORDER BY nome')->fetchAll();
    }
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nome, ativo FROM categorias WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
    public function nomeEmUso(string $nome, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT id FROM categorias WHERE LOWER(nome) = LOWER(:nome)';
        $params = ['nome' => $nome];
        if ($ignorarId !== null) { $sql .= ' AND id <> :id'; $params['id'] = $ignorarId; }
        $sql .= ' LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }
    public function criar(string $nome): void
    {
        $stmt = $this->db->prepare('INSERT INTO categorias (nome) VALUES (:nome)');
        $stmt->execute(['nome' => $nome]);
    }
    public function atualizar(int $id, string $nome): void
    {
        $stmt = $this->db->prepare('UPDATE categorias SET nome = :nome WHERE id = :id');
        $stmt->execute(['nome' => $nome, 'id' => $id]);
    }
    public function alterarStatus(int $id, bool $ativo): void
    {
        $stmt = $this->db->prepare('UPDATE categorias SET ativo = :ativo WHERE id = :id');
        $stmt->execute(['ativo' => $ativo ? 1 : 0, 'id' => $id]);
    }
}
