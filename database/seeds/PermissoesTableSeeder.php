<?php

use Illuminate\Database\Seeder;
use App\Permissao;

class PermissoesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array = [
            [
                'controller' => 'UserController',
                'metodo' => 'index',
                'nome' => 'Usuário',
                'grupo_menu' => null,
            ], [
                'controller' => 'RegionalController',
                'metodo' => 'edit',
                'nome' => 'Regional',
                'grupo_menu' => null,
            ],[
                'controller' => 'PaginaController',
                'metodo' => 'index',
                'nome' => 'Página',
                'grupo_menu' => Permissao::G_CONTEUDO,
            ], [
                'controller' => 'PaginaController',
                'metodo' => 'create',
                'nome' => 'Página',
                'grupo_menu' => null,
            ], [
                'controller' => 'PaginaController',
                'metodo' => 'edit',
                'nome' => 'Página',
                'grupo_menu' => null,
            ], [
                'controller' => 'PaginaController',
                'metodo' => 'destroy',
                'nome' => 'Página',
                'grupo_menu' => null,
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'index',
                'nome' => 'Notícia',
                'grupo_menu' => Permissao::G_CONTEUDO,
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'create',
                'nome' => 'Notícia',
                'grupo_menu' => null,
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'edit',
                'nome' => 'Notícia',
                'grupo_menu' => null,
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'destroy',
                'nome' => 'Notícia',
                'grupo_menu' => null,
            ], [
                'controller' => 'CursoController',
                'metodo' => 'index',
                'nome' => 'Curso',
                'grupo_menu' => Permissao::G_CONTEUDO,
            ], [
                'controller' => 'CursoController',
                'metodo' => 'create',
                'nome' => 'Curso',
                'grupo_menu' => null,
            ], [
                'controller' => 'CursoController',
                'metodo' => 'edit',
                'nome' => 'Curso',
                'grupo_menu' => null,
            ], [
                'controller' => 'CursoController',
                'metodo' => 'destroy',
                'nome' => 'Curso',
                'grupo_menu' => null,
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'index',
                'nome' => 'Curso (Inscrito)',
                'grupo_menu' => null,
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'create',
                'nome' => 'Curso (Inscrito)',
                'grupo_menu' => null,
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'edit',
                'nome' => 'Curso (Inscrito)',
                'grupo_menu' => null,
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'destroy',
                'nome' => 'Curso (Inscrito)',
                'grupo_menu' => null,
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'index',
                'nome' => 'B. de Oportunidades (Empresas)',
                'grupo_menu' => Permissao::G_CONTEUDO,
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'create',
                'nome' => 'B. de Oportunidades (Empresas)',
                'grupo_menu' => null,
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'edit',
                'nome' => 'B. de Oportunidades (Empresas)',
                'grupo_menu' => null,
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'destroy',
                'nome' => 'B. de Oportunidades (Empresas)',
                'grupo_menu' => null,
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'index',
                'nome' => 'B. de Oportunidades (Oportunidades)',
                'grupo_menu' => Permissao::G_CONTEUDO,
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'create',
                'nome' => 'B. de Oportunidades (Oportunidades)',
                'grupo_menu' => null,
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'edit',
                'nome' => 'B. de Oportunidades (Oportunidades)',
                'grupo_menu' => null,
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'destroy',
                'nome' => 'B. de Oportunidades (Oportunidades)',
                'grupo_menu' => null,
            ], [
                'controller' => 'AgendamentoController',
                'metodo' => 'index',
                'nome' => 'Agendamento / Sala de Reunião - Agendados',
                'grupo_menu' => Permissao::G_ATENDIMENTO,
            ], [
                'controller' => 'AgendamentoController',
                'metodo' => 'edit',
                'nome' => 'Agendamento / Sala de Reunião - Agendados',
                'grupo_menu' => null,
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'index',
                'nome' => 'Agendamentos Bloqueio / Sala de Reunião Bloqueio',
                'grupo_menu' => Permissao::G_ATENDIMENTO,
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'create',
                'nome' => 'Agendamentos Bloqueio / Sala de Reunião Bloqueio',
                'grupo_menu' => null,
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'edit',
                'nome' => 'Agendamentos Bloqueio / Sala de Reunião Bloqueio',
                'grupo_menu' => null,
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'destroy',
                'nome' => 'Agendamentos Bloqueio / Sala de Reunião Bloqueio',
                'grupo_menu' => null,
            ], [
                'controller' => 'LicitacaoController',
                'metodo' => 'index',
                'nome' => 'Licitação',
                'grupo_menu' => Permissao::G_JURIDICO,
            ], [
                'controller' => 'LicitacaoController',
                'metodo' => 'create',
                'nome' => 'Licitação',
                'grupo_menu' => null,
            ], [
                'controller' => 'LicitacaoController',
                'metodo' => 'edit',
                'nome' => 'Licitação',
                'grupo_menu' => null,
            ], [
                'controller' => 'LicitacaoController',
                'metodo' => 'destroy',
                'nome' => 'Licitação',
                'grupo_menu' => null,
            ], [
                'controller' => 'ConcursoController',
                'metodo' => 'index',
                'nome' => 'Concurso',
                'grupo_menu' => Permissao::G_JURIDICO,
            ], [
                'controller' => 'ConcursoController',
                'metodo' => 'create',
                'nome' => 'Concurso',
                'grupo_menu' => null,
            ], [
                'controller' => 'ConcursoController',
                'metodo' => 'edit',
                'nome' => 'Concurso',
                'grupo_menu' => null,
            ], [
                'controller' => 'ConcursoController',
                'metodo' => 'destroy',
                'nome' => 'Concurso',
                'grupo_menu' => null,
            ], [
                'controller' => 'NewsletterController',
                'metodo' => 'index',
                'nome' => 'Newsletter',
                'grupo_menu' => null,
            ], [
                'controller' => 'HomeImagemController',
                'metodo' => 'edit',
                'nome' => 'Imagem (Home)',
                'grupo_menu' => Permissao::G_CONTEUDO,
            ], [
                'controller' => 'PostsController',
                'metodo' => 'index',
                'nome' => 'Post',
                'grupo_menu' => Permissao::G_CONTEUDO,
            ], [
                'controller' => 'PostsController',
                'metodo' => 'edit',
                'nome' => 'Post',
                'grupo_menu' => null,
            ], [
                'controller' => 'RepresentanteEnderecoController',
                'metodo' => 'index',
                'nome' => 'Representante - Endereço',
                'grupo_menu' => Permissao::G_ATENDIMENTO,
            ], [
                'controller' => 'RepresentanteEnderecoController',
                'metodo' => 'show',
                'nome' => 'Representante - Endereço',
                'grupo_menu' => null,
            ], [
                'controller' => 'RepresentanteController',
                'metodo' => 'index',
                'nome' => 'Representante - Cadastrados',
                'grupo_menu' => Permissao::G_ATENDIMENTO,
            ], [
                'controller' => 'PostsController',
                'metodo' => 'create',
                'nome' => 'Post',
                'grupo_menu' => null,
            ], [
                'controller' => 'PostsController',
                'metodo' => 'destroy',
                'nome' => 'Post',
                'grupo_menu' => null,
            ], [
                'controller' => 'FiscalizacaoController',
                'metodo' => 'index',
                'nome' => 'Fiscalização',
                'grupo_menu' => Permissao::G_FISCAL,
            ], [
                'controller' => 'FiscalizacaoController',
                'metodo' => 'create',
                'nome' => 'Fiscalização',
                'grupo_menu' => null,
            ], [
                'controller' => 'FiscalizacaoController',
                'metodo' => 'edit',
                'nome' => 'Fiscalização',
                'grupo_menu' => null,
            ], [
                'controller' => 'CompromissoController',
                'metodo' => 'index',
                'nome' => 'Compromisso',
                'grupo_menu' => Permissao::G_CONTEUDO,
            ], [
                'controller' => 'CompromissoController',
                'metodo' => 'create',
                'nome' => 'Compromisso',
                'grupo_menu' => null,
            ], [
                'controller' => 'CompromissoController',
                'metodo' => 'edit',
                'nome' => 'Compromisso',
                'grupo_menu' => null,
            ], [
                'controller' => 'CompromissoController',
                'metodo' => 'destroy',
                'nome' => 'Compromisso',
                'grupo_menu' => null,
            ], [
                'controller' => 'AvisoController',
                'metodo' => 'index',
                'nome' => 'Aviso',
                'grupo_menu' => Permissao::G_CONTEUDO,
            ], [
                'controller' => 'AvisoController',
                'metodo' => 'edit',
                'nome' => 'Aviso',
                'grupo_menu' => null,
            ], [
                'controller' => 'SolicitaCedulaController',
                'metodo' => 'index',
                'nome' => 'Representante - Cédula',
                'grupo_menu' => Permissao::G_ATENDIMENTO,
            ], [
                'controller' => 'SolicitaCedulaController',
                'metodo' => 'show',
                'nome' => 'Representante - Cédula',
                'grupo_menu' => null,
            ], [
                'controller' => 'PlantaoJuridicoController',
                'metodo' => 'index',
                'nome' => 'Plantão Jurídico',
                'grupo_menu' => Permissao::G_JURIDICO,
            ], [
                'controller' => 'PlantaoJuridicoController',
                'metodo' => 'edit',
                'nome' => 'Plantão Jurídico',
                'grupo_menu' => null,
            ], [
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'index',
                'nome' => 'Plantão Jurídico Bloqueio',
                'grupo_menu' => Permissao::G_JURIDICO,
            ], [
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'create',
                'nome' => 'Plantão Jurídico Bloqueio',
                'grupo_menu' => null,
            ], [
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'edit',
                'nome' => 'Plantão Jurídico Bloqueio',
                'grupo_menu' => null,
            ], [
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'destroy',
                'nome' => 'Plantão Jurídico Bloqueio',
                'grupo_menu' => null,
            ], [
                'controller' => 'SalaReuniaoController',
                'metodo' => 'index',
                'nome' => 'Sala Reunião',
                'grupo_menu' => Permissao::G_ATENDIMENTO,
            ], [
                'controller' => 'SalaReuniaoController',
                'metodo' => 'edit',
                'nome' => 'Sala Reunião',
                'grupo_menu' => null,
            ], [
                'controller' => 'SuspensaoExcecaoController',
                'metodo' => 'index',
                'nome' => 'Sala Reunião - Suspensos / Exceções',
                'grupo_menu' => Permissao::G_ATENDIMENTO,
            ], [
                'controller' => 'SuspensaoExcecaoController',
                'metodo' => 'create',
                'nome' => 'Sala Reunião - Suspensos / Exceções',
                'grupo_menu' => null,
            ], [
                'controller' => 'SuspensaoExcecaoController',
                'metodo' => 'edit',
                'nome' => 'Sala Reunião - Suspensos / Exceções',
                'grupo_menu' => null,
            ], [
                'controller' => 'AgendamentoController',
                'metodo' => 'create',
                'nome' => 'Agendamento / Sala de Reunião - Agendados',
                'grupo_menu' => null,
            ], [
                'controller' => 'CartaServicos',
                'metodo' => 'index',
                'nome' => 'Carta de Serviços',
                'grupo_menu' => Permissao::G_CONTEUDO,
            ], [
                'controller' => 'CartaServicos',
                'metodo' => 'edit',
                'nome' => 'Carta de Serviços',
                'grupo_menu' => null,
            ],
        ];

        foreach($array as $campos)
            Permissao::updateOrInsert(
                ['controller' => $campos['controller'], 'metodo' => $campos['metodo']],
                ['nome' => $campos['nome'], 'grupo_menu' => $campos['grupo_menu']]
            );
    }
}
