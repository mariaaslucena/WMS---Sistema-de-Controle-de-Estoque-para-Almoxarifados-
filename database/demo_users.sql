-- WMS — usuários de demonstração
-- Uso opcional em ambiente acadêmico/demonstração.
-- Senha dos dois usuários: Wms@2026

INSERT INTO usuarios (nome, login, senha_hash, perfil, ativo)
VALUES
('Gestor de Demonstração', 'gestor', '$2y$12$ERLWS1ImF4Su7loK2aeS2.E6WeiOiho59eVZyo/0yI94gIMyns2vm', 'GESTOR', 1),
('Operador de Demonstração', 'operador', '$2y$12$ERLWS1ImF4Su7loK2aeS2.E6WeiOiho59eVZyo/0yI94gIMyns2vm', 'OPERADOR', 1)
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    senha_hash = VALUES(senha_hash),
    perfil = VALUES(perfil),
    ativo = 1;
