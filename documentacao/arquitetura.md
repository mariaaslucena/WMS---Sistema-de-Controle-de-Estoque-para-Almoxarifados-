# Arquitetura e Tecnologias do WMS

## Visão geral

O WMS foi desenvolvido como uma aplicação web para controle de estoque de um almoxarifado escolar.

A solução separa a interface, as regras da aplicação e a persistência dos dados por meio de uma organização baseada em **MVC — Model, View, Controller**.

## Tecnologias utilizadas

### Backend

O backend foi desenvolvido em **PHP 8**.

A aplicação utiliza recursos de **Programação Orientada a Objetos (POO)** para organizar classes e responsabilidades.

O acesso ao banco de dados é realizado com **PDO**.

### Frontend

A interface utiliza:

- HTML5;
- CSS3;
- JavaScript.

Essas tecnologias são responsáveis pela apresentação das telas e pelas interações do usuário com o sistema.

### Banco de dados

O sistema utiliza **MySQL** para persistência dos dados.

Entre as principais entidades estão:

- usuários;
- categorias;
- produtos;
- escolas;
- posições;
- estoque;
- movimentações;
- remessas.

O modelo físico e o DDL executável ficam na pasta `database` do projeto.

## Organização em MVC

A estrutura principal do projeto é:

```text
app/
├── Controllers/
├── Core/
├── Models/
└── Views/

assets/
├── css/
└── js/

config/
database/
index.php
```

## Controllers

Os Controllers recebem as requisições do usuário e coordenam as ações da aplicação.

Entre os controladores existentes estão os responsáveis por:

- autenticação;
- painel;
- categorias;
- produtos;
- escolas;
- mapa;
- movimentações;
- relatórios;
- remessas.

## Models

Os Models concentram o acesso aos dados e as regras ligadas às entidades do sistema.

Há modelos para elementos como:

- usuário;
- produto;
- categoria;
- escola;
- posição;
- estoque;
- movimentação;
- relatório;
- remessa.

## Views

As Views são responsáveis pela apresentação das telas ao usuário.

O projeto possui telas para:

- login;
- dashboard;
- cadastros;
- mapa de estoque;
- movimentações;
- relatórios;
- geração e consulta de remessas.

## Core

A pasta `Core` reúne componentes de infraestrutura da aplicação, como:

- autenticação;
- roteamento;
- conexão com o banco;
- classe base dos Controllers;
- geração simples de PDF.

## Fluxo simplificado

O fluxo da aplicação pode ser representado assim:

```text
Navegador
   ↓
index.php / Router
   ↓
Controller
   ↓
Model
   ↓
PDO
   ↓
MySQL
```

Quando necessário, o Controller também direciona a resposta para uma View, que é apresentada no navegador.

## Hospedagem e versionamento

A aplicação funcional foi publicada em ambiente de hospedagem web.

O código-fonte e a documentação são mantidos no **GitHub**.

A Landing Page do projeto está publicada através do **GitHub Pages**.
