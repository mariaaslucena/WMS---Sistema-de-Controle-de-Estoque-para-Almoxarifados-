<?php
declare(strict_types=1);
namespace App\Models;

use PDO;

final class Remessa extends BaseModel
{
    protected string $table = 'remessas';

    public function garantirParaMovimentacao(int $movimentacaoId): ?array
    {
        if ($movimentacaoId <= 0) { return null; }

        $stmt = $this->db->prepare("SELECT id, criado_em FROM movimentacoes WHERE id = :id AND tipo = 'SAIDA' LIMIT 1");
        $stmt->execute(['id' => $movimentacaoId]);
        $mov = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$mov) { return null; }

        $ano = date('Y', strtotime((string) $mov['criado_em']));
        $numero = sprintf('GR-%s-%06d', $ano, $movimentacaoId);
        $ins = $this->db->prepare(
            'INSERT IGNORE INTO remessas (movimentacao_id, numero_guia) VALUES (:movimentacao_id, :numero_guia)'
        );
        $ins->execute(['movimentacao_id' => $movimentacaoId, 'numero_guia' => $numero]);

        return $this->buscarDetalhes($movimentacaoId);
    }

    public function buscarDetalhes(int $movimentacaoId): ?array
    {
        $sql = "SELECT r.id AS remessa_id, r.numero_guia, r.criado_em AS remessa_criada_em,
                       m.id AS movimentacao_id, m.quantidade, m.observacao, m.criado_em,
                       p.nome AS produto_nome, p.unidade_medida,
                       pos.rua, pos.numero,
                       e.nome AS escola_nome, e.codigo AS escola_codigo,
                       u.nome AS usuario_nome, u.login AS usuario_login
                FROM movimentacoes m
                INNER JOIN remessas r ON r.movimentacao_id = m.id
                INNER JOIN produtos p ON p.id = m.produto_id
                INNER JOIN posicoes pos ON pos.id = m.posicao_id
                INNER JOIN escolas e ON e.id = m.escola_id
                INNER JOIN usuarios u ON u.id = m.usuario_id
                WHERE m.id = :id AND m.tipo = 'SAIDA'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $movimentacaoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
