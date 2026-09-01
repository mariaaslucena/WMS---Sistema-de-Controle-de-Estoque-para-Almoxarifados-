<?php
declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

final class Relatorio extends BaseModel
{
    public function movimentacoes(array $filtros): array
    {
        [$where, $params] = $this->montarWhere($filtros);

        $sql = "SELECT
                    m.id,
                    m.tipo,
                    m.quantidade,
                    m.observacao,
                    m.criado_em,
                    p.id AS produto_id,
                    p.nome AS produto_nome,
                    p.unidade_medida,
                    pos.id AS posicao_id,
                    pos.rua,
                    pos.numero,
                    e.id AS escola_id,
                    e.nome AS escola_nome,
                    e.codigo AS escola_codigo,
                    u.id AS usuario_id,
                    u.nome AS usuario_nome,
                    u.login AS usuario_login,
                    r.numero_guia
                FROM movimentacoes m
                INNER JOIN produtos p ON p.id = m.produto_id
                INNER JOIN posicoes pos ON pos.id = m.posicao_id
                INNER JOIN usuarios u ON u.id = m.usuario_id
                LEFT JOIN escolas e ON e.id = m.escola_id
                LEFT JOIN remessas r ON r.movimentacao_id = m.id
                {$where}
                ORDER BY m.criado_em DESC, m.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function indicadores(array $filtros): array
    {
        [$where, $params] = $this->montarWhere($filtros);

        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN m.tipo = 'ENTRADA' THEN 1 ELSE 0 END) AS entradas,
                    SUM(CASE WHEN m.tipo = 'SAIDA' THEN 1 ELSE 0 END) AS saidas,
                    COUNT(DISTINCT CASE WHEN m.tipo = 'SAIDA' THEN m.escola_id ELSE NULL END) AS escolas_atendidas
                FROM movimentacoes m
                INNER JOIN produtos p ON p.id = m.produto_id
                INNER JOIN posicoes pos ON pos.id = m.posicao_id
                INNER JOIN usuarios u ON u.id = m.usuario_id
                LEFT JOIN escolas e ON e.id = m.escola_id
                {$where}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetch() ?: [];

        return [
            'total' => (int) ($dados['total'] ?? 0),
            'entradas' => (int) ($dados['entradas'] ?? 0),
            'saidas' => (int) ($dados['saidas'] ?? 0),
            'escolas_atendidas' => (int) ($dados['escolas_atendidas'] ?? 0),
        ];
    }

    public function resumoProdutos(array $filtros): array
    {
        [$where, $params] = $this->montarWhere($filtros);

        $sql = "SELECT
                    p.id,
                    p.nome AS produto_nome,
                    p.unidade_medida,
                    SUM(CASE WHEN m.tipo = 'ENTRADA' THEN m.quantidade ELSE 0 END) AS entradas_quantidade,
                    SUM(CASE WHEN m.tipo = 'SAIDA' THEN m.quantidade ELSE 0 END) AS saidas_quantidade,
                    COUNT(*) AS movimentacoes
                FROM movimentacoes m
                INNER JOIN produtos p ON p.id = m.produto_id
                INNER JOIN posicoes pos ON pos.id = m.posicao_id
                INNER JOIN usuarios u ON u.id = m.usuario_id
                LEFT JOIN escolas e ON e.id = m.escola_id
                {$where}
                GROUP BY p.id, p.nome, p.unidade_medida
                ORDER BY p.nome";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function opcoes(): array
    {
        return [
            'escolas' => $this->db->query(
                "SELECT id, nome, codigo FROM escolas WHERE ativo = 1 ORDER BY nome"
            )->fetchAll(),
            'produtos' => $this->db->query(
                "SELECT id, nome, unidade_medida FROM produtos WHERE ativo = 1 ORDER BY nome"
            )->fetchAll(),
            'usuarios' => $this->db->query(
                "SELECT id, nome, login, perfil FROM usuarios WHERE ativo = 1 ORDER BY nome"
            )->fetchAll(),
        ];
    }

    private function montarWhere(array $filtros): array
    {
        $condicoes = [];
        $params = [];

        $dataInicial = trim((string) ($filtros['data_inicial'] ?? ''));
        if ($dataInicial !== '') {
            $condicoes[] = 'm.criado_em >= :data_inicial';
            $params['data_inicial'] = $dataInicial . ' 00:00:00';
        }

        $dataFinal = trim((string) ($filtros['data_final'] ?? ''));
        if ($dataFinal !== '') {
            $fim = new DateTimeImmutable($dataFinal);
            $condicoes[] = 'm.criado_em < :data_final_exclusiva';
            $params['data_final_exclusiva'] = $fim->modify('+1 day')->format('Y-m-d') . ' 00:00:00';
        }

        $tipo = strtoupper(trim((string) ($filtros['tipo'] ?? '')));
        if (in_array($tipo, ['ENTRADA', 'SAIDA'], true)) {
            $condicoes[] = 'm.tipo = :tipo';
            $params['tipo'] = $tipo;
        }

        $escolaId = (int) ($filtros['escola_id'] ?? 0);
        if ($escolaId > 0) {
            $condicoes[] = 'm.escola_id = :escola_id';
            $params['escola_id'] = $escolaId;
        }

        $produtoId = (int) ($filtros['produto_id'] ?? 0);
        if ($produtoId > 0) {
            $condicoes[] = 'm.produto_id = :produto_id';
            $params['produto_id'] = $produtoId;
        }

        $usuarioId = (int) ($filtros['usuario_id'] ?? 0);
        if ($usuarioId > 0) {
            $condicoes[] = 'm.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }

        $rua = strtoupper(trim((string) ($filtros['rua'] ?? '')));
        if (in_array($rua, ['A', 'B', 'C', 'D', 'E', 'F', 'G'], true)) {
            $condicoes[] = 'pos.rua = :rua';
            $params['rua'] = $rua;
        }

        $posicao = (int) ($filtros['posicao'] ?? 0);
        if ($posicao >= 1 && $posicao <= 40) {
            $condicoes[] = 'pos.numero = :posicao';
            $params['posicao'] = $posicao;
        }

        return [
            $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '',
            $params,
        ];
    }
}
