# Segurança e suporte

## Versão suportada para homologação

`0.3.1-beta.1`, Moodle 4.5.4 / PHP 8.1. Esta é uma pré-release; não há certificação de compatibilidade com Moodle 5.x, outras versões PHP ou instalações distribuídas.

## Bloqueio confirmado de produção: metadados de tentativas entre grupos

A função nativa mod_quiz_get_user_attempts, disponibilizada pelo serviço do plugin, verifica mod/quiz:viewreports, mas não a autorização de revisão por grupo em questionários com grupos separados no Moodle 4.5.4. Um professor com token pode receber metadados e notas de tentativas de outro grupo. A API de alunos também pode retornar identificadores de outros grupos quando a separação existe somente na atividade.

O problema foi reproduzido no laboratório: a chamada de alternativas detalhadas recusou a tentativa fora do grupo com `noreviewattempt`, enquanto a função nativa de metadados ainda retornou uma tentativa. Os dados usados eram fictícios. A solução pendente é substituir a função nativa exposta por uma função que aplique autorização por tentativa/grupo, atualizar o cliente e testar os dois caminhos.

**Não usar esta pré-release em produção nem com dados reais.** A publicação é exclusivamente para homologação e revisão; não é uma versão estável. Isso continua sendo um bloqueio mesmo com os demais testes aprovados.

## Verificações realizadas em 13/11/2026

- Revisão do fluxo de autorização, permissões de criação e consulta de alternativas, e pacote de distribuição.
- Uso único do código de autorização protegido por lock; verificador errado invalida o código e repetição concorrente não deve emitir outro token.
- A leitura de alternativas verifica `quiz_attempt::is_review_allowed()`, incluindo grupos separados.
- Testes reais no laboratório para autorização, revogação, rejeição de aluno e confirmação sem chave de sessão, criação com imagens, recusa de professor sem edição e rollback de XML inválido.
- 208 testes unitários do lado Aferium aprovados.

## Limites da homologação

Ainda não foram validados: atualização a partir de todas as versões antigas, carga sustentada, caches/locks compartilhados em múltiplos servidores, a matriz Moodle/PHP e a implantação real de cada instituição. A revisão de código e os testes focais não substituem um teste da instalação de produção.

Antes de liberar: valide HTTPS, backup, permissões por curso e grupo, autorização institucional para dados de alunos e conectividade do servidor Aferium. No Aferium, confirme a chave de criptografia e as migrations; não substitua uma chave que já protege tokens sem planejar a migração.

## Relatar uma vulnerabilidade

Use **Security → Report a vulnerability** neste repositório, quando disponível. Não publique tokens, códigos de autorização, dados de alunos nem detalhes exploráveis em issues públicas. O mantenedor é `@JoaoRajiv`.

Não há processamento de pull requests externos sem decisão do proprietário, nem concessão automática de acesso de escrita.
