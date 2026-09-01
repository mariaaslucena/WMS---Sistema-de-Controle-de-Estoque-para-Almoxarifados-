# Manual de Uso do WMS

## Objetivo

Este documento apresenta, de forma resumida, como utilizar as principais funções do WMS — Sistema de Controle de Estoque para Almoxarifados.

## Acesso ao sistema

O sistema possui autenticação por usuário e senha e trabalha com dois perfis:

- **Gestor**: possui acesso às funções operacionais e também aos cadastros e relatórios.
- **Operador**: possui acesso às rotinas operacionais de estoque.

Credenciais de demonstração:

- Gestor: `gestor`
- Operador: `operador`
- Senha de demonstração: `Wms@2026`

## Painel principal

Após o login, o usuário acessa o painel do sistema. A partir dele é possível navegar entre:

- Mapa de Estoque;
- Movimentações;
- Categorias;
- Produtos;
- Escolas;
- Relatórios, quando permitido pelo perfil.

## Mapa de Estoque

O mapa representa fisicamente o almoxarifado.

A estrutura possui:

- Ruas de **A até G**;
- Posições de **1 até 40** em cada rua;
- Total de **280 posições**.

As posições são apresentadas visualmente conforme sua situação no estoque.

O usuário pode selecionar uma rua e clicar em uma posição para consultar seus dados.

## Entrada de materiais

Na entrada, o usuário informa o produto, a posição onde ele será armazenado e a quantidade recebida.

Ao confirmar a operação, o sistema:

- registra a movimentação;
- atualiza o saldo do produto naquela posição;
- atualiza a situação visual do mapa;
- registra o usuário responsável.

## Saída de materiais

Na saída, o usuário informa:

- produto;
- posição de origem;
- quantidade;
- escola de destino;
- observação, quando necessário.

Antes de concluir a retirada, o sistema valida a disponibilidade do estoque.

Após a confirmação, o saldo é atualizado e a movimentação fica registrada no histórico.

## Guia de Remessa

As saídas podem gerar uma Guia de Remessa em PDF.

A guia apresenta informações como:

- número da guia;
- data e hora;
- posição de origem;
- unidade escolar de destino;
- material;
- quantidade;
- responsável pela movimentação;
- observação.

## Histórico de movimentações

A tela de movimentações apresenta os registros recentes de entradas e saídas, incluindo:

- data;
- tipo de movimentação;
- produto;
- posição;
- quantidade;
- destino;
- usuário;
- guia de remessa, quando houver.

## Relatórios

O perfil Gestor possui acesso aos relatórios e à auditoria das movimentações.

É possível utilizar filtros por:

- período;
- tipo de movimentação;
- escola;
- produto;
- usuário;
- rua;
- posição.

Os resultados também podem ser exportados em CSV ou preparados para impressão/PDF.
