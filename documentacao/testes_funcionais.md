# Testes Funcionais do WMS

## Objetivo

Este documento registra cenários básicos de validação funcional do WMS.

Os testes abaixo verificam as principais jornadas da aplicação: autenticação, controle de estoque, movimentações, guia de remessa e relatórios.

## Teste 1 — Login com credenciais válidas

*Objetivo:* verificar se um usuário válido consegue acessar o sistema.

*Procedimento:*

1. Abrir a tela de login.
2. Informar um usuário válido.
3. Informar a senha correta.
4. Clicar em Entrar.

*Resultado esperado:* o sistema autentica o usuário e apresenta o painel principal conforme o perfil.

## Teste 2 — Login com credenciais inválidas

*Objetivo:* verificar o tratamento de usuário ou senha incorretos.

*Procedimento:*

1. Abrir a tela de login.
2. Informar usuário ou senha incorretos.
3. Tentar entrar.

*Resultado esperado:* o sistema não permite o acesso e apresenta mensagem informando que o usuário ou a senha são inválidos.

## Teste 3 — Registro de entrada

*Objetivo:* verificar a entrada de material no estoque.

*Procedimento:*

1. Acessar o mapa.
2. Selecionar uma posição.
3. Escolher um produto.
4. Informar uma quantidade de entrada.
5. Confirmar a operação.

*Resultado esperado:* a movimentação é registrada, o saldo é atualizado e a posição passa a refletir a existência de estoque.

## Teste 4 — Registro de saída

*Objetivo:* verificar a retirada de material do estoque.

*Procedimento:*

1. Selecionar uma posição com saldo disponível.
2. Escolher o produto.
3. Informar a quantidade.
4. Selecionar a escola de destino.
5. Confirmar a saída.

*Resultado esperado:* o sistema reduz o saldo, registra a movimentação, o usuário responsável e o destino.

## Teste 5 — Tentativa de saída sem saldo suficiente

*Objetivo:* verificar a validação de estoque.

*Procedimento:*

1. Selecionar um produto com saldo inferior à quantidade solicitada.
2. Informar uma quantidade maior que o saldo disponível.
3. Tentar confirmar a saída.

*Resultado esperado:* a operação não deve deixar o estoque negativo.

## Teste 6 — Geração de Guia de Remessa

*Objetivo:* verificar a geração do documento após uma saída.

*Procedimento:*

1. Registrar uma saída válida.
2. Acessar a guia vinculada à movimentação.

*Resultado esperado:* o sistema apresenta um PDF contendo número da guia, data e hora, origem, destino, material, quantidade e responsável.

## Teste 7 — Histórico de movimentações

*Objetivo:* verificar a rastreabilidade.

*Procedimento:*

1. Acessar a tela de Movimentações.
2. Conferir os registros recentes.

*Resultado esperado:* entradas e saídas aparecem com data, produto, posição, quantidade, usuário e demais informações relacionadas.

## Teste 8 — Filtros de relatório

*Objetivo:* verificar os filtros da área de relatórios.

*Procedimento:*

1. Acessar Relatórios com o perfil Gestor.
2. Definir um período.
3. Aplicar um ou mais filtros, como tipo, escola, produto ou posição.
4. Aplicar os filtros.

*Resultado esperado:* a listagem apresenta somente as movimentações compatíveis com os critérios escolhidos.

## Teste 9 — Exportação do relatório

*Objetivo:* verificar a saída dos dados de auditoria.

*Procedimento:*

1. Acessar Relatórios.
2. Aplicar os filtros desejados.
3. Utilizar a opção de exportação CSV ou impressão/PDF.

*Resultado esperado:* o sistema prepara os dados conforme os registros filtrados.

## Conclusão

Os cenários acima cobrem as principais funções previstas no MVP e ajudam a verificar se o fluxo operacional está coerente com a proposta do sistema.
