<?php
declare(strict_types=1);
namespace App\Models;

final class Escola extends BaseModel
{
    protected string $table = 'escolas';

    public function listar(): array
    {
        return $this->db->query('SELECT id, nome, codigo, ativo, criado_em FROM escolas ORDER BY nome')->fetchAll();
    }

    public function listarAtivas(): array
    {
        return $this->db->query('SELECT id, nome, codigo FROM escolas WHERE ativo = 1 ORDER BY nome')->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nome, codigo, ativo FROM escolas WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function codigoEmUso(string $codigo, ?int $ignorarId = null): bool
    {
        if ($codigo === '') { return false; }
        $sql = 'SELECT id FROM escolas WHERE codigo = :codigo';
        $params = ['codigo' => $codigo];
        if ($ignorarId !== null) { $sql .= ' AND id <> :id'; $params['id'] = $ignorarId; }
        $sql .= ' LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public function criar(string $nome, ?string $codigo): void
    {
        $stmt = $this->db->prepare('INSERT INTO escolas (nome, codigo) VALUES (:nome, :codigo)');
        $stmt->execute(['nome' => $nome, 'codigo' => $codigo]);
    }

    public function atualizar(int $id, string $nome, ?string $codigo): void
    {
        $stmt = $this->db->prepare('UPDATE escolas SET nome = :nome, codigo = :codigo WHERE id = :id');
        $stmt->execute(['nome' => $nome, 'codigo' => $codigo, 'id' => $id]);
    }

    public function alterarStatus(int $id, bool $ativo): void
    {
        $stmt = $this->db->prepare('UPDATE escolas SET ativo = :ativo WHERE id = :id');
        $stmt->execute(['ativo' => $ativo ? 1 : 0, 'id' => $id]);
    }
}
