# Aferium para Moodle

Plugin oficial `local_aferium` para conectar o Moodle ao [Aferium](https://aferium.com.br), enviar avaliações e consultar resultados.

**Versão: 0.3.1-beta.1. Pré-lançamento para homologação.** Validado no Moodle 4.5.4 com PHP 8.1. Compatibilidade com outras versões e ambientes com vários nós ainda não foi homologada. Não confundir esta publicação com uma aprovação para uso irrestrito em produção.

## Bloqueio de produção

**Não instale em produção nem use dados reais de alunos nesta pré-release.** A função padrão mod_quiz_get_user_attempts, ainda exposta no serviço, permite consultar metadados e notas de tentativas de outro grupo quando o professor tem permissão de relatório. A correção das alternativas detalhadas já está aplicada; o caminho de metadados ainda precisa ser substituído por uma função com autorização por grupo. Use somente homologação com dados fictícios.

## Download

[Baixar aferium-moodle-plugin.zip](https://github.com/JoaoRajiv/moodle-local_aferium/releases/download/v0.3.1-beta.1/aferium-moodle-plugin.zip)

[Ver versões e verificações SHA-256](https://github.com/JoaoRajiv/moodle-local_aferium/releases)

Use o arquivo `aferium-moodle-plugin.zip` da release para instalar. O ZIP de código-fonte gerado automaticamente pelo GitHub não tem a mesma estrutura de instalação.

## Instalação e atualização

1. Faça backup do banco e dos arquivos do Moodle e teste primeiro em homologação.
2. Como administrador, abra **Administração do site → Plugins → Instalar plugins** e envie o ZIP da release.
3. Confirme a atualização do banco. Na instalação inicial, o plugin habilita web services e REST, registra o serviço Aferium e cria o papel de acesso REST.
4. No Aferium, abra **Avaliações → Moodle**, informe o endereço HTTPS público da instituição e autorize com a conta de professor.

O endereço de retorno padrão é `https://aferium.com.br`. Somente o administrador pode alterá-lo em **Plugins → Plugins locais → Aferium**. Não configure endereços de terceiros.

## Recursos e permissões

- Autorização com código temporário de uso único e PKCE; o token é trocado entre servidores, sem passar pelo navegador.
- Criação de questionários a partir de Moodle XML, incluindo imagens, horários, tentativas e embaralhamento. O questionário é criado oculto por padrão.
- Consulta de cursos, alunos, tentativas, notas e alternativas marcadas. A leitura de alternativas já respeita grupos separados; a consulta padrão de metadados ainda tem o bloqueio descrito acima.
- A criação exige `mod/quiz:addinstance`, `moodle/course:manageactivities` e `moodle/question:add` no curso. Relatórios exigem `mod/quiz:viewreports` e acesso à tentativa.
- Reconectar substitui o token anterior. Desconectar revoga o token. A desinstalação remove o serviço, seus tokens e o papel; não desativa globalmente REST/web services, pois outras integrações podem usá-los.

A instituição deve autorizar o compartilhamento de dados de alunos com o Aferium. O plugin declara os dados transmitidos na API de privacidade do Moodle.

## Configuração no servidor Aferium

O servidor Aferium precisa de `MOODLE_TOKEN_ENCRYPTION_KEY` (32 bytes aleatórios em base64) para criptografar tokens. Essa chave não é instalada no Moodle, não acompanha o ZIP e nunca deve ser publicada. `MOODLE_ALLOW_LOCAL_HTTP` deve permanecer desativado em produção.

A aplicação Aferium também precisa das migrations `0167_moodle_online_assessments` e `0169_moodle_attempt_choices`. Publicar este plugin não aplica migrations nem publica a aplicação.

## Estado da versão

**Veredito da revisão: não pronto para produção.**

- Testes unitários da integração Aferium: 208 aprovados.
- Laboratório Moodle 4.5.4: autorização, revogação, proteção de sessão, troca concorrente de código, criação com imagens e rollback de importação inválida.
- Correções desta versão: autorização de leitura respeita grupos separados; consumo do código de autorização usa a API de locks do Moodle.
- Sincronização agendada de resultados depende da aplicação Aferium e ainda não está liberada. Use a sincronização manual.
- Consulte [SECURITY.md](SECURITY.md) antes de instalar em produção.

## Manutenção

Repositório oficial: [JoaoRajiv/moodle-local_aferium](https://github.com/JoaoRajiv/moodle-local_aferium). Somente o proprietário pode publicar alterações oficiais. Propostas externas não são incorporadas automaticamente. A licença GPL permite cópias e versões derivadas; elas não se tornam versões oficiais do Aferium.

Licença: GNU GPL versão 3 ou posterior. Veja [LICENSE](LICENSE).
