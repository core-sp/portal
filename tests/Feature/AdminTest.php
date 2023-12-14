<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_logged_in_user_can_see_the_admin_panel()
    {
        $user = $this->signInAsAdmin();

        $this->get('/admin')->assertOk()->assertSee($user->nome);
    }

    /** @test */
    public function a_logged_in_user_can_see_the_manual()
    {
        $this->signIn();

        $this->get('/admin')->assertOk()
        ->assertSee('<a href="' . route('admin.manual') . '" class="nav-link">Manual');
    }

    /** @test */
    public function a_logged_in_user_can_see_his_info_on_the_admin_panel()
    {
        $user = $this->signInAsAdmin();

        $this->get('/admin/perfil')
            ->assertOk()
            ->assertSee($user->nome)
            ->assertSee($user->username)
            ->assertSee($user->email);
    }

    /** @test */
    public function the_admin_menu_has_correct_links()
    {
        $this->signInAsAdmin();
        $this->assertAuthenticated('web');

        // Ordem do menu
        $this->get('/admin')
            ->assertSee(route('usuarios.lista'))
            ->assertSee('/admin/usuarios/criar')
            ->assertSee(route('perfis.lista'))
            ->assertSee(route('suporte.log.externo.index'))
            ->assertSee(route('suporte.ips.view'))
            ->assertSee(route('chamados.lista'))
            ->assertSee(route('regionais.index'))
            ->assertSee(route('paginas.index'))
            ->assertSee(route('paginas.create'))
            ->assertSee(route('noticias.index'))
            ->assertSee(route('noticias.create'))
            ->assertSee(route('posts.index'))
            ->assertSee(route('posts.create'))
            ->assertSee(route('cursos.index'))
            ->assertSee(route('cursos.create'))
            ->assertSee(route('bdoempresas.lista'))
            ->assertSee(route('bdooportunidades.lista'))
            ->assertSee(route('compromisso.index'))
            ->assertSee(route('compromisso.create'))
            ->assertSee(route('imagens.banner'))
            ->assertSee(route('imagens.itens.home'))
            ->assertSee(route('avisos.index'))
            ->assertSee(route('agendamentos.lista'))
            ->assertSee(route('agendamentobloqueios.lista'))
            ->assertSee('/admin/representantes/buscaGerenti')
            ->assertSee('/admin/representantes')
            ->assertSee('/admin/representante-enderecos')
            ->assertSee(route('solicita-cedula.index'))
            ->assertSee(route('sala.reuniao.index'))
            ->assertSee(route('sala.reuniao.agendados.index'))
            ->assertSee(route('sala.reuniao.bloqueio.lista'))
            ->assertSee(route('sala.reuniao.suspensao.lista'))
            ->assertSee(route('licitacoes.index'))
            ->assertSee(route('licitacoes.create'))
            ->assertSee(route('concursos.index'))
            ->assertSee(route('concursos.create'))
            ->assertSee(route('plantao.juridico.index'))
            ->assertSee(route('plantao.juridico.bloqueios.index'))
            ->assertSee(route('fiscalizacao.index'))
            ->assertSee(route('fiscalizacao.createperiodo'));
    }

    /** @test */
    public function non_authorized_users_cannot_see_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        // Ordem do menu
        $this->get('/admin')
            ->assertDontSee(route('usuarios.lista'))
            ->assertDontSee('/admin/usuarios/criar')
            ->assertDontSee(route('perfis.lista'))
            ->assertDontSee(route('suporte.log.externo.index'))
            ->assertDontSee(route('suporte.ips.view'))
            ->assertDontSee(route('chamados.lista'))
            ->assertDontSee(route('paginas.index'))
            ->assertDontSee(route('paginas.create'))
            ->assertDontSee(route('noticias.index'))
            ->assertDontSee(route('noticias.create'))
            ->assertDontSee(route('posts.index'))
            ->assertDontSee(route('posts.create'))
            ->assertDontSee(route('cursos.index'))
            ->assertDontSee(route('cursos.create'))
            ->assertDontSee(route('bdoempresas.lista'))
            ->assertDontSee(route('bdooportunidades.lista'))
            ->assertDontSee(route('compromisso.index'))
            ->assertDontSee(route('compromisso.create'))
            ->assertDontSee(route('imagens.banner'))
            ->assertDontSee(route('imagens.itens.home'))
            ->assertDontSee(route('avisos.index'))
            ->assertDontSee(route('agendamentos.lista'))
            ->assertDontSee(route('agendamentobloqueios.lista'))
            ->assertDontSee('/admin/representantes/buscaGerenti')
            ->assertDontSee('/admin/representantes')
            ->assertDontSee('/admin/representante-enderecos')
            ->assertDontSee(route('solicita-cedula.index'))
            ->assertDontSee(route('sala.reuniao.index'))
            ->assertDontSee(route('sala.reuniao.agendados.index'))
            ->assertDontSee(route('sala.reuniao.bloqueio.lista'))
            ->assertDontSee(route('sala.reuniao.suspensao.lista'))
            ->assertDontSee(route('licitacoes.index'))
            ->assertDontSee(route('licitacoes.create'))
            ->assertDontSee(route('concursos.index'))
            ->assertDontSee(route('concursos.create'))
            ->assertDontSee(route('plantao.juridico.index'))
            ->assertDontSee(route('plantao.juridico.bloqueios.index'))
            ->assertDontSee(route('fiscalizacao.index'))
            ->assertDontSee(route('fiscalizacao.createperiodo'));
    }

    /** @test */
    public function admin_can_access_horizon()
    {
        $this->signInAsAdmin();
        $this->assertAuthenticated('web');

        $this->get('/horizon')->assertOk();
    }

    /** @test */
    public function non_authorized_users_cannot_access_horizon()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        $this->get('/horizon')->assertForbidden();
    }

    /** @test */
    public function non_authenticated_users_cannot_access_horizon()
    {
        $this->assertGuest();

        $this->get('/horizon')->assertRedirect(route('login'));
    }

    /** @test */
    public function authorized_users_can_view_calls_list_with_same_regional()
    {
        $admin = $this->signInAsAdmin();

        $user = factory('App\User')->create([
            'idperfil' => factory('App\Perfil')->create(['idperfil' => 8]),
            'idregional' => $admin->idregional
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'tiposervico' => 'Outros para Ambas',
            'protocolo' => 'AGE-ABCD',
            'hora' => '10:00',
            'status' => 'Compareceu',
            'idregional' => $admin->idregional,
            'idusuario' => $user->idusuario
        ]);

        $this->get('/admin')
        ->assertSee('<th>Atendente</th>')
        ->assertSee('<th>Atendimentos</th>')
        ->assertSee('<td>'.$user->nome.'</td>')
        ->assertSee('<td>1</td>');
    }

    /** @test */
    public function non_authorized_users_cannot_view_calls_list_with_same_regional()
    {
        $non_admin = $this->signIn();

        $user = factory('App\User')->create([
            'idregional' => $non_admin->idregional
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'tiposervico' => 'Outros para Ambas',
            'protocolo' => 'AGE-ABCD',
            'hora' => '10:00',
            'status' => 'Compareceu',
            'idregional' => $non_admin->idregional,
            'idusuario' => $user->idusuario
        ]);

        $this->get('/admin')
        ->assertDontSee('<th>Atendente</th>')
        ->assertDontSee('<th>Atendimentos</th>')
        ->assertDontSee('<td>'.$user->nome.'</td>')
        ->assertDontSee('<td>1</td>');
    }

    /** @test */
    public function authorized_users_can_view_empty_calls_list_with_different_regional()
    {
        $admin = $this->signInAsAdmin();

        $user = factory('App\User')->create([
            'idperfil' => factory('App\Perfil')->create(['idperfil' => 8]),
        ]);

        $agendamento = factory('App\Agendamento')->create([
            'tiposervico' => 'Outros para Ambas',
            'protocolo' => 'AGE-ABCD',
            'hora' => '10:00',
            'status' => 'Compareceu',
            'idregional' => $user->idregional,
            'idusuario' => $user->idusuario
        ]);

        $this->get('/admin')
        ->assertSee('<th>Atendente</th>')
        ->assertSee('<th>Atendimentos</th>')
        ->assertDontSee('<td>'.$user->nome.'</td>')
        ->assertDontSee('<td>1</td>');
    }

    /** @test */
    public function non_authorized_users_cannot_view_agendamentos_pendentes_alerts()
    {
        $non_admin = $this->signIn();

        $agendamento = factory('App\Agendamento')->create([
            'tiposervico' => 'Outros para Ambas',
            'protocolo' => 'AGE-ABCD',
            'dia' => Carbon::today()->subDay()->format('Y-m-d'),
            'hora' => '10:00',
            'status' => null,
            'idregional' => $non_admin->idregional,
            'idusuario' => null
        ]);

        $this->get('/admin')
        ->assertOk()
        ->assertDontSee('Existe <strong>1</strong> atendimento pendente de validação!');
    }

    /** @test */
    public function authorized_users_can_view_agendamentos_pendentes_alerts()
    {
        $admin = $this->signInAsAdmin();

        $agendamento = factory('App\Agendamento')->create([
            'tiposervico' => 'Outros para Ambas',
            'protocolo' => 'AGE-ABCD',
            'dia' => Carbon::today()->subDay()->format('Y-m-d'),
            'hora' => '10:00',
            'status' => null,
            'idregional' => $admin->idregional,
            'idusuario' => null
        ]);

        $this->get('/admin')
        ->assertOk()
        ->assertSee('Existe <strong>1</strong> atendimento pendente de validação!');
    }

    /** @test */
    public function view_agendamentos_pendentes_alerts()
    {
        $admin = $this->signInAsAdmin();

        factory('App\Agendamento', 2)->create([
            'tiposervico' => 'Outros para Ambas',
            'protocolo' => 'AGE-ABCD',
            'dia' => Carbon::today()->subDay()->format('Y-m-d'),
            'hora' => '10:00',
            'status' => null,
            'idregional' => $admin->idregional,
            'idusuario' => null
        ]);

        $this->get('/admin')
        ->assertSee('Existem <strong>2</strong> atendimentos pendentes de validação!');
    }

    /** @test */
    public function authorized_users_can_view_password_info()
    {
        $this->signInAsAdmin();

        $this->get('/admin')
        ->assertSeeText('Para alterar sua senha, clique em seu nome de usuário no menu da esquerda e depois selecione "Alterar Senha";');
    }
}
