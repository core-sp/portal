# Aplicação

---

- [Visão Geral](#visao-geral)
- [Perfil](#perfil)
- [Chamados](#chamados)
- [Usuários](#usuarios)
- [Páginas](#paginas)
- [Notícias](#noticias)
- [Cursos](#cursos)
- [Balcão de Oportunidades](#balcao-de-oportunidades)
- [Agendamentos](#agendamentos)

<a name="visao-geral"></a>
## Visão Geral

<p class="pb-3">Uma vez logado, a seção de administrador do Portal CORE-SP oferece uma grande gama de funcionalidades.</p>
<img src="{{ asset('img/admin_pcsp.png') }}" />
<p class="pt-2">Confira-as abaixo:</p>

<a name="perfil"></a>
## Perfil

Localizada no menu superior, permite visualizar informações relativas ao perfil de usuário, assim como alterar a senha se necessário.

<a name="chamados"></a>
## Chamados

Localizada no menu superior, a abertura de chamados pode ser feita a qualquer momento e por qualquer usuário. Seu intuito é de solicitar novas funcionalidades no Portal para o CTI.

<a name="usuarios"></a>
## Usuários

Além de listar os usuários, é possível criar, editar e deletar usuários, além de configurar detalhadamente as permissões de cada perfil de usuário, de acordo com cada funcionalidade da ferramenta.

<blockquote class="alert is-warning">
    <p>Antes de alterar as permissões de um perfil, lembre-se que isto irá influenciar diretamente na utilização da ferramenta.</p>
</blockquote>

<a name="paginas"></a>
## Páginas

<p class="pb-3">Além de listar as páginas, é possível criar, editar e deletar páginas.</p>
<p class="pb-3">Caso nenhuma categoria seja atribuída à página, sua estrutura ficará assim: <code>core-sp.org.br/&lt;titulo&gt;</code></p>
<p>Em caso, de atribuição de categoria, ficará assim: <code>core-sp.org.br/&lt;categoria&gt;/&lt;titulo&gt;</code></p>

<a name="noticias"></a>
## Notícias

<p>Aqueles com as devidas permissões podem criar, editar e deletar notícias.</p>

<a name="cursos"></a>
## Cursos

Além de criar, editar e deletar cursos, a plataforma oferece todo o suporte para o gerenciamento de inscritos em cada curso, possibilitando a inclusão ilimitada via admin, e monitoramento de vagas já preenchidas.

<a name="balcao-de-oportunidades"></a>
## Balcão de Oportunidades

<p class="pb-3">Para se trabalhar com o Balcão de Oportunidades, é necessário cadastrar tanto as empresas quanto as oportunidades em momentos separados.</p>
<p class="pb-3">As oportunidades são inteiramente dependentes das empresas, e só podem ser criadas se vinculadas à alguma empresa.</p>
<p>Por padrão, uma oportunidade dura 90 dias, e exige alterações manuais para mudança de status.</p>

<a name="agendamentos"></a>
## Agendamentos / Bloqueios
<p class="pb-3">Todo o gerenciamento de agendamentos / bloqueios é realizado dentro do painel de administrador do Portal CORE-SP. Regras de utilização:</p>
<ul>
    <li style="line-height:2;">O número de atendimentos por horário para cada seccional é sempre relativo à quantidade de usuários com o perfil "Atendimento" daquela seccional - 1. e.g: Caso existam 4 atendentes em uma determinada seccional será possível agendar 3 atendimentos por horário (4 - 1 = 3). <i>Quando houver apenas um atendente, este número será fixado em 1.</i></li>
    <li style="line-height:2;">O atendimento será vinculado ao atendente que pressionar o botão "Confirmar presença" na tela de atendimentos no painel de Administrador.</li>
    <li style="line-height:2;">Os bloqueios poderão ser realizados à qualquer momento, e deverão especificar as datas exatas de início e términos. Para aplicar bloqueios indefinidamente, basta deixar os campos de data em branco.</li>
</ul>