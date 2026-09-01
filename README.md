# WMS — Sistema de Controle de Estoque para Almoxarifados

Projeto Integrador do curso de **Tecnologia em Análise e Desenvolvimento de Sistemas — Centro Universitário Senac**.

O WMS é uma aplicação web desenvolvida para apoiar o controle de estoque e a distribuição de materiais de um almoxarifado central escolar. O projeto substitui controles manuais e planilhas estáticas por uma solução com localização visual, rastreabilidade de movimentações e relatórios gerenciais.

## Demonstração

- **Sistema:** https://wms-almoxarifado.infinityfreeapp.com/
- **Landing Page:** https://mariaaslucena.github.io/WMS---Sistema-de-Controle-de-Estoque-para-Almoxarifados-/

### Perfis de demonstração

| Perfil | Usuário | Senha |
| --- | --- | --- |
| Gestor | `gestor` | `Wms@2026` |
| Operador | `operador` | `Wms@2026` |

> As credenciais acima existem apenas para a demonstração acadêmica publicada.

## Funcionalidades do MVP

- autenticação e controle de acesso por perfil;
- perfis **Gestor** e **Operador**;
- cadastro de categorias;
- cadastro de produtos;
- cadastro de unidades escolares;
- Matriz Visual de Estoque com **Ruas A–G** e **Posições 1–40**;
- identificação visual de posições livres e ocupadas;
- registro de entradas de materiais;
- registro de saídas com unidade escolar de destino;
- validação de saldo antes da retirada;
- atualização automática do estoque e da posição;
- histórico de movimentações e rastreabilidade por usuário;
- geração de **Guia de Remessa em PDF**;
- relatórios por período, tipo, escola, produto, usuário, rua e posição;
- exportação de relatórios em **CSV**;
- versão de impressão/relatório para PDF.

## Tecnologias

| Camada | Tecnologia |
| --- | --- |
| Backend | PHP 8 |
| Arquitetura | MVC + Programação Orientada a Objetos |
| Banco de dados | MySQL |
| Acesso ao banco | PDO |
| Frontend | HTML5, CSS3 e JavaScript |
| Hospedagem da aplicação | InfinityFree |
| Versionamento | Git e GitHub |
| Landing Page | GitHub Pages |

## Arquitetura

O código foi organizado em MVC para separar responsabilidades:

```text
app/
├── Controllers/   # Fluxo das requisições e regras de aplicação
├── Core/          # Router, autenticação, conexão e infraestrutura
├── Models/        # Persistência e regras ligadas aos dados
└── Views/         # Telas renderizadas pela aplicação

assets/
├── css/
└── js/

config/
└── database.example.php

database/
├── schema.sql
├── demo_users.sql
└── modelo_fisico.md

index.php
```

### Fluxo simplificado

```text
Navegador
   │
   ▼
index.php / Router
   │
   ▼
Controllers
   │
   ├────────► Views (HTML/CSS/JS)
   │
   ▼
Models
   │
   ▼
PDO
   │
   ▼
MySQL
```

## Modelo físico do banco

O banco é composto pelas tabelas:

- `usuarios`
- `categorias`
- `produtos`
- `escolas`
- `posicoes`
- `estoque`
- `movimentacoes`
- `remessas`

O DDL completo está em [`database/schema.sql`](database/schema.sql).

O diagrama e a descrição dos relacionamentos estão em [`database/modelo_fisico.md`](database/modelo_fisico.md).

## Instalação

### 1. Criar o banco

Crie um banco MySQL e importe:

```text
database/schema.sql
```

O script cria toda a estrutura e as **280 posições** do mapa.

### 2. Usuários de demonstração (opcional)

Para criar os dois acessos de teste, importe:

```text
database/demo_users.sql
```

### 3. Configurar a conexão

Copie:

```text
config/database.example.php
```

para:

```text
config/database.php
```

Preencha o arquivo criado com os dados do seu MySQL:

```php
<?php
declare(strict_types=1);

return [
    'host' => 'SEU_HOST_MYSQL',
    'port' => '3306',
    'dbname' => 'SEU_BANCO',
    'user' => 'SEU_USUARIO',
    'password' => 'SUA_SENHA',
];
```

O arquivo `config/database.php` está no `.gitignore` e **não deve ser versionado**.

### 4. Publicar a aplicação

O `index.php` deve ficar na raiz pública do servidor web, junto das pastas `app`, `assets` e `config`.

Não é necessário framework PHP externo ou gerenciador de dependências para executar o MVP atual.

## Perfis

### Operador

O perfil Operador é destinado às atividades operacionais:

- visualizar o mapa;
- consultar posições;
- registrar entradas;
- registrar saídas;
- consultar histórico de movimentações.

### Gestor

Além das funções operacionais, o Gestor pode:

- administrar categorias;
- administrar produtos;
- administrar escolas;
- consultar relatórios e auditoria;
- exportar dados;
- acessar guias de remessa.

## Segurança do repositório

Este repositório **não deve conter**:

- `config/database.php`;
- senha real do MySQL;
- arquivos temporários de carga;
- dumps com dados pessoais ou credenciais privadas;
- logs da hospedagem.

O `.gitignore` já protege o arquivo local de conexão.

## Equipe — Grupo 03

- Erick Felipe dos Santos de Melo
- Leonardo Sheldow dos Santos Brito
- Maria Adélia Sena de Lucena
- Nicole Alves Silva
- Thiago Gualberto de Oliveira

## Contexto acadêmico

Projeto desenvolvido como prova de conceito funcional para a disciplina **Projeto Integrador: Análise de Soluções Integradas para Organizações**.

A aplicação implementa as jornadas centrais definidas na etapa de planejamento: operação de estoque por matriz visual, expedição com guia de remessa e auditoria de movimentações.
