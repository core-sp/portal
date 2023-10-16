<?php

namespace Tests\Feature;

use App\Curso;
use App\CursoInscrito;
use App\Permissao;
use DateInterval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Mail\CursoInscritoMailGuest;
use Illuminate\Support\Facades\Mail;

class CursoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.index'))->assertRedirect(route('login'));
        $this->get(route('cursos.busca'))->assertRedirect(route('login'));
        $this->get(route('cursos.edit', $curso->idcurso))->assertRedirect(route('login'));
        $this->get(route('cursos.create'))->assertRedirect(route('login'));
        $this->get(route('cursos.lixeira'))->assertRedirect(route('login'));
        $this->get(route('cursos.restore', $curso->idcurso))->assertRedirect(route('login'));
        $this->post(route('cursos.store'))->assertRedirect(route('login'));
        $this->patch(route('cursos.update', $curso->idcurso))->assertRedirect(route('login'));
        $this->delete(route('cursos.destroy', $curso->idcurso))->assertRedirect(route('login'));

        $this->get(route('inscritos.index', $curso->idcurso))->assertRedirect(route('login'));
        $this->get('/admin/cursos/inscritos/'.$curso->idcurso.'/busca')->assertRedirect(route('login'));
        $this->get('/admin/cursos/inscritos/editar/'.$curso->idcurso)->assertRedirect(route('login'));
        $this->put('/admin/cursos/inscritos/editar/'.$curso->idcurso)->assertRedirect(route('login'));
        $this->put('/admin/cursos/inscritos/confirmar-presenca/'.$curso->idcurso)->assertRedirect(route('login'));
        $this->put('/admin/cursos/inscritos/confirmar-falta/'.$curso->idcurso)->assertRedirect(route('login'));
        $this->get('/admin/cursos/adicionar-inscrito/'.$curso->idcurso)->assertRedirect(route('login'));
        $this->post('/admin/cursos/adicionar-inscrito/'.$curso->idcurso)->assertRedirect(route('login'));
        $this->get('/admin/cursos/inscritos/download/'.$curso->idcurso)->assertRedirect(route('login'));
        $this->delete('/admin/cursos/cancelar-inscricao/'.$curso->idcurso)->assertRedirect(route('login'));

        // Quando acesso privado
        $this->get(route('cursos.inscricao.website', $curso->idcurso))->assertRedirect(route('representante.login'));
        $this->post(route('cursos.inscricao', $curso->idcurso), ['idcurso' => $curso->idcurso])->assertRedirect(route('representante.login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.index'))->assertForbidden();
        $this->get(route('cursos.busca'))->assertForbidden();
        $this->get(route('cursos.edit', $curso->idcurso))->assertForbidden();
        $this->get(route('cursos.create'))->assertForbidden();
        $this->get(route('cursos.lixeira'))->assertForbidden();
        $this->get(route('cursos.restore', $curso->idcurso))->assertForbidden();
        $this->post(route('cursos.store'), $curso->toArray())->assertForbidden();
        $this->patch(route('cursos.update', $curso->idcurso), $curso->toArray())->assertForbidden();
        $this->delete(route('cursos.destroy', $curso->idcurso))->assertForbidden();

        $this->get(route('inscritos.index', $curso->idcurso))->assertForbidden();
        $this->get('/admin/cursos/inscritos/'.$curso->idcurso.'/busca')->assertForbidden();
        $this->get('/admin/cursos/inscritos/editar/'.$curso->idcurso)->assertForbidden();
        $this->put('/admin/cursos/inscritos/editar/'.$curso->idcurso)->assertForbidden();
        $this->put('/admin/cursos/inscritos/confirmar-presenca/'.$curso->idcurso)->assertForbidden();
        $this->put('/admin/cursos/inscritos/confirmar-falta/'.$curso->idcurso)->assertForbidden();
        $this->get('/admin/cursos/adicionar-inscrito/'.$curso->idcurso)->assertForbidden();
        $this->post('/admin/cursos/adicionar-inscrito/'.$curso->idcurso)->assertForbidden();
        $this->get('/admin/cursos/inscritos/download/'.$curso->idcurso)->assertForbidden();
        $this->delete('/admin/cursos/cancelar-inscricao/'.$curso->idcurso)->assertForbidden();
    }

    /** @test */
    public function curso_can_be_created()
    {
        $curso = factory('App\Curso')->create();

        $this->assertDatabaseHas('cursos', ['tema' => $curso->tema]);
    }

    /** @test */
    public function curso_can_be_created_by_an_user()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw();

        $this->post(route('cursos.store'), $attributes);
        $this->assertDatabaseHas('cursos', [
            'tema' => $attributes['tema'],
            'idusuario' => $user->idusuario
        ]);
    }

    /** @test */
    public function log_is_generated_when_curso_is_created()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Curso')->raw();

        $this->post(route('cursos.store'), $attributes);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('criou', $log);
        $this->assertStringContainsString('curso', $log);
    }

    /** @test */
    public function curso_is_shown_on_admin_panel_after_its_creation()
    {
        $this->signInAsAdmin();
        $curso = factory('App\Curso')->create();
        
        $this->get(route('cursos.index'))
            ->assertSee($curso->idcurso)
            ->assertSee($curso->tema);
    }

    /** @test */
    public function curso_without_acesso_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'acesso' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('acesso');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_invalid_value_in_acesso_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'acesso' => 'Liberado'
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('acesso');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_without_tema_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'tema' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('tema');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_without_datarealizacao_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'datarealizacao' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('datarealizacao');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_without_datatermino_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'datatermino' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('datatermino');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_endereco_is_required_if_tipo_not_live()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'tipo' => 'Curso',
            'endereco' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('endereco');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_endereco_is_not_required_if_tipo_is_live()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'tipo' => 'Live',
            'endereco' => ''
        ]);

        $this->post(route('cursos.store'), $attributes);
        $this->assertDatabaseHas('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function the_name_of_the_user_who_created_curso_is_shown_on_admin_panel()
    {
        $user = $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.edit', $curso->idcurso))
            ->assertOk()
            ->assertSee($user->nome);
    }

    /** @test */
    public function the_cursos_regional_is_shown_on_admin_panel()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.index'))
            ->assertSee($curso->regional->regional);
    }

    /** @test */
    public function non_authorized_users_cannot_create_cursos()
    {
        $this->signIn();

        $this->get(route('cursos.create'))->assertForbidden();

        $attributes = factory('App\Curso')->raw();

        $this->post(route('cursos.store'), $attributes)->assertForbidden();
        $this->assertDatabaseMissing('cursos', ['tema' => $attributes['tema']]);
    }

    /** @test */
    public function non_authorized_users_cannot_see_cursos_on_admin_panel()
    {
        $this->signIn();

        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.index'))
            ->assertForbidden()
            ->assertDontSee($curso->tema);
    }

    /** @test */
    function multiple_cursos_can_be_created()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $cursoDois = factory('App\Curso')->create();

        $this->assertDatabaseHas('cursos', ['tema' => $curso->tema]);
        $this->assertDatabaseHas('cursos', ['tema' => $cursoDois->tema]);
        $this->assertEquals(2, Curso::count());
    }

    /** @test */
    public function curso_can_be_updated()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\Curso')->raw();

        $this->patch(route('cursos.update', $curso->idcurso), $attributes);

        $cur = Curso::find($curso->idcurso);
        $this->assertEquals($cur->tema, $attributes['tema']);
        $this->assertEquals($cur->descricao, $attributes['descricao']);
        $this->assertEquals($cur->resumo, $attributes['resumo']);
        $this->assertDatabaseHas('cursos', [
            'tema' => $attributes['tema'],
            'descricao' => $attributes['descricao'],
            'resumo' => $attributes['resumo']
        ]);
    }

    /** @test */
    public function log_is_generated_when_curso_is_updated()
    {
        $user = $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\Curso')->raw();

        $this->patch(route('cursos.update', $curso->idcurso), $attributes);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('editou', $log);
        $this->assertStringContainsString('curso', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_update_cursos()
    {
        $this->signIn();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\Curso')->raw();

        $this->get(route('cursos.edit', $curso->idcurso))->assertForbidden();
        $this->patch(route('cursos.update', $curso->idcurso), $attributes)->assertForbidden();

        $this->assertDatabaseMissing('cursos', ['tema' => $attributes['tema']]);
    }

    /** @test */
    public function curso_can_be_deleted()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->delete(route('cursos.destroy', $curso->idcurso));
        $this->assertSoftDeleted('cursos', ['idcurso' => $curso->idcurso]);
    }

    /** @test */
    public function log_is_generated_when_curso_is_deleted()
    {
        $user = $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $this->delete(route('cursos.destroy', $curso->idcurso));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('cancelou', $log);
        $this->assertStringContainsString('curso', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_delete_curso()
    {
        $this->signIn();

        $curso = factory('App\Curso')->create();

        $this->delete(route('cursos.destroy', $curso->idcurso))->assertForbidden();
        $this->assertNull(Curso::withTrashed()->find($curso->idcurso)->deleted_at);
    }

    /** @test */
    public function canceled_cursos_are_shown_in_trash()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->delete(route('cursos.destroy', $curso->idcurso));

        $this->get(route('cursos.lixeira'))->assertOk()->assertSee($curso->idcurso);
    }

    /** @test */
    public function deleted_cursos_are_not_shown_on_index()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->delete(route('cursos.destroy', $curso->idcurso));

        $this->get(route('cursos.index'))->assertOk()->assertDontSee($curso->tema);
    }

    /** @test */
    public function deleted_cursos_can_be_restored()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->delete(route('cursos.destroy', $curso->idcurso));
        $this->get(route('cursos.restore', $curso->idcurso));

        $this->assertNull(Curso::find($curso->idcurso)->deleted_at);
        $this->get(route('cursos.index'))->assertSee($curso->tema);
    }

    /** @test */
    public function log_is_generated_when_curso_is_restored()
    {
        $user = $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $this->delete(route('cursos.destroy', $curso->idcurso));
        $this->get(route('cursos.restore', $curso->idcurso));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('reabriu', $log);
        $this->assertStringContainsString('curso', $log);
    }

    /** @test */
    function curso_can_be_searched_on_admin_panel()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.busca', ['q' => $curso->tema]))
            ->assertSeeText($curso->tema);
    }

    /** @test */
    function link_to_create_curso_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $this->get(route('cursos.index'))->assertSee(route('cursos.create'));
    }

    /** @test */
    function link_to_edit_curso_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.index'))->assertSee(route('cursos.edit', $curso->idcurso));
    }

    /** @test */
    function link_to_destroy_curso_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.index'))->assertSee(route('cursos.destroy', $curso->idcurso));
    }

    /** @test */
    function curso_is_shown_on_website()
    {
        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.show', $curso->idcurso))
            ->assertOk()
            ->assertSee($curso->tema);
    }

    /** @test */
    function next_cursos_are_shown_on_next_curso_lista_on_website()
    {
        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.index.website'))
            ->assertOk()
            ->assertSee(route('cursos.show', $curso->idcurso))
            ->assertSee($curso->tema);
    }

    /** @test */
    function next_cursos_are_not_shown_on_previous_curso_lista_on_website()
    {
        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.previous.website'))
            ->assertOk()
            ->assertDontSee($curso->tema);
    }

    /** @test */
    function previous_cursos_are_not_shown_on_next_curso_list_on_website()
    {
        $date = new \DateTime();
        $date->sub(new DateInterval('P30D'));
        $realizacao = $date->format('Y-m-d\TH:i:s');
        $new_date = new \DateTime();
        $new_date->sub(new DateInterval('P31D'));
        $termino = $new_date->format('Y-m-d\TH:i:s');

        $curso = factory('App\Curso')->create([
            'datarealizacao' => $realizacao,
            'datatermino' => $termino
        ]);

        $this->get(route('cursos.index.website'))
            ->assertOk()
            ->assertDontSee($curso->tema);
    }

    /** @test */
    function previous_cursos_are_shown_on_previous_curso_list_on_website()
    {
        $date = new \DateTime();
        $date->sub(new DateInterval('P30D'));
        $realizacao = $date->format('Y-m-d\TH:i:s');
        $new_date = new \DateTime();
        $new_date->sub(new DateInterval('P31D'));
        $termino = $new_date->format('Y-m-d\TH:i:s');

        $curso = factory('App\Curso')->create([
            'datarealizacao' => $realizacao,
            'datatermino' => $termino
        ]);

        $this->get(route('cursos.previous.website'))
            ->assertOk()
            ->assertSee(route('cursos.show', $curso->idcurso))
            ->assertSee($curso->tema);
    }

    /** @test */
    function previous_cursos_with_noticia_are_shown_on_previous_curso_list_on_website()
    {
        $date = new \DateTime();
        $date->sub(new DateInterval('P30D'));
        $realizacao = $date->format('Y-m-d\TH:i:s');
        $new_date = new \DateTime();
        $new_date->sub(new DateInterval('P31D'));
        $termino = $new_date->format('Y-m-d\TH:i:s');

        $curso = factory('App\Curso')->create([
            'datarealizacao' => $realizacao,
            'datatermino' => $termino
        ]);

        $noticias = factory('App\Noticia', 2)->create([
            'idcurso' => $curso->idcurso
        ]);

        $this->get(route('cursos.previous.website'))
            ->assertOk()
            ->assertSee(route('cursos.show', $curso->idcurso))
            ->assertSee($curso->tema)
            ->assertSeeText('Veja como foi')
            ->assertSee('noticia/' . $noticias->get(0)->slug)
            ->assertDontSee('noticia/' . $noticias->get(1)->slug);
    }

    /** 
     * =======================================================================================================
     * TESTES INSCRITOS ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function inscrito_private_can_be_created_by_admin()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $inscrito = factory('App\CursoInscrito')->states('tipo_convidado')->raw([
            'idcurso' => $curso->idcurso,
        ]);
        $this->post('/admin/cursos/adicionar-inscrito/'.$curso->idcurso, $inscrito)
        ->assertRedirect(route('inscritos.index', $curso->idcurso));

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf'], 'tipo_inscrito' => CursoInscrito::INSCRITO_CON]);

        CursoInscrito::find(1)->delete();
        $inscrito = factory('App\CursoInscrito')->states('tipo_autoridade')->raw([
            'idcurso' => $curso->idcurso,
        ]);
        $this->post('/admin/cursos/adicionar-inscrito/'.$curso->idcurso, $inscrito)
        ->assertRedirect(route('inscritos.index', $curso->idcurso));

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf'], 'tipo_inscrito' => CursoInscrito::INSCRITO_AUT]);

        CursoInscrito::find(2)->delete();
        $inscrito = factory('App\CursoInscrito')->states('tipo_parceiro')->raw([
            'idcurso' => $curso->idcurso,
        ]);
        $this->post('/admin/cursos/adicionar-inscrito/'.$curso->idcurso, $inscrito)
        ->assertRedirect(route('inscritos.index', $curso->idcurso));

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf'], 'tipo_inscrito' => CursoInscrito::INSCRITO_PAR]);

        CursoInscrito::find(3)->delete();
        $inscrito = factory('App\CursoInscrito')->states('tipo_funcionario')->raw([
            'idcurso' => $curso->idcurso,
        ]);
        $this->post('/admin/cursos/adicionar-inscrito/'.$curso->idcurso, $inscrito)
        ->assertRedirect(route('inscritos.index', $curso->idcurso));

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf'], 'tipo_inscrito' => CursoInscrito::INSCRITO_FUN]);

        CursoInscrito::find(4)->delete();
        $inscrito = factory('App\CursoInscrito')->states('tipo_site')->raw([
            'idcurso' => $curso->idcurso,
        ]);
        $this->post('/admin/cursos/adicionar-inscrito/'.$curso->idcurso, $inscrito)
        ->assertRedirect(route('inscritos.index', $curso->idcurso));

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf'], 'tipo_inscrito' => CursoInscrito::INSCRITO_SITE]);
    }

    /** @test */
    public function inscrito_public_can_be_created_by_admin()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->states('publico')->create();

        $inscrito = factory('App\CursoInscrito')->states('tipo_convidado')->raw([
            'idcurso' => $curso->idcurso,
        ]);
        $this->post('/admin/cursos/adicionar-inscrito/'.$curso->idcurso, $inscrito)
        ->assertRedirect(route('inscritos.index', $curso->idcurso));

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf'], 'tipo_inscrito' => CursoInscrito::INSCRITO_CON]);

        CursoInscrito::find(1)->delete();
        $inscrito = factory('App\CursoInscrito')->states('tipo_autoridade')->raw([
            'idcurso' => $curso->idcurso,
        ]);
        $this->post('/admin/cursos/adicionar-inscrito/'.$curso->idcurso, $inscrito)
        ->assertRedirect(route('inscritos.index', $curso->idcurso));

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf'], 'tipo_inscrito' => CursoInscrito::INSCRITO_AUT]);

        CursoInscrito::find(2)->delete();
        $inscrito = factory('App\CursoInscrito')->states('tipo_parceiro')->raw([
            'idcurso' => $curso->idcurso,
        ]);
        $this->post('/admin/cursos/adicionar-inscrito/'.$curso->idcurso, $inscrito)
        ->assertRedirect(route('inscritos.index', $curso->idcurso));

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf'], 'tipo_inscrito' => CursoInscrito::INSCRITO_PAR]);

        CursoInscrito::find(3)->delete();
        $inscrito = factory('App\CursoInscrito')->states('tipo_funcionario')->raw([
            'idcurso' => $curso->idcurso,
        ]);
        $this->post('/admin/cursos/adicionar-inscrito/'.$curso->idcurso, $inscrito)
        ->assertRedirect(route('inscritos.index', $curso->idcurso));

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf'], 'tipo_inscrito' => CursoInscrito::INSCRITO_FUN]);

        CursoInscrito::find(4)->delete();
        $inscrito = factory('App\CursoInscrito')->states('tipo_site')->raw([
            'idcurso' => $curso->idcurso,
        ]);
        $this->post('/admin/cursos/adicionar-inscrito/'.$curso->idcurso, $inscrito)
        ->assertRedirect(route('inscritos.index', $curso->idcurso));

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf'], 'tipo_inscrito' => CursoInscrito::INSCRITO_SITE]);
    }

    /** 
     * =======================================================================================================
     * TESTES INSCRITOS SITE
     * =======================================================================================================
     */

    /** @test */
    public function inscrito_private_can_be_created()
    {
        Mail::fake();

        $curso = factory('App\Curso')->create();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertSee('disabled');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['idcurso' => $curso->idcurso, 'termo' => 'on'])
        ->assertViewIs('site.agradecimento');

        Mail::assertQueued(CursoInscritoMailGuest::class);

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $representante->cpf_cnpj]);
    }

    /** @test */
    public function inscrito_private_can_be_created_with_cnpj_temp()
    {
        Mail::fake();

        $curso = factory('App\Curso')->create();

        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '11748345000144'
        ]);
        $this->actingAs($representante, 'representante');

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertSee('disabled');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['idcurso' => $curso->idcurso, 'termo' => 'on'])
        ->assertViewIs('site.agradecimento');

        Mail::assertQueued(CursoInscritoMailGuest::class);

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $representante->cpf_cnpj]);
    }

    /** @test */
    public function inscrito_private_cannot_be_created_exists_cpf()
    {
        $curso = factory('App\Curso')->create();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['idcurso' => $curso->idcurso, 'termo' => 'on']);

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $representante->cpf_cnpj]);

        $this->post(route('cursos.inscricao', $curso->idcurso), ['idcurso' => $curso->idcurso, 'termo' => 'on'])
        ->assertSessionHasErrors('cpf');
    }

    /** @test */
    public function inscrito_private_cannot_be_created_gerenti_without_situacao_em_dia()
    {
        // Editar GerentiMock gerentiStatus()
        $curso = factory('App\Curso')->create();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertRedirect(route('representante.cursos'))
        ->assertSessionHas('message', '<i class="fas fa-info-circle"></i>&nbsp;Para liberar sua inscrição entre em contato com o setor de atendimento da <a href="'.route('regionais.siteGrid').'" target="_blank">seccional</a> de interesse.');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['idcurso' => $curso->idcurso, 'termo' => 'on'])
        ->assertRedirect(route('representante.cursos'))
        ->assertSessionHas('message', '<i class="fas fa-info-circle"></i>&nbsp;Para liberar sua inscrição entre em contato com o setor de atendimento da <a href="'.route('regionais.siteGrid').'" target="_blank">seccional</a> de interesse.');

        $this->assertDatabaseMissing('curso_inscritos', ['cpf' => $representante->cpf_cnpj]);
    }

    /** @test */
    public function inscrito_public_can_be_created()
    {
        Mail::fake();

        $curso = factory('App\Curso')->states('publico')->create();
        $inscrito = factory('App\CursoInscrito')->raw([
            'idcurso' => $curso->idcurso,
            'termo' => 'on'
        ]);

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertDontSee('disabled');

        $this->post(route('cursos.inscricao', $curso->idcurso), $inscrito)
        ->assertViewIs('site.agradecimento');

        Mail::assertQueued(CursoInscritoMailGuest::class);

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf']]);
    }

    /** @test */
    public function inscrito_private_can_be_created_with_cnpj_temp_if_authenticated()
    {
        Mail::fake();

        $curso = factory('App\Curso')->states('publico')->create();

        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '11748345000144'
        ]);
        $this->actingAs($representante, 'representante');

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertSee('disabled');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['idcurso' => $curso->idcurso, 'termo' => 'on'])
        ->assertViewIs('site.agradecimento');

        Mail::assertQueued(CursoInscritoMailGuest::class);

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $representante->cpf_cnpj]);
    }

    /** @test */
    public function inscrito_public_cannot_be_created_with_cnpj_temp_without_authenticated()
    {
        $curso = factory('App\Curso')->states('publico')->create();
        $inscrito = factory('App\CursoInscrito')->raw([
            'idcurso' => $curso->idcurso,
            'cpf' => '11748345000144',
            'termo' => 'on'
        ]);

        $this->post(route('cursos.inscricao', $curso->idcurso), $inscrito)
        ->assertSessionHasErrors('cpf');
    }

    /** @test */
    public function inscrito_public_cannot_be_created_exists_cpf()
    {
        $curso = factory('App\Curso')->states('publico')->create();
        $inscrito = factory('App\CursoInscrito')->raw([
            'idcurso' => $curso->idcurso,
            'termo' => 'on'
        ]);

        $this->post(route('cursos.inscricao', $curso->idcurso), $inscrito);

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf']]);

        $this->post(route('cursos.inscricao', $curso->idcurso), $inscrito)
        ->assertSessionHasErrors('cpf');
    }

    /** @test */
    public function inscrito_public_can_be_created_by_representante()
    {
        Mail::fake();

        $curso = factory('App\Curso')->states('publico')->create();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertSee('disabled');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['idcurso' => $curso->idcurso, 'termo' => 'on'])
        ->assertViewIs('site.agradecimento');

        Mail::assertQueued(CursoInscritoMailGuest::class);

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $representante->cpf_cnpj]);
    }

    /** @test */
    public function inscrito_public_cannot_be_created_without_required_input()
    {
        $curso = factory('App\Curso')->states('publico')->create();

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertDontSee('disabled');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['idcurso' => $curso->idcurso, 'termo' => null])
        ->assertSessionHasErrors([
            'cpf',
            'nome',
            'telefone',
            'termo'
        ]);

        $this->assertDatabaseMissing('curso_inscritos', ['idcursoinscrito' => 1]);
    }

    /** 
     * =======================================================================================================
     * TESTES INSCRITOS ÁREA DO REPRESENTANTE
     * =======================================================================================================
     */

    /** @test */
    public function cannot_view_cards_without_cursos()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('representante.cursos'))
        ->assertOk()
        ->assertSee('<p class="light pb-0">No momento não há cursos restritos com vagas abertas para o representante.</p>');

        $curso = factory('App\Curso')->create([
            'datatermino' => now()->subDay()->format('Y-m-d H:i:s')
        ]);

        $this->get(route('representante.cursos'))
        ->assertOk()
        ->assertSee('<p class="light pb-0">No momento não há cursos restritos com vagas abertas para o representante.</p>');
    }

    /** @test */
    public function can_view_cards_with_cursos()
    {
        $curso = factory('App\Curso')->create();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('representante.cursos'))
        ->assertOk()
        ->assertSee('<a href="'. route('cursos.show', $curso->idcurso) .'">')
        ->assertSee('<h6 class="light cinza-claro">'. $curso->regional->regional .' - '. onlyDate($curso->datarealizacao) .'</h6>')
        ->assertSee('<a href="'. route('cursos.inscricao.website', $curso->idcurso) .'" class="btn btn-sm btn-primary text-white mt-2">Inscrever-se</a>');
    }

    /** @test */
    public function can_view_button_inscrito_with_cursos()
    {
        $curso = factory('App\Curso')->create();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['idcurso' => $curso->idcurso, 'termo' => 'on']);

        $this->get(route('representante.cursos'))
        ->assertOk()
        ->assertSee('<a href="'. route('cursos.show', $curso->idcurso) .'">')
        ->assertSee('<h6 class="light cinza-claro">'. $curso->regional->regional .' - '. onlyDate($curso->datarealizacao) .'</h6>')
        ->assertSee('<span class="btn btn-sm btn-secondary text-center mt-2 disabled">Inscrição realizada</span>');
    }
}
