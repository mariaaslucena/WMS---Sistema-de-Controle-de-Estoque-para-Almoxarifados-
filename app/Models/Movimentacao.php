<?php
declare(strict_types=1);
namespace App\Models;

use DomainException;
use PDO;
use Throwable;

final class Movimentacao extends BaseModel
{
    protected string $table = 'movimentacoes';

    public function registrarEntrada(int $produtoId, int $posicaoId, float $quantidade, int $usuarioId, ?string $observacao = null): int
    {
        if ($produtoId <= 0 || $posicaoId <= 0 || $usuarioId <= 0 || $quantidade <= 0) {
            throw new DomainException('Informe produto, posição e uma quantidade válida.');
        }

        $this->db->beginTransaction();
        try {
            $produto = $this->buscarProdutoAtivo($produtoId);
            $posicao = $this->buscarPosicaoAtiva($posicaoId);
            if (!$produto || !$posicao) {
                throw new DomainException('Produto ou posição não está disponível.');
            }

            $stmt = $this->db->prepare(
                'SELECT id, quantidade FROM estoque WHERE produto_id = :produto_id AND posicao_id = :posicao_id FOR UPDATE'
            );
            $stmt->execute(['produto_id' => $produtoId, 'posicao_id' => $posicaoId]);
            $estoque = $stmt->fetch();

            if ($estoque) {
                $novoSaldo = (float) $estoque['quantidade'] + $quantidade;
                $upd = $this->db->prepare('UPDATE estoque SET quantidade = :quantidade WHERE id = :id');
                $upd->execute(['quantidade' => $novoSaldo, 'id' => (int) $estoque['id']]);
            } else {
                $ins = $this->db->prepare('INSERT INTO estoque (produto_id, posicao_id, quantidade) VALUES (:produto_id, :posicao_id, :quantidade)');
                $ins->execute(['produto_id' => $produtoId, 'posicao_id' => $posicaoId, 'quantidade' => $quantidade]);
            }

            $movimentacaoId = $this->inserirMovimentacao('ENTRADA', $produtoId, $posicaoId, null, $usuarioId, $quantidade, $observacao);
            $this->db->commit();
            return $movimentacaoId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $e;
        }
    }

    public function registrarSaida(int $produtoId, int $posicaoId, int $escolaId, float $quantidade, int $usuarioId, ?string $observacao = null): int
    {
        if ($produtoId <= 0 || $posicaoId <= 0 || $escolaId <= 0 || $usuarioId <= 0 || $quantidade <= 0) {
            throw new DomainException('Informe produto, escola de destino e uma quantidade válida.');
        }

        $this->db->beginTransaction();
        try {
            if (!$this->buscarEscolaAtiva($escolaId)) {
                throw new DomainException('A escola de destino não está disponível.');
            }

            $stmt = $this->db->prepare(
                'SELECT e.id, e.quantidade, p.nome, p.unidade_medida
                 FROM estoque e
                 INNER JOIN produtos p ON p.id = e.produto_id
                 WHERE e.produto_id = :produto_id AND e.posicao_id = :posicao_id
                 FOR UPDATE'
            );
            $stmt->execute(['produto_id' => $produtoId, 'posicao_id' => $posicaoId]);
            $estoque = $stmt->fetch();

            if (!$estoque || (float) $estoque['quantidade'] <= 0) {
                throw new DomainException('Não há saldo deste produto na posição selecionada.');
            }

            $saldoAtual = (float) $estoque['quantidade'];
            if ($quantidade > $saldoAtual + 0.000001) {
                throw new DomainException('A quantidade solicitada é maior que o saldo disponível.');
            }

            $novoSaldo = max(0, $saldoAtual - $quantidade);
            $upd = $this->db->prepare('UPDATE estoque SET quantidade = :quantidade WHERE id = :id');
            $upd->execute(['quantidade' => $novoSaldo, 'id' => (int) $estoque['id']]);

            $movimentacaoId = $this->inserirMovimentacao('SAIDA', $produtoId, $posicaoId, $escolaId, $usuarioId, $quantidade, $observacao);
            $this->criarRemessa($movimentacaoId);
            $this->db->commit();
            return $movimentacaoId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $e;
        }
    }

    public function listarRecentes(int $limite = 100): array
    {
        $limite = max(1, min(250, $limite));
        $sql = "SELECT m.id, m.tipo, m.quantidade, m.observacao, m.criado_em,
                       p.nome AS produto_nome, p.unidade_medida,
                       pos.rua, pos.numero,
                       e.nome AS escola_nome,
                       u.nome AS usuario_nome,
                       r.numero_guia
                FROM movimentacoes m
                INNER JOIN produtos p ON p.id = m.produto_id
                INNER JOIN posicoes pos ON pos.id = m.posicao_id
                INNER JOIN usuarios u ON u.id = m.usuario_id
                LEFT JOIN escolas e ON e.id = m.escola_id
                LEFT JOIN remessas r ON r.movimentacao_id = m.id
                ORDER BY m.criado_em DESC, m.id DESC
                LIMIT {$limite}";
        return $this->db->query($sql)->fetchAll();
    }

    private function inserirMovimentacao(string $tipo, int $produtoId, int $posicaoId, ?int $escolaId, int $usuarioId, float $quantidade, ?string $observacao): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO movimentacoes (tipo, produto_id, posicao_id, escola_id, usuario_id, quantidade, observacao)
             VALUES (:tipo, :produto_id, :posicao_id, :escola_id, :usuario_id, :quantidade, :observacao)'
        );
        $stmt->execute([
            'tipo' => $tipo,
            'produto_id' => $produtoId,
            'posicao_id' => $posicaoId,
            'escola_id' => $escolaId,
            'usuario_id' => $usuarioId,
            'quantidade' => $quantidade,
            'observacao' => $this->normalizarObservacao($observacao),
        ]);
        return (int) $this->db->lastInsertId();
    }

    private function criarRemessa(int $movimentacaoId): void
    {
        $numero = sprintf('GR-%s-%06d', date('Y'), $movimentacaoId);
        $stmt = $this->db->prepare(
            'INSERT INTO remessas (movimentacao_id, numero_guia) VALUES (:movimentacao_id, :numero_guia)'
        );
        $stmt->execute(['movimentacao_id' => $movimentacaoId, 'numero_guia' => $numero]);
    }

    private function buscarProdutoAtivo(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id FROM produtos WHERE id = :id AND ativo = 1 LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function buscarPosicaoAtiva(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id FROM posicoes WHERE id = :id AND ativo = 1 LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function buscarEscolaAtiva(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id FROM escolas WHERE id = :id AND ativo = 1 LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function normalizarObservacao(?string $observacao): ?string
    {
        $observacao = trim((string) $observacao);
        if ($observacao === '') { return null; }
        return mb_substr($observacao, 0, 500);
    }
}
