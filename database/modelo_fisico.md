# Modelo físico do banco de dados

O arquivo [`schema.sql`](schema.sql) contém o DDL executável do banco MySQL.

```mermaid
erDiagram
    USUARIOS {
        INT id PK
        VARCHAR nome
        VARCHAR login UK
        VARCHAR senha_hash
        ENUM perfil
        TINYINT ativo
        TIMESTAMP criado_em
        TIMESTAMP atualizado_em
    }

    CATEGORIAS {
        INT id PK
        VARCHAR nome UK
        TINYINT ativo
        TIMESTAMP criado_em
    }

    PRODUTOS {
        INT id PK
        INT categoria_id FK
        VARCHAR nome
        VARCHAR descricao
        VARCHAR unidade_medida
        DECIMAL estoque_minimo
        TINYINT ativo
        TIMESTAMP criado_em
        TIMESTAMP atualizado_em
    }

    ESCOLAS {
        INT id PK
        VARCHAR nome
        VARCHAR codigo UK
        TINYINT ativo
        TIMESTAMP criado_em
    }

    POSICOES {
        INT id PK
        CHAR rua
        TINYINT numero
        TINYINT ativo
        TIMESTAMP criado_em
    }

    ESTOQUE {
        BIGINT id PK
        INT produto_id FK
        INT posicao_id FK
        DECIMAL quantidade
        TIMESTAMP atualizado_em
    }

    MOVIMENTACOES {
        BIGINT id PK
        ENUM tipo
        INT produto_id FK
        INT posicao_id FK
        INT escola_id FK
        INT usuario_id FK
        DECIMAL quantidade
        VARCHAR observacao
        TIMESTAMP criado_em
    }

    REMESSAS {
        BIGINT id PK
        BIGINT movimentacao_id FK
        VARCHAR numero_guia UK
        VARCHAR arquivo_pdf
        TIMESTAMP criado_em
    }

    CATEGORIAS ||--o{ PRODUTOS : classifica
    PRODUTOS ||--o{ ESTOQUE : possui
    POSICOES ||--o{ ESTOQUE : armazena
    PRODUTOS ||--o{ MOVIMENTACOES : movimenta
    POSICOES ||--o{ MOVIMENTACOES : ocorre_em
    ESCOLAS ||--o{ MOVIMENTACOES : destino
    USUARIOS ||--o{ MOVIMENTACOES : registra
    MOVIMENTACOES ||--o| REMESSAS : gera
```

## Regras físicas relevantes

- As posições são únicas pelo par `rua + numero`.
- O mapa possui as Ruas **A a G** e as posições **1 a 40**, totalizando **280 posições**.
- O estoque não aceita quantidade negativa.
- Cada combinação `produto + posição` é única na tabela de estoque.
- Uma movimentação de saída pode estar vinculada a uma escola de destino.
- Uma movimentação pode gerar no máximo uma guia de remessa.
