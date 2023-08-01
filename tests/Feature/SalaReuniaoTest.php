<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Permissao;
use Carbon\Carbon;
use App\SalaReuniao;
use App\SalaReuniaoBloqueio;
use Illuminate\Support\Facades\Mail;
use App\Mail\SalaReuniaoMail;

class SalaReuniaoTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * =======================================================================================================
     * TESTES GERENCIAR SALA DE REUNIÃO
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $sala = factory('App\SalaReuniao')->create();
        
        $this->get(route('sala.reuniao.index'))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertRedirect(route('login'));
        $this->put(route('sala.reuniao.editar', $sala->id))->assertRedirect(route('login'));

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create();

        $this->get(route('sala.reuniao.bloqueio.lista'))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.bloqueio.busca'))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.bloqueio.criar'))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio->id))->assertRedirect(route('login'));
        $this->post(route('sala.reuniao.bloqueio.store'))->assertRedirect(route('login'));
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio->id))->assertRedirect(route('login'));
        $this->delete(route('sala.reuniao.bloqueio.delete', $bloqueio->id))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.bloqueio.horariosAjax'), ['id' => $bloqueio->sala_reuniao_id])->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        // $sala = factory('App\SalaReuniao')->create();
        
        // $this->get(route('sala.reuniao.index'))->assertForbidden();
        // $this->get(route('sala.reuniao.editar.view', $sala->id))->assertForbidden();
        // $this->put(route('sala.reuniao.editar', $sala->id))->assertForbidden();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create();

        $this->get(route('sala.reuniao.bloqueio.lista'))->assertForbidden();
        $this->get(route('sala.reuniao.bloqueio.busca'))->assertForbidden();
        $this->get(route('sala.reuniao.bloqueio.criar'))->assertForbidden();
        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio->id))->assertForbidden();
        $this->post(route('sala.reuniao.bloqueio.store'), [
            'sala_reuniao_id' => $bloqueio->sala_reuniao_id,
            'dataInicial' => $bloqueio->dataInicial,
            'dataFinal' => null,
            'horarios' => ['10:00'],
        ])->assertForbidden();
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio->id), [
            'sala_reuniao_id' => $bloqueio->sala_reuniao_id,
            'dataInicial' => $bloqueio->dataInicial,
            'dataFinal' => null,
            'horarios' => ['10:00'],
        ])->assertForbidden();
        $this->delete(route('sala.reuniao.bloqueio.delete', $bloqueio->id))->assertForbidden();
        $this->get(route('sala.reuniao.bloqueio.horariosAjax'), ['id' => $bloqueio->sala_reuniao_id])->assertForbidden();
    }

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
    public function when_add_mesa_change_same_participantes_reuniao()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_r = $sala->getHorariosTarde('reuniao');
        $horarios_c = $sala->getHorariosManha('coworking');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = 2;
        $dados['manha_horarios_reuniao'] = ['10:00', '11:00', '11:30'];
        $dados['tarde_horarios_reuniao'] = $horarios_r;
        $dados['manha_horarios_coworking'] = $horarios_c;
        $dados['tarde_horarios_coworking'] = ['14:00', '15:00', '15:30'];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertNotEquals($dados['itens_reuniao'][3], "Mesa com 2 cadeira(s)");
        $itens = $dados['itens_reuniao'];
        $itens[3] = "Mesa com 2 cadeira(s)";
        $this->assertDatabaseHas('salas_reunioes', [
            'participantes_reuniao' => 2,
            'horarios_reuniao' => json_encode(['manha' => $dados['manha_horarios_reuniao'], 'tarde' => $horarios_r], JSON_FORCE_OBJECT),
            'horarios_coworking' => json_encode(['manha' => $horarios_c, 'tarde' => $dados['tarde_horarios_coworking']], JSON_FORCE_OBJECT),
            'itens_reuniao' => json_encode($itens, JSON_FORCE_OBJECT)
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
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou *sala de reunião / coworking* (id: 1)';
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
    public function sala_can_be_edited_without_horarios_reuniao_manha_when_horarios_reuniao_tarde_full_and_participantes_reuniao_not_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_c = $sala->getHorariosManha('coworking');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = [];
        $dados['tarde_horarios_reuniao'] = ['14:00'];
        $dados['manha_horarios_coworking'] = $horarios_c;
        $dados['tarde_horarios_coworking'] = ['14:00', '15:00', '16:00'];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'horarios_reuniao' => json_encode(['manha' => array(), 'tarde' => ['14:00']], JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function sala_can_be_edited_without_horarios_reuniao_tarde_when_horarios_reuniao_manha_full_and_participantes_reuniao_not_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_c = $sala->getHorariosManha('coworking');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = ['10:00'];
        $dados['tarde_horarios_reuniao'] = [];
        $dados['manha_horarios_coworking'] = $horarios_c;
        $dados['tarde_horarios_coworking'] = ['14:00', '15:00', '16:00'];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'horarios_reuniao' => json_encode(['manha' => ['10:00'], 'tarde' => array()], JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function sala_can_be_edited_without_itens_reuniao_when_participantes_reuniao_0()
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
        $dados['itens_coworking'] = $sala->getItens('coworking');
        $dados['itens_reuniao'] = [];

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
    public function sala_can_be_edited_without_horarios_coworking_manha_when_horarios_coworking_tarde_full_and_participantes_coworking_not_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_r = $sala->getHorariosManha('reuniao');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = $horarios_r;
        $dados['tarde_horarios_reuniao'] = ['14:00', '15:00', '16:00'];
        $dados['manha_horarios_coworking'] = [];
        $dados['tarde_horarios_coworking'] = ['14:00'];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'horarios_coworking' => json_encode(['manha' => array(), 'tarde' => ['14:00']], JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function sala_can_be_edited_without_horarios_coworking_tarde_when_horarios_coworking_manha_full_and_participantes_coworking_not_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $horarios_r = $sala->getHorariosManha('reuniao');
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = $horarios_r;
        $dados['tarde_horarios_reuniao'] = ['14:00', '15:00', '16:00'];
        $dados['manha_horarios_coworking'] = ['10:00'];
        $dados['tarde_horarios_coworking'] = [];
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = $sala->getItens('coworking');

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'horarios_coworking' => json_encode(['manha' => ['10:00'], 'tarde' => array()], JSON_FORCE_OBJECT),
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
        $dados['itens_coworking'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertRedirect(route('sala.reuniao.index'));

        $this->assertDatabaseHas('salas_reunioes', [
            'participantes_coworking' => 0,
            'horarios_coworking' => json_encode(['manha' => array(), 'tarde' => array()], JSON_FORCE_OBJECT),
            'itens_coworking' => json_encode(array(), JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function sala_can_be_edited_with_itens_with_underline_when_participantes_reuniao_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['participantes_reuniao'] = 0;
        $dados['manha_horarios_reuniao'] = $sala->getHorariosManha('reuniao');
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = ['Água', 'Café', 'TV _ polegadas com entrada HDMI'];
        $dados['itens_coworking'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados);

        $this->assertDatabaseHas('salas_reunioes', [
            'itens_reuniao' => json_encode(array(), JSON_FORCE_OBJECT),
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
    public function sala_cannot_be_edited_without_periodo_manha_and_without_periodo_tarde_when_participantes_reuniao_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = null;
        $dados['tarde_horarios_reuniao'] = null;
        $dados['itens_reuniao'] = $sala->getItens('reuniao');
        $dados['itens_coworking'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'manha_horarios_reuniao',
            'tarde_horarios_reuniao'
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
        $dados['itens_coworking'] = [];

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
        $dados['itens_coworking'] = [];

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
        $dados['itens_coworking'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'manha_horarios_reuniao.*'
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
        $dados['itens_coworking'] = [];

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
        $dados['itens_coworking'] = [];

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
        $dados['itens_coworking'] = [];

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
        $dados['itens_coworking'] = [];

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
        $dados['itens_coworking'] = [];

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
        $dados['itens_coworking'] = [];

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
        $dados['itens_coworking'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'itens_reuniao'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_with_itens_with_underline_when_participantes_reuniao_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_coworking')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_reuniao'] = $sala->getHorariosManha('reuniao');
        $dados['tarde_horarios_reuniao'] = $sala->getHorariosTarde('reuniao');
        $dados['itens_reuniao'] = ['Água', 'Café', 'TV _ polegadas com entrada HDMI'];
        $dados['itens_coworking'] = [];

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
        $dados['itens_reuniao'] = [];

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
        $dados['itens_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'participantes_coworking'
        ]);
    }

    /** @test */
    public function sala_cannot_be_edited_without_periodo_manha_and_periodo_tarde_when_participantes_coworking_greater_than_0()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->states('desativa_reuniao')->create();
                
        $this->get(route('sala.reuniao.index'))->assertOk();
        $this->get(route('sala.reuniao.editar.view', $sala->id))->assertOk();

        $dados = $sala->toArray();
        $dados['manha_horarios_coworking'] = null;
        $dados['tarde_horarios_coworking'] = null;
        $dados['itens_coworking'] = $sala->getItens('coworking');
        $dados['itens_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'manha_horarios_coworking',
            'tarde_horarios_coworking'
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
        $dados['itens_reuniao'] = [];

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
        $dados['itens_reuniao'] = [];

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
        $dados['itens_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados)
        ->assertSessionHasErrors([
            'manha_horarios_coworking.*'
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
        $dados['itens_reuniao'] = [];

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
        $dados['itens_reuniao'] = [];

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
        $dados['itens_reuniao'] = [];

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
        $dados['itens_reuniao'] = [];

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
        $dados['itens_reuniao'] = [];

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
        $dados['itens_reuniao'] = [];

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
        $dados['itens_reuniao'] = [];

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
        $dados['itens_coworking'] = [];

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
        $dados['itens_coworking'] = [];

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
        $dados['itens_reuniao'] = [];

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
        $dados['itens_reuniao'] = [];

        $this->put(route('sala.reuniao.editar', $sala->id), $dados);

        Mail::assertQueued(SalaReuniaoMail::class);
    }

    /** 
     * =======================================================================================================
     * TESTES GERENCIAR SALA DE REUNIÃO BLOQUEIO
     * =======================================================================================================
     */

    /** @test */
    public function can_create_bloqueio()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'horarios' => ['10:00', '11:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 

        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseHas('salas_reunioes_bloqueios', [
            'id' => 1,
            'dataInicial' => $bloqueio['dataInicial'],
            'dataFinal' => $bloqueio['dataFinal'],
            'horarios' => implode(',', $bloqueio['horarios']),
            'sala_reuniao_id' => $bloqueio['sala_reuniao_id']
        ]);
    }

    /** @test */
    public function two_or_more_bloqueios_with_same_sala_can_be_created()
    {
        $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();

        $dados = [
            'sala_reuniao_id' => $sala->id,
            'dataInicial' => now()->addDay()->format('Y-m-d'),
            'dataFinal' => null,
            'horarios' => ['11:00']
        ];

        $this->post(route('sala.reuniao.bloqueio.store'), $dados)
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseHas('salas_reunioes_bloqueios', [
            'id' => 1,
            'dataInicial' => $dados['dataInicial'],
            'dataFinal' => $dados['dataFinal'],
            'horarios' => '11:00',
            'sala_reuniao_id' => $sala->id
        ]);

        $dados['dataFinal'] = now()->addDays(3)->format('Y-m-d');
        $dados['dataFinal'] = now()->addDays(6)->format('Y-m-d');
        $dados['horarios'] = ['10:00'];

        $this->post(route('sala.reuniao.bloqueio.store'), $dados)
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseHas('salas_reunioes_bloqueios', [
            'id' => 2,
            'dataInicial' => $dados['dataInicial'],
            'dataFinal' => $dados['dataFinal'],
            'horarios' => '10:00',
            'sala_reuniao_id' => $sala->id
        ]);
    }

    /** @test */
    public function can_create_bloqueio_without_data_final()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'horarios' => ['10:00', '14:00'],
            'dataFinal' => null
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseHas('salas_reunioes_bloqueios', [
            'id' => 1,
            'dataInicial' => $bloqueio['dataInicial'],
            'dataFinal' => $bloqueio['dataFinal'],
            'horarios' => implode(',', $bloqueio['horarios']),
            'sala_reuniao_id' => $bloqueio['sala_reuniao_id']
        ]);
    }

    /** @test */
    public function log_is_generated_when_bloqueio_is_created()
    {
        $user = $this->signInAsAdmin();
        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'horarios' => ['10:00', '11:00'],
        ]);

        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') criou *sala reunião / coworking bloqueio* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_create_bloqueio_without_sala_reuniao_id()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'sala_reuniao_id' => null,
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertStatus(404);
    }

    /** @test */
    public function cannot_create_bloqueio_when_sala_disabled()
    {
        $user = $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->states('desativa_ambos')->create();

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), [
            'sala_reuniao_id' => $sala->id,
            'dataInicial' => now()->addDay()->format('Y-m-d'),
            'dataFinal' => null,
            'horarios' => ['10:00'],
        ])
        ->assertSessionHasErrors([
            'sala_reuniao_id',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_without_data_inicial()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'dataInicial' => null,
            'horarios' => ['10:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'dataInicial',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_data_inicial_invalid()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'dataFinal' => null,
            'dataInicial' => now()->addDays(2)->format('d/m/Y'),
            'horarios' => ['10:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'dataInicial',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_data_final_invalid()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'dataInicial' => now()->addDays(2)->format('Y-m-d'),
            'dataFinal' => now()->addDays(2)->format('d/m/Y'),
            'horarios' => ['10:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'dataFinal',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_data_inicial_before_or_equal_today()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'dataInicial' => now()->format('Y-m-d'),
            'horarios' => ['10:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'dataInicial',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_data_final_filled_and_before_data_inicial()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'dataFinal' => now()->format('Y-m-d'),
            'horarios' => ['10:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'dataFinal',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_without_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'horarios' => [],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'horarios',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_horarios_without_array()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'horarios' => '10:00',
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'horarios',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_same_hours_in_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'horarios' => ['10:00', '10:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'horarios.*',
        ]);
    }

    /** @test */
    public function cannot_create_bloqueio_with_invalid_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->raw([
            'horarios' => ['10:00', '18:00'],
        ]);

        $this->get(route('sala.reuniao.bloqueio.criar'))->assertOk(); 
        $this->post(route('sala.reuniao.bloqueio.store'), $bloqueio)
        ->assertSessionHasErrors([
            'horarios',
        ]);
    }

    /** @test */
    public function can_edit_bloqueio()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00'];

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk();
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseHas('salas_reunioes_bloqueios', [
            'id' => 1,
            'dataInicial' => $bloqueio['dataInicial'],
            'dataFinal' => $bloqueio['dataFinal'],
            'horarios' => implode(',', $bloqueio['horarios']),
            'sala_reuniao_id' => $bloqueio['sala_reuniao_id']
        ]);
    }

    /** @test */
    public function can_edit_bloqueio_without_data_final()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00'];
        $bloqueio['dataFinal'] = null;

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk();
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseHas('salas_reunioes_bloqueios', [
            'id' => 1,
            'dataInicial' => $bloqueio['dataInicial'],
            'dataFinal' => $bloqueio['dataFinal'],
            'horarios' => implode(',', $bloqueio['horarios']),
            'sala_reuniao_id' => $bloqueio['sala_reuniao_id']
        ]);
    }

    /** @test */
    public function can_edit_bloqueio_with_sala_disabled()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create([
            'sala_reuniao_id' => factory('App\SalaReuniao')->states('desativa_ambos')->create()
        ])->toArray();
        $bloqueio['horarios'] = ['10:00'];
        $bloqueio['dataFinal'] = null;

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk();
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseHas('salas_reunioes_bloqueios', [
            'id' => 1,
            'dataInicial' => $bloqueio['dataInicial'],
            'dataFinal' => $bloqueio['dataFinal'],
            'horarios' => implode(',', $bloqueio['horarios']),
            'sala_reuniao_id' => $bloqueio['sala_reuniao_id']
        ]);
    }

    /** @test */
    public function log_is_generated_when_bloqueio_is_edited()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00'];

        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou *sala reunião / coworking bloqueio* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_edit_bloqueio_without_sala_reuniao_id()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['sala_reuniao_id'] = null;
        $bloqueio['horarios'] = ['10:00'];

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertStatus(404);
    }

    /** @test */
    public function cannot_edit_bloqueio_without_data_inicial()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00'];
        $bloqueio['dataInicial'] = null;

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertSessionHasErrors([
            'dataInicial',
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_data_inicial_before_or_equal_today()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00'];
        $bloqueio['dataInicial'] = now()->format('Y-m-d');

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertSessionHasErrors([
            'dataInicial',
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_data_final_filled_and_before_data_inicial()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00'];
        $bloqueio['dataFinal'] = now()->format('Y-m-d');

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertSessionHasErrors([
            'dataFinal',
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_without_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = [];

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertSessionHasErrors([
            'horarios',
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_horarios_without_array()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = '10:00';

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertSessionHasErrors([
            'horarios',
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_same_hours_in_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00', '10:00'];

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertSessionHasErrors([
            'horarios.*',
        ]);
    }

    /** @test */
    public function cannot_edit_bloqueio_with_invalid_horarios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create()->toArray();
        $bloqueio['horarios'] = ['10:00', '18:00'];

        $this->get(route('sala.reuniao.bloqueio.edit', $bloqueio['id']))->assertOk(); 
        $this->put(route('sala.reuniao.bloqueio.update', $bloqueio['id']), $bloqueio)
        ->assertSessionHasErrors([
            'horarios',
        ]);
    }

    /** @test */
    public function can_delete_bloqueio()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create();

        $this->delete(route('sala.reuniao.bloqueio.delete', $bloqueio->id))
        ->assertRedirect(route('sala.reuniao.bloqueio.lista'));

        $this->assertDatabaseMissing('salas_reunioes_bloqueios', [
            'idagendamentobloqueio' => $bloqueio->id,
            'dataInicial' => $bloqueio->dataInicial,
        ]);
    }

    /** @test */
    public function log_is_generated_when_bloqueio_is_deleted()
    {
        $user = $this->signInAsAdmin();
        $bloqueio = factory('App\SalaReuniaoBloqueio')->create();

        $this->delete(route('sala.reuniao.bloqueio.delete', $bloqueio->id));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') excluiu *sala reunião / coworking bloqueio* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function can_view_list_bloqueios()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create();

        $this->get(route('sala.reuniao.bloqueio.lista'))
        ->assertSee($bloqueio->sala->regional->regional)
        ->assertSee($bloqueio->mostraPeriodo())
        ->assertSee($bloqueio->horarios);
    }

    /** @test */
    public function can_view_list_bloqueios_with_data_final_null()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create([
            'dataFinal' => null
        ]);

        $this->get(route('sala.reuniao.bloqueio.lista'))
        ->assertSee($bloqueio->sala->regional->regional)
        ->assertSee('Tempo Indeterminado');
    }

    /** @test */
    public function can_view_button_editar_and_cancelar()
    {
        $user = $this->signInAsAdmin();

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create();

        $this->get(route('sala.reuniao.bloqueio.lista'))
        ->assertSee($bloqueio->sala->regional->regional)
        ->assertSee($bloqueio->mostraPeriodo())
        ->assertSee($bloqueio->horarios)
        ->assertSee('Editar')
        ->assertSee('Apagar');
    }

    /** @test */
    public function can_view_list_salas_when_create()
    {
        $user = $this->signInAsAdmin();

        $salas = factory('App\SalaReuniao', 5)->create();

        $this->get(route('sala.reuniao.bloqueio.criar'))
        ->assertSee($salas->get(0)->regional->regional)
        ->assertSee($salas->get(1)->regional->regional)
        ->assertSee($salas->get(2)->regional->regional)
        ->assertSee($salas->get(3)->regional->regional)
        ->assertSee($salas->get(4)->regional->regional);
    }

    /** @test */
    public function cannot_view_sala_disabled_when_create()
    {
        $user = $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->states('desativa_ambos')->create();

        $this->get(route('sala.reuniao.bloqueio.criar'))
        ->assertDontSeeText($sala->regional->regional);
    }

    /** @test */
    public function get_horarios_by_ajax()
    {
        $user = $this->signInAsAdmin();

        $sala = factory('App\SalaReuniao')->create();

        $this->get(route('sala.reuniao.bloqueio.horariosAjax', ['id' => $sala->id]))
        ->assertJson(
            $sala->getTodasHoras()
        );
    }

    /** @test */
    public function get_error_when_nonexistent_sala_by_ajax()
    {
        $user = $this->signInAsAdmin();

        $this->get(route('sala.reuniao.bloqueio.horariosAjax', ['id' => 5]))->assertStatus(500);
    }
}
