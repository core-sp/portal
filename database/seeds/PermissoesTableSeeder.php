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
                'nome' => 'Usuário'
            ], [
                'controller' => 'RegionalController',
                'metodo' => 'edit',
                'nome' => 'Regional'
            ],[
                'controller' => 'PaginaController',
                'metodo' => 'index',
                'nome' => 'Página'
            ], [
                'controller' => 'PaginaController',
                'metodo' => 'create',
                'nome' => 'Página'
            ], [
                'controller' => 'PaginaController',
                'metodo' => 'edit',
                'nome' => 'Página'
            ], [
                'controller' => 'PaginaController',
                'metodo' => 'destroy',
                'nome' => 'Página'
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'index',
                'nome' => 'Notícia'
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'create',
                'nome' => 'Notícia'
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'edit',
                'nome' => 'Notícia'
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'destroy',
                'nome' => 'Notícia'
            ], [
                'controller' => 'CursoController',
                'metodo' => 'index',
                'nome' => 'Curso'
            ], [
                'controller' => 'CursoController',
                'metodo' => 'create',
                'nome' => 'Curso'
            ], [
                'controller' => 'CursoController',
                'metodo' => 'edit',
                'nome' => 'Curso'
            ], [
                'controller' => 'CursoController',
                'metodo' => 'destroy',
                'nome' => 'Curso'
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'index',
                'nome' => 'Curso (Inscrito)'
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'create',
                'nome' => 'Curso (Inscrito)'
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'edit',
                'nome' => 'Curso (Inscrito)'
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'destroy',
                'nome' => 'Curso (Inscrito)'
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'index',
                'nome' => 'B. de Oportunidades (Empresas)'
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'create',
                'nome' => 'B. de Oportunidades (Empresas)'
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'edit',
                'nome' => 'B. de Oportunidades (Empresas)'
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'destroy',
                'nome' => 'B. de Oportunidades (Empresas)'
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'index',
                'nome' => 'B. de Oportunidades (Oportunidades)'
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'create',
                'nome' => 'B. de Oportunidades (Oportunidades)'
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'edit',
                'nome' => 'B. de Oportunidades (Oportunidades)'
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'destroy',
                'nome' => 'B. de Oportunidades (Oportunidades)'
            ], [
                'controller' => 'AgendamentoController',
                'metodo' => 'index',
                'nome' => 'Agendamento / Sala de Reunião - Agendados'
            ], [
                'controller' => 'AgendamentoController',
                'metodo' => 'edit',
                'nome' => 'Agendamento / Sala de Reunião - Agendados'
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'index',
                'nome' => 'Agendamentos Bloqueio / Sala de Reunião Bloqueio'
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'create',
                'nome' => 'Agendamentos Bloqueio / Sala de Reunião Bloqueio'
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'edit',
                'nome' => 'Agendamentos Bloqueio / Sala de Reunião Bloqueio'
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'destroy',
                'nome' => 'Agendamentos Bloqueio / Sala de Reunião Bloqueio'
            ], [
                'controller' => 'LicitacaoController',
                'metodo' => 'index',
                'nome' => 'Licitação'
            ], [
                'controller' => 'LicitacaoController',
                'metodo' => 'create',
                'nome' => 'Licitação'
            ], [
                'controller' => 'LicitacaoController',
                'metodo' => 'edit',
                'nome' => 'Licitação'
            ], [
                'controller' => 'LicitacaoController',
                'metodo' => 'destroy',
                'nome' => 'Licitação'
            ], [
                'controller' => 'ConcursoController',
                'metodo' => 'index',
                'nome' => 'Concurso'
            ], [
                'controller' => 'ConcursoController',
                'metodo' => 'create',
                'nome' => 'Concurso'
            ], [
                'controller' => 'ConcursoController',
                'metodo' => 'edit',
                'nome' => 'Concurso'
            ], [
                'controller' => 'ConcursoController',
                'metodo' => 'destroy',
                'nome' => 'Concurso'
            ], [
                'controller' => 'NewsletterController',
                'metodo' => 'index',
                'nome' => 'Newsletter'
            ], [
                'controller' => 'HomeImagemController',
                'metodo' => 'edit',
                'nome' => 'Imagem (Home)'
            ], [
                'controller' => 'PostsController',
                'metodo' => 'index',
                'nome' => 'Post'
            ], [
                'controller' => 'PostsController',
                'metodo' => 'edit',
                'nome' => 'Post'
            ], [
                'controller' => 'RepresentanteEnderecoController',
                'metodo' => 'index',
                'nome' => 'Representante - Endereço'
            ], [
                'controller' => 'RepresentanteEnderecoController',
                'metodo' => 'show',
                'nome' => 'Representante - Endereço'
            ], [
                'controller' => 'RepresentanteController',
                'metodo' => 'index',
                'nome' => 'Representante - Cadastrados'
            ], [
                'controller' => 'PostsController',
                'metodo' => 'create',
                'nome' => 'Post'
            ], [
                'controller' => 'PostsController',
                'metodo' => 'destroy',
                'nome' => 'Post'
            ], [
                'controller' => 'FiscalizacaoController',
                'metodo' => 'index',
                'nome' => 'Fiscalização'
            ], [
                'controller' => 'FiscalizacaoController',
                'metodo' => 'create',
                'nome' => 'Fiscalização'
            ], [
                'controller' => 'FiscalizacaoController',
                'metodo' => 'edit',
                'nome' => 'Fiscalização'
            ], [
                'controller' => 'CompromissoController',
                'metodo' => 'index',
                'nome' => 'Compromisso'
            ], [
                'controller' => 'CompromissoController',
                'metodo' => 'create',
                'nome' => 'Compromisso'
            ], [
                'controller' => 'CompromissoController',
                'metodo' => 'edit',
                'nome' => 'Compromisso'
            ], [
                'controller' => 'CompromissoController',
                'metodo' => 'destroy',
                'nome' => 'Compromisso'
            ], [
                'controller' => 'AvisoController',
                'metodo' => 'index',
                'nome' => 'Aviso'
            ], [
                'controller' => 'AvisoController',
                'metodo' => 'edit',
                'nome' => 'Aviso'
            ], [
                'controller' => 'SolicitaCedulaController',
                'metodo' => 'index',
                'nome' => 'Representante - Cédula'
            ], [
                'controller' => 'SolicitaCedulaController',
                'metodo' => 'show',
                'nome' => 'Representante - Cédula'
            ], [
                'controller' => 'PlantaoJuridicoController',
                'metodo' => 'index',
                'nome' => 'Plantão Jurídico'
            ], [
                'controller' => 'PlantaoJuridicoController',
                'metodo' => 'edit',
                'nome' => 'Plantão Jurídico'
            ], [
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'index',
                'nome' => 'Plantão Jurídico Bloqueio'
            ], [
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'create',
                'nome' => 'Plantão Jurídico Bloqueio'
            ], [
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'edit',
                'nome' => 'Plantão Jurídico Bloqueio'
            ], [
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'destroy',
                'nome' => 'Plantão Jurídico Bloqueio'
            ], [
                'controller' => 'SalaReuniaoController',
                'metodo' => 'index',
                'nome' => 'Sala Reunião'
            ], [
                'controller' => 'SalaReuniaoController',
                'metodo' => 'edit',
                'nome' => 'Sala Reunião'
            ], [
                'controller' => 'SuspensaoExcecaoController',
                'metodo' => 'index',
                'nome' => 'Sala Reunião - Suspensos / Exceções'
            ], [
                'controller' => 'SuspensaoExcecaoController',
                'metodo' => 'create',
                'nome' => 'Sala Reunião - Suspensos / Exceções'
            ], [
                'controller' => 'SuspensaoExcecaoController',
                'metodo' => 'edit',
                'nome' => 'Sala Reunião - Suspensos / Exceções'
            ], [
                'controller' => 'AgendamentoController',
                'metodo' => 'create',
                'nome' => 'Agendamento / Sala de Reunião - Agendados'
            ], [
                'controller' => 'CartaServicos',
                'metodo' => 'index',
                'nome' => 'Carta de Serviços'
            ], [
                'controller' => 'CartaServicos',
                'metodo' => 'edit',
                'nome' => 'Carta de Serviços'
            ],
        ];

        foreach($array as $campos)
            Permissao::updateOrInsert(
                ['controller' => $campos['controller'], 'metodo' => $campos['metodo']],
                ['nome' => $campos['nome']]
            );
    }
}
