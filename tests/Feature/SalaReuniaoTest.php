<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Permissao;
use Carbon\Carbon;
use App\SalaReuniao;
use Illuminate\Support\Facades\Mail;
use App\Mail\SalaReuniaoMail;

class SalaReuniaoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $sala = factory('App\SalaReuniao')->create();
        
        $this->get(route('sala.reuniao.index'))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertRedirect(route('login'));
        $this->put(route('sala.reuniao.editar', $sala->id))->assertRedirect(route('login'));
    }

    // /** @test */
    // public function non_authorized_users_cannot_access_links()
    // {
    //     $this->signIn();
    //     $this->assertAuthenticated('web');

    //     $plantao = factory('App\PlantaoJuridico')->create();
    //     $bloqueio = factory('App\PlantaoJuridicoBloqueio')->create();

    //     $this->get(route('plantao.juridico.index'))->assertForbidden();
    //     $this->get(route('plantao.juridico.editar.view', $plantao->id))->assertForbidden();
    //     $this->put(route('plantao.juridico.editar', $plantao->id))->assertForbidden();
    //     $this->get(route('plantao.juridico.bloqueios.index'))->assertForbidden();
    //     $this->get(route('plantao.juridico.bloqueios.criar.view'))->assertForbidden();
    //     $this->post(route('plantao.juridico.bloqueios.criar'))->assertForbidden();
    //     $this->get(route('plantao.juridico.bloqueios.editar.view', $bloqueio->id))->assertForbidden();
    //     $this->put(route('plantao.juridico.bloqueios.editar', $bloqueio->id))->assertForbidden();
    //     $this->delete(route('plantao.juridico.bloqueios.excluir', $bloqueio->id))->assertForbidden();
    //     $this->get(route('plantao.juridico.bloqueios.ajax'))->assertForbidden();
    // }

    /* SALAS */

    /** @test */
    public function sala_can_be_edited()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_r = $sala->getHorariosTarde('reuniao');
        $horarios_c = $sala->getHorariosManha('coworking');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = 2;
        $dados['participantes_coworking'] = 2;
        $dados['manha_horarios_reuniao'] = ['10:00', '11:00', '11:30'];
        $dados['tarde_horarios_reuniao'] = $horarios_r;
        $dados['manha_horarios_coworking'] = $horarios_c;
        $dados['tarde_horarios_coworking'] = ['14:00', '15:00', '15:30'];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'participantes_reuniao' => 2,
            'participantes_coworking' => 2,
            'horarios_reuniao' => json_encode(['manha' => $dados['manha_horarios_reuniao'], 'tarde' => $horarios_r], JSON_FORCE_OBJECT),
            'horarios_coworking' => json_encode(['manha' => $horarios_c, 'tarde' => $dados['tarde_horarios_coworking']], JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function log_is_generated_when_sala_is_edited()
    {
        $user = $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_r = $sala->getHorariosTarde('reuniao');
        $horarios_c = $sala->getHorariosManha('coworking');

        $this->get(route('sala.reuniao.editar.view', $sala->id));

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = 2;
        $dados['manha_horarios_reuniao'] = ['10:00', '11:00', '11:30'];
        $dados['tarde_horarios_reuniao'] = $horarios_r;
        $dados['manha_horarios_coworking'] = $horarios_c;
        $dados['tarde_horarios_coworking'] = ['14:00', '15:00', '15:30'];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou *sala de reunião* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function sala_can_be_edited_without_horarios_reuniao_when_participantes_reuniao_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_c = $sala->getHorariosManha('coworking');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = 0;
        $dados['manha_horarios_coworking'] = $horarios_c;
        $dados['tarde_horarios_coworking'] = ['14:00', '15:00', '16:00'];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'participantes_reuniao' => 0,
            'horarios_reuniao' => json_encode(['manha' => array(), 'tarde' => array()], JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function sala_can_be_edited_without_itens_reuniao_when_participantes_reuniao_0()
    {
        $this->withoutExceptionHandling();
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_c = $sala->getHorariosManha('coworking');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = 0;
        $dados['manha_horarios_coworking'] = $horarios_c;
        $dados['tarde_horarios_coworking'] = ['14:00', '15:00', '16:00'];
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'participantes_reuniao' => 0,
            'horarios_reuniao' => json_encode(['manha' => array(), 'tarde' => array()], JSON_FORCE_OBJECT),
            'itens_reuniao' => json_encode(array(), JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function sala_can_be_edited_without_horarios_coworking_when_participantes_coworking_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_r = $sala->getHorariosManha('reuniao');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_coworking'] = 0;
        $dados['manha_horarios_reuniao'] = $horarios_r;
        $dados['tarde_horarios_reuniao'] = ['14:00', '15:00', '16:00'];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'participantes_coworking' => 0,
            'horarios_coworking' => json_encode(['manha' => array(), 'tarde' => array()], JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function sala_can_be_edited_without_itens_coworking_when_participantes_coworking_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_r = $sala->getHorariosManha('reuniao');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_coworking'] = 0;
        $dados['manha_horarios_reuniao'] = $horarios_r;
        $dados['tarde_horarios_reuniao'] = ['14:00', '15:00', '16:00'];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'participantes_coworking' => 0,
            'horarios_coworking' => json_encode(['manha' => array(), 'tarde' => array()], JSON_FORCE_OBJECT),
            'itens_coworking' => json_encode(array(), JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_participantes_reuniao()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = null;
        $dados['manha_horarios_reuniao'] = $sala->getHorariosManha('reuniao');
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'participantes_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_participantes_reuniao_not_integer()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = 'A';
        $dados['manha_horarios_reuniao'] = $sala->getHorariosManha('reuniao');
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'participantes_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_participantes_reuniao_equal_1()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = 1;
        $dados['manha_horarios_reuniao'] = $sala->getHorariosManha('reuniao');
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'participantes_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_periodo_manha_when_participantes_reuniao_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = null;
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'manha_horarios_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_periodo_manha_not_array_when_participantes_reuniao_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = '09:00,10:00';
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'manha_horarios_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_periodo_manha_invalid_when_participantes_reuniao_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = ['15:00', '16:00'];
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'manha_horarios_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_periodo_manha_not_distinct_when_participantes_reuniao_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = ['09:00', '10:00', '10:00'];
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'manha_horarios_reuniao.*'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_periodo_tarde_when_participantes_reuniao_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['tarde_horarios_reuniao'] = null;
        $dados['manha_horarios_reuniao'] = $sala->getHorariosManha('reuniao');
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'tarde_horarios_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_periodo_tarde_not_array_when_participantes_reuniao_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['tarde_horarios_reuniao'] = '09:00,10:00';
        $dados['manha_horarios_reuniao'] = $sala->getHorariosManha('reuniao');
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'tarde_horarios_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_periodo_tarde_invalid_when_participantes_reuniao_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['tarde_horarios_reuniao'] = ['10:00', '11:00'];
        $dados['manha_horarios_reuniao'] = $sala->getHorariosManha('reuniao');
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'tarde_horarios_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_periodo_tarde_not_distinct_when_participantes_reuniao_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['tarde_horarios_reuniao'] = ['14:00', '15:00', '14:00'];
        $dados['manha_horarios_reuniao'] = $sala->getHorariosManha('reuniao');
        $dados['itens_reuniao'] = $sala->getItens('reuniao');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'tarde_horarios_reuniao.*'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_itens_when_participantes_reuniao_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = $sala->getHorariosManha('reuniao');
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = null;

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_itens_not_array_when_participantes_reuniao_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = $sala->getHorariosManha('reuniao');
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = 'tv de teste';

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_itens_invalid_when_participantes_reuniao_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = $sala->getHorariosManha('reuniao');
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = ['Agua', 'Café'];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_itens_not_distinct_when_participantes_reuniao_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = $sala->getHorariosManha('reuniao');
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = ['Água', 'Café', 'Água'];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_participantes_coworking()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_coworking'] = null;
        $dados['manha_horarios_coworking'] = $sala->getHorariosManha('coworking');
        $dados['tarde_horarios_coworking'] = $sala->getHorariosTarde('coworking');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'participantes_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_participantes_coworking_not_integer()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_coworking'] = 'A';
        $dados['manha_horarios_coworking'] = $sala->getHorariosManha('coworking');
        $dados['tarde_horarios_coworking'] = $sala->getHorariosTarde('coworking');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'participantes_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_periodo_manha_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_coworking'] = null;
        $dados['tarde_horarios_coworking'] = $sala->getHorariosTarde('coworking');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'manha_horarios_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_periodo_manha_not_array_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_coworking'] = '09:00,10:00';
        $dados['tarde_horarios_coworking'] = $sala->getHorariosTarde('coworking');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'manha_horarios_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_periodo_manha_invalid_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_coworking'] = ['15:00', '16:00'];
        $dados['tarde_horarios_coworking'] = $sala->getHorariosTarde('coworking');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'manha_horarios_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_periodo_manha_not_distinct_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_coworking'] = ['09:00', '10:00', '10:00'];
        $dados['tarde_horarios_coworking'] = $sala->getHorariosTarde('coworking');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'manha_horarios_coworking.*'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_periodo_tarde_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['tarde_horarios_coworking'] = null;
        $dados['manha_horarios_coworking'] = $sala->getHorariosManha('coworking');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'tarde_horarios_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_periodo_tarde_not_array_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['tarde_horarios_coworking'] = '09:00,10:00';
        $dados['manha_horarios_coworking'] = $sala->getHorariosManha('coworking');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'tarde_horarios_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_periodo_tarde_invalid_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['tarde_horarios_coworking'] = ['10:00', '11:00'];
        $dados['manha_horarios_coworking'] = $sala->getHorariosManha('coworking');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'tarde_horarios_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_periodo_tarde_not_distinct_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['tarde_horarios_coworking'] = ['14:00', '15:00', '14:00'];
        $dados['manha_horarios_coworking'] = $sala->getHorariosManha('coworking');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'tarde_horarios_coworking.*'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_itens_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_coworking'] = $sala->getHorariosManha('coworking');
        $dados['tarde_horarios_coworking'] = $sala->getHorariosTarde('coworking');
        $dados['itens_coworking'] = null;

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_itens_not_array_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_coworking'] = $sala->getHorariosManha('coworking');
        $dados['tarde_horarios_coworking'] = $sala->getHorariosTarde('coworking');
        $dados['itens_coworking'] = 'tv de teste';

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_itens_invalid_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_coworking'] = $sala->getHorariosManha('coworking');
        $dados['tarde_horarios_coworking'] = $sala->getHorariosTarde('coworking');
        $dados['itens_coworking'] = ['Agua', 'Café'];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_itens_not_distinct_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_coworking'] = $sala->getHorariosManha('coworking');
        $dados['tarde_horarios_coworking'] = $sala->getHorariosTarde('coworking');
        $dados['itens_coworking'] = ['Água', 'Café', 'Água'];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_coworking.*'
        ]);
    }

    /** @test */
    public function send_mail_after_change_itens_reuniao_or_participantes_reuniao_when_idregional_not_1_or_14()
    {
        Mail::fake();

        $user = factory('App\User')->create([
            'idperfil' => factory('App\Perfil')->create([
                'idperfil' => 21
            ]),
            'email' => 'gerteste@teste.com',
            'idregional' => factory('App\Regional')->create([
                'idregional' => 2
            ])
        ]);

        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create([
            'idregional' => $user->idregional
        ]);
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = ['10:30', '11:00', '11:30'];
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = ['Água', 'Café'];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados);

        Mail::assertQueued(SalaReuniaoMail::class);
    }

    /** @test */
    public function send_mail_after_change_itens_reuniao_or_participantes_reuniao_when_idregional_1_or_14()
    {
        Mail::fake();

        $user = factory('App\User')->create([
            'idusuario' => 39,
            'email' => 'teste@teste.com',
            'idregional' => factory('App\Regional')->create([
                'idregional' => 1
            ])
        ]);

        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create([
            'idregional' => $user->idregional
        ]);
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = ['10:30', '11:00', '11:30'];
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = ['Água', 'Café'];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados);

        Mail::assertQueued(SalaReuniaoMail::class);
    }

    /** @test */
    public function send_mail_after_change_itens_reuniao_or_participantes_coworking_when_idregional_not_1_or_14()
    {
        Mail::fake();

        $user = factory('App\User')->create([
            'idperfil' => factory('App\Perfil')->create([
                'idperfil' => 21
            ]),
            'email' => 'gerteste@teste.com',
            'idregional' => factory('App\Regional')->create([
                'idregional' => 2
            ])
        ]);

        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create([
            'idregional' => $user->idregional
        ]);
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_coworking'] = ['10:30', '11:00', '11:30'];
        $dados['tarde_horarios_coworking'] = $sala->getHorariosTarde('coworking');
        $dados['itens_coworking'] = ['Água', 'Café'];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados);

        Mail::assertQueued(SalaReuniaoMail::class);
    }

    /** @test */
    public function send_mail_after_change_itens_reuniao_or_participantes_coworking_when_idregional_1_or_14()
    {
        Mail::fake();

        $user = factory('App\User')->create([
            'idusuario' => 39,
            'email' => 'teste@teste.com',
            'idregional' => factory('App\Regional')->create([
                'idregional' => 1
            ])
        ]);

        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create([
            'idregional' => $user->idregional
        ]);
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_coworking'] = ['10:30', '11:00', '11:30'];
        $dados['tarde_horarios_coworking'] = $sala->getHorariosTarde('coworking');
        $dados['itens_coworking'] = ['Água', 'Café'];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados);

        Mail::assertQueued(SalaReuniaoMail::class);
    }
}
