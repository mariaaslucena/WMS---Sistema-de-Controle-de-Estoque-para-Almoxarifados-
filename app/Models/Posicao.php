<?php
declare(strict_types=1);
namespace App\Models;

final class Posicao extends BaseModel
{
    protected string $table = 'posicoes';

    public function mapaPorRua(string $rua): array
    {
        $sql = "SELECT p.id, p.rua, p.numero,
                       COALESCE(s.quantidade_total, 0) AS quantidade_total,
                       COALESCE(s.qtd_produtos, 0) AS qtd_produtos
                FROM posicoes p
                LEFT JOIN (
                    SELECT posicao_id,
                           SUM(quantidade) AS quantidade_total,
                           COUNT(*) AS qtd_produtos
                    FROM estoque
                    WHERE quantidade > 0
                    GROUP BY posicao_id
                ) s ON s.posicao_id = p.id
                WHERE p.ativo = 1 AND p.rua = :rua
                ORDER BY p.numero";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['rua' => $rua]);
        return $stmt->fetchAll();
    }

    public function itensPorRua(string $rua): array
    {
        $sql = "SELECT e.posicao_id, e.produto_id, pr.nome AS produto_nome,
                       pr.unidade_medida, e.quantidade
                FROM estoque e
                INNER JOIN posicoes p ON p.id = e.posicao_id
                INNER JOIN produtos pr ON pr.id = e.produto_id
                WHERE p.ativo = 1
                  AND p.rua = :rua
                  AND e.quantidade > 0
                ORDER BY p.numero, pr.nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['rua' => $rua]);
        return $stmt->fetchAll();
    }

    public function resumoGeral(): array
    {
        $sql = "SELECT COUNT(*) AS total,
                       SUM(CASE WHEN COALESCE(s.quantidade_total, 0) > 0 THEN 1 ELSE 0 END) AS ocupadas
                FROM posicoes p
                LEFT JOIN (
                    SELECT posicao_id, SUM(quantidade) AS quantidade_total
                    FROM estoque
                    WHERE quantidade > 0
                    GROUP BY posicao_id
                ) s ON s.posicao_id = p.id
                WHERE p.ativo = 1";
        $row = $this->db->query($sql)->fetch() ?: ['total' => 0, 'ocupadas' => 0];
        $total = (int) ($row['total'] ?? 0);
        $ocupadas = (int) ($row['ocupadas'] ?? 0);
        return ['total' => $total, 'ocupadas' => $ocupadas, 'livres' => max(0, $total - $ocupadas)];
    }
}
