<?php
declare(strict_types=1);
namespace App\Models;

final class Produto extends BaseModel
{
    protected string $table = 'produtos';

    public function listar(): array
    {
        $sql = 'SELECT p.id, p.nome, p.descricao, p.unidade_medida, p.estoque_minimo, p.ativo,
                       c.nome AS categoria_nome
                FROM produtos p LEFT JOIN categorias c ON c.id = p.categoria_id ORDER BY p.nome';
        return $this->db->query($sql)->fetchAll();
    }

    public function listarAtivos(): array
    {
        return $this->db->query(
            'SELECT id, nome, unidade_medida FROM produtos WHERE ativo = 1 ORDER BY nome'
        )->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, categoria_id, nome, descricao, unidade_medida, estoque_minimo, ativo FROM produtos WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function criar(array $dados): void
    {
        $stmt = $this->db->prepare('INSERT INTO produtos (categoria_id, nome, descricao, unidade_medida, estoque_minimo) VALUES (:categoria_id, :nome, :descricao, :unidade_medida, :estoque_minimo)');
        $stmt->execute($dados);
    }

    public function atualizar(int $id, array $dados): void
    {
        $dados['id'] = $id;
        $stmt = $this->db->prepare('UPDATE produtos SET categoria_id = :categoria_id, nome = :nome, descricao = :descricao, unidade_medida = :unidade_medida, estoque_minimo = :estoque_minimo WHERE id = :id');
        $stmt->execute($dados);
    }

    public function alterarStatus(int $id, bool $ativo): void
    {
        $stmt = $this->db->prepare('UPDATE produtos SET ativo = :ativo WHERE id = :id');
        $stmt->execute(['ativo' => $ativo ? 1 : 0, 'id' => $id]);
    }
}
