<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Permissao;
use Carbon\Carbon;
use App\AgendamentoSala;
use Illuminate\Support\Facades\Mail;
use App\Mail\AgendamentoSalaMail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Mail\InternoAgendamentoSalaMail;

class AgendamentoSalaTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * =======================================================================================================
     * TESTES AGENDADOS NO ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $agenda = factory('App\AgendamentoSala')->create();
        
        $this->get(route('sala.reuniao.agendados.index'))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.agendados.view', $agenda->id))->assertRedirect(route('login'));
        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'confirma']))->assertRedirect(route('login'));
        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'aceito']))->assertRedirect(route('login'));
        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'recusa']), [
            'justificativa_admin' => 'dfdfdfdfdfddfdfdfddfdfdf'
        ])->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.agendados.filtro'))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.agendados.busca'))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.agendados.create'))->assertRedirect(route('login'));
        $this->post(route('sala.reuniao.agendados.verifica.criar'))->assertRedirect(route('login'));
        $this->post(route('sala.reuniao.agendados.store'))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        $agenda = factory('App\AgendamentoSala')->create();

        $this->get(route('sala.reuniao.agendados.index'))->assertForbidden();
        $this->get(route('sala.reuniao.agendados.view', $agenda->id))->assertForbidden();
        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'confirma']))->assertForbidden();
        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'aceito']))->assertForbidden();
        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'recusa']), [
            'justificativa_admin' => 'dfdfdfdfdfddfdfdfddfdfdf'
        ])->assertForbidden();
        $this->get(route('sala.reuniao.agendados.filtro'))->assertForbidden();
        $this->get(route('sala.reuniao.agendados.busca'))->assertForbidden();
        $this->get(route('sala.reuniao.agendados.create'))->assertForbidden();
        $this->post(route('sala.reuniao.agendados.verifica.criar'), ['sala_reuniao_id' => 1])->assertForbidden();
        $criar = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'sala_reuniao_id' => 1
        ]);
        $this->post(route('sala.reuniao.agendados.store'), $criar)->assertForbidden();
    }

    /** @test */
    public function can_create_presencial()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw();

        $this->get(route('sala.reuniao.agendados.create'))->assertOk();

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertRedirect(route('sala.reuniao.agendados.busca', ['q' => AgendamentoSala::find(1)->protocolo]))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i> Agendamento com ID 1 com presença confirmada criado com sucesso!');

        $this->get(route('sala.reuniao.agendados.busca', ['q' => AgendamentoSala::find(1)->protocolo]))
        ->assertOk()
        ->assertSee('<br><small><strong>Agendado via: </strong> <i>Presencial</i></small>');
    }

    /** @test */
    public function log_is_generated_when_created_presencial()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw();

        $this->get(route('sala.reuniao.agendados.create'))->assertOk();

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertRedirect(route('sala.reuniao.agendados.busca', ['q' => AgendamentoSala::find(1)->protocolo]));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') criou com representante presencial *agendamento da sala de reunião / coworking* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_verify_presencial_invalid_format_cpf()
    {
        $user = $this->signInAsAdmin();
        
        $sala = factory('App\SalaReuniao')->states('desativa_ambos')->create();
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'sala_reuniao_id' => 1
        ]);

        $this->post(route('sala.reuniao.agendados.verifica.criar'), [
            'participantes_cpf' => '56983238010',
        ])
        ->assertJson([
            'participante_irregular' => '<strong>Formato do CPF inválido!</strong>'
        ])
        ->assertSessionMissing('participantes_verificados')
        ->assertSessionMissing('participantes_invalidos');

        $this->post(route('sala.reuniao.agendados.verifica.criar'), [
            'participantes_cpf' => '569.832.380.10',
        ])
        ->assertJson([
            'participante_irregular' => '<strong>Formato do CPF inválido!</strong>'
        ])
        ->assertSessionMissing('participantes_verificados')
        ->assertSessionMissing('participantes_invalidos');
    }

    /** @test */
    public function cannot_create_presencial_without_sala()
    {
        $user = $this->signInAsAdmin();
        
        $sala = factory('App\SalaReuniao')->states('desativa_ambos')->create();
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'sala_reuniao_id' => 1
        ]);

        $this->get(route('sala.reuniao.agendados.create'))
        ->assertRedirect(route('sala.reuniao.agendados.index'))
        ->assertSessionHas('message', 'Não possui salas ativas para criar agendamento!');

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors('sala_reuniao_id');
    }

    /** @test */
    public function cannot_create_presencial_without_cpf_cnpj_gerenti()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'cpf_cnpj' => formataCpfCnpj('76797171768')
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['nome', 'registro_core', 'email']);
    }

    /** @test */
    public function cannot_create_presencial_without_ativo_gerenti()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'cpf_cnpj' => formataCpfCnpj('22553674830')
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['registro_core']);
    }

    /** @test */
    public function cannot_create_presencial_without_tipo_sala()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'tipo_sala' => ''
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['tipo_sala']);
    }

    /** @test */
    public function cannot_create_presencial_with_invalid_value_tipo_sala()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'tipo_sala' => 'reunião'
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['tipo_sala']);
    }

    /** @test */
    public function cannot_create_presencial_without_dia()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'dia' => ''
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['dia']);
    }

    /** @test */
    public function cannot_create_presencial_with_dia_after_today()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'dia' => now()->addDay()->format('Y-m-d')
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['dia']);
    }

    /** @test */
    public function cannot_create_presencial_with_dia_weekend()
    {
        $user = $this->signInAsAdmin();
        
        $hj = Carbon::today();
        while(!$hj->isWeekend())
            $hj->subDay();

        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'dia' => $hj->format('Y-m-d')
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['dia']);
    }

    /** @test */
    public function cannot_create_presencial_without_periodo_entrada()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'periodo_entrada' => ''
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['periodo_entrada']);
    }

    /** @test */
    public function cannot_create_presencial_with_invalid_format_periodo_entrada()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'periodo_entrada' => '09::00'
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['periodo_entrada']);
    }

    /** @test */
    public function cannot_create_presencial_with_invalid_value_periodo_entrada()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'periodo_entrada' => '17:10'
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['periodo_entrada']);
    }

    /** @test */
    public function cannot_create_presencial_with_periodo_entrada_after_17_30()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'periodo_entrada' => '18:00'
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['periodo_entrada']);
    }

    /** @test */
    public function cannot_create_presencial_without_periodo_saida()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'periodo_saida' => ''
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['periodo_saida']);
    }

    /** @test */
    public function cannot_create_presencial_with_invalid_format_periodo_saida()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'periodo_saida' => '09::00'
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['periodo_saida']);
    }

    /** @test */
    public function cannot_create_presencial_with_invalid_value_periodo_saida()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'periodo_saida' => '19:00'
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['periodo_saida']);
    }

    /** @test */
    public function cannot_create_presencial_with_invalid_value_periodo_saida_before_or_equal_periodo_entrada()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'periodo_saida' => '09:00'
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['periodo_saida']);

        $agenda['periodo_saida'] = $agenda['periodo_entrada'];
        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['periodo_saida']);
    }

    /** @test */
    public function cannot_create_presencial_with_tipo_sala_reuniao_disabled()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_reuniao')->raw([
            'sala_reuniao_id' => factory('App\SalaReuniao')->states('desativa_reuniao')->create()
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['tipo_sala']);
    }

    /** @test */
    public function cannot_create_presencial_with_tipo_sala_coworking_disabled()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_coworking')->raw([
            'sala_reuniao_id' => factory('App\SalaReuniao')->states('desativa_coworking')->create()
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['tipo_sala']);
    }

    /** @test */
    public function cannot_create_presencial_without_participantes_cpf_with_tipo_sala_reuniao()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_reuniao')->raw([
            'participantes_cpf' => []
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['participantes_cpf']);
    }

    /** @test */
    public function cannot_create_presencial_with_invalid_cpf_participantes_cpf_with_tipo_sala_reuniao()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_reuniao')->raw();
        $agenda['participantes_cpf'][0] = '11111111111';

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['participantes_cpf.*']);
    }

    /** @test */
    public function cannot_create_presencial_not_distinct_participantes_cpf_with_tipo_sala_reuniao()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_reuniao')->raw();
        $agenda['participantes_cpf'][0] = $agenda['participantes_cpf'][1];

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['participantes_cpf.*']);
    }

    /** @test */
    public function cannot_create_presencial_cpf_cnpj_in_participantes_cpf_with_tipo_sala_reuniao()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_reuniao')->raw();
        $agenda['participantes_cpf'][0] = apenasNumeros($agenda['cpf_cnpj']);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['participantes_cpf.*']);
    }

    /** @test */
    public function cannot_create_presencial_without_participantes_nome_with_tipo_sala_reuniao()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_reuniao')->raw([
            'participantes_nome' => []
        ]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['participantes_nome']);
    }

    /** @test */
    public function cannot_create_presencial_with_participantes_nome_not_same_size_participantes_cpf_with_tipo_sala_reuniao()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_reuniao')->raw();
        unset($agenda['participantes_cpf'][1]);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['participantes_nome']);
    }

    /** @test */
    public function cannot_create_presencial_not_distinct_participantes_nome_with_tipo_sala_reuniao()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_reuniao')->raw();
        $agenda['participantes_nome'][0] = $agenda['participantes_nome'][1];

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['participantes_nome.*']);
    }

    /** @test */
    public function cannot_create_presencial_with_invalid_format_participantes_nome_with_tipo_sala_reuniao()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_reuniao')->raw();
        $agenda['participantes_nome'][0] = 'Nome com núm3ro';

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['participantes_nome.*']);
    }

    /** @test */
    public function cannot_create_presencial_with_participantes_nome_less_than_5_chars_with_tipo_sala_reuniao()
    {
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_reuniao')->raw();
        $agenda['participantes_nome'][0] = 'Test';

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['participantes_nome.*']);
    }

    /** @test */
    public function cannot_create_presencial_with_participantes_nome_greater_than_191_chars_with_tipo_sala_reuniao()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();
        
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_reuniao')->raw();
        $agenda['participantes_nome'][0] = $faker->sentence(300);

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertSessionHasErrors(['participantes_nome.*']);
    }

    /** @test */
    public function can_verify_when_create_presencial()
    {
        $user = $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $suspenso = factory('App\SuspensaoExcecao')->create();

        $this->get(route('sala.reuniao.agendados.create'))->assertOk();

        $this->post(route('sala.reuniao.agendados.verifica.criar'), ['cpf_cnpj' => formataCpfCnpj('11748345000144')])
        ->assertJson([
            "nomeGerenti" => "RC Teste 2",
            "registroGerenti" => formataRegistro("0000000002"),
            "emailGerenti" => "desenvolvimento@core-sp.org.br",
            "situacaoGerenti" => "Ativo, Em dia.",
        ]);

        $suspenso2 = factory('App\SuspensaoExcecao')->create([
            'cpf_cnpj' => '11748345000144'
        ]);

        $this->post(route('sala.reuniao.agendados.verifica.criar'), ['cpf_cnpj' => formataCpfCnpj('11748345000144')])
        ->assertJson([
            "nomeGerenti" => "RC Teste 2",
            "registroGerenti" => formataRegistro("0000000002"),
            "emailGerenti" => "desenvolvimento@core-sp.org.br",
            "situacaoGerenti" => "Ativo, Em dia.",
            "suspenso" => formataCpfCnpj('11748345000144'),
        ]);
        $suspenso2->delete();

        $this->post(route('sala.reuniao.agendados.verifica.criar'), ['cpf_cnpj' => formataCpfCnpj('76797171768')])
        ->assertJson([
            "nomeGerenti" => "",
            "registroGerenti" => "",
            "emailGerenti" => "",
            "situacaoGerenti" => "Não encontrado",
        ]);

        $this->post(route('sala.reuniao.agendados.verifica.criar'), ['sala_reuniao_id' => 1, 'tipo_sala' => 'reuniao'])
        ->assertJson([
            "total_participantes" => 2,
        ]);

        $this->post(route('sala.reuniao.agendados.verifica.criar'), ['sala_reuniao_id' => 1, 'tipo_sala' => 'coworking'])
        ->assertJson([
            "total_participantes" => 2,
        ]);

        $this->post(route('sala.reuniao.agendados.verifica.criar'), ['sala_reuniao_id' => 10, 'tipo_sala' => 'reuniao'])
        ->assertJson([
            "total_participantes" => 0,
        ]);

        $this->post(route('sala.reuniao.agendados.verifica.criar'), ['sala_reuniao_id' => 10, 'tipo_sala' => 'coworking'])
        ->assertJson([
            "total_participantes" => 0,
        ]);

        $this->post(route('sala.reuniao.agendados.verifica.criar'), ['participantes_cpf' => [$suspenso->fresh()->representante->cpf_cnpj]])
        ->assertJson([
            "suspenso" => "O seguinte participante está suspenso para novos agendamentos na área restrita do representante:<br><strong>862.943.730-85</strong>",
        ]);

        $this->post(route('sala.reuniao.agendados.verifica.criar'), ['participantes_cpf' => [formataCpfCnpj('11748345000144')]])
        ->assertJson([
            "suspenso" => "",
        ]);

        $this->post(route('sala.reuniao.agendados.verifica.criar'), ['participantes_cpf' => formataCpfCnpj('56983238010')])
        ->assertJson([
            'participante_irregular' => null
        ])
        ->assertSessionHas('participantes_verificados')
        ->assertSessionMissing('participantes_invalidos');
    }

    /** @test */
    public function remove_verify_gerenti_after_submit_presencial()
    {
        $user = $this->signInAsAdmin();
        $sala = factory('App\SalaReuniao')->create();
        $agenda = factory('App\AgendamentoSala')->states('presencial_request_reuniao')->raw();

        $this->get(route('sala.reuniao.agendados.create'))->assertOk();

        $this->post(route('sala.reuniao.agendados.verifica.criar'), ['participantes_cpf' => formataCpfCnpj('56983238010')])
        ->assertJson([
            'participante_irregular' => null
        ])
        ->assertSessionHas('participantes_verificados')
        ->assertSessionMissing('participantes_invalidos');

        $this->post(route('sala.reuniao.agendados.store'), $agenda)
        ->assertRedirect(route('sala.reuniao.agendados.busca', ['q' => AgendamentoSala::find(1)->protocolo]))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i> Agendamento com ID 1 com presença confirmada criado com sucesso!');

        $this->get(route('sala.reuniao.agendados.index'))
        ->assertSessionMissing('participantes_verificados')
        ->assertSessionMissing('participantes_invalidos');
    }

    /** @test */
    public function can_view_agendado()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->create();

        $this->get(route('sala.reuniao.agendados.index'))->assertOk();

        $this->get(route('sala.reuniao.agendados.view', $agenda->id))
        ->assertOk()
        ->assertSeeText($agenda->representante->nome)
        ->assertSeeText($agenda->representante->cpf_cnpj)
        ->assertSeeText($agenda->representante->registro_core)
        ->assertSeeText($agenda->representante->email)
        ->assertSee('<p class="mb-0">Agendamento criado via: <strong>Online</strong></p>')
        ->assertSeeText($agenda->getTipoSala())
        ->assertSeeText(onlyDate($agenda->dia))
        ->assertSeeText($agenda->sala->regional->regional);
    }

    /** @test */
    public function can_view_agendado_presencial()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('presencial')->create();
        $agenda->representante = $agenda->getRepresentante();

        $this->get(route('sala.reuniao.agendados.index'))->assertOk();

        $this->get(route('sala.reuniao.agendados.view', $agenda->id))
        ->assertOk()
        ->assertSeeText($agenda->representante->nome)
        ->assertSeeText($agenda->representante->cpf_cnpj)
        ->assertSeeText($agenda->representante->registro_core)
        ->assertSeeText($agenda->representante->email)
        ->assertSee('<p class="mb-0">Agendamento criado via: <strong>Presencial</strong></p>')
        ->assertSeeText($agenda->getTipoSala())
        ->assertSeeText(onlyDate($agenda->dia))
        ->assertSeeText($agenda->sala->regional->regional);
    }

    /** @test */
    public function can_view_participantes()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $this->get(route('sala.reuniao.agendados.index'))->assertOk();

        $cpfs = array_keys($agenda->getParticipantes());
        $nomes = array_values($agenda->getParticipantes());

        $this->get(route('sala.reuniao.agendados.view', $agenda->id))
        ->assertOk()
        ->assertSee('<h4>Participantes:</h4>')
        ->assertSee('<p class="mb-0">CPF: <strong>'.formataCpfCnpj($cpfs[0]).'</strong> | Nome: <strong>'.$nomes[0].'</strong></p>')
        ->assertSee('<p class="mb-0">CPF: <strong>'.formataCpfCnpj($cpfs[1]).'</strong> | Nome: <strong>'.$nomes[1].'</strong></p>');
    }

    /** @test */
    public function can_view_justificado()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('justificado')->create();

        $this->get(route('sala.reuniao.agendados.index'))->assertOk();

        $this->get(route('sala.reuniao.agendados.view', $agenda->id))
        ->assertOk()
        ->assertSee('<h4>Justificativa do Representante:</h4>')
        ->assertSee('<p class="mb-0">'.$agenda->justificativa.'</p>')
        ->assertSee('<button type="submit" class="btn btn-primary">Não Compareceu Justificado</button>')
        ->assertSee('<label for="justificativa_admin">Insira o motivo:</label>');
    }

    /** @test */
    public function can_view_justificado_with_file()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('justificado_com_anexo')->create();

        $this->get(route('sala.reuniao.agendados.index'))->assertOk();

        $this->get(route('sala.reuniao.agendados.view', $agenda->id))
        ->assertOk()
        ->assertSee('<h4>Justificativa do Representante:</h4>')
        ->assertSee('<p class="mb-0">'.$agenda->justificativa.'</p>')
        ->assertSee('<a href="'.route('sala.reuniao.agendados.view', ['id' => $agenda->id, 'anexo' => $agenda->anexo]).'"')
        ->assertSeeText('Comprovante')
        ->assertSee('<button type="submit" class="btn btn-primary">Não Compareceu Justificado</button>')
        ->assertSee('<label for="justificativa_admin">Insira o motivo:</label>');
    }

    /** @test */
    public function can_view_justificado_admin()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('recusado')->create();

        $this->get(route('sala.reuniao.agendados.index'))->assertOk();

        $this->get(route('sala.reuniao.agendados.view', $agenda->id))
        ->assertOk()
        ->assertSee('<h4 class="text-danger">Justificativa do(a) atendente <em>'.$user->nome.'</em>:</h4>')
        ->assertSee('<p class="mb-0">'.$agenda->justificativa_admin.'</p>')
        ->assertDontSee('<button type="submit" class="btn btn-primary">Não Compareceu Justificado</button>')
        ->assertDontSee('<label for="justificativa_admin">Insira o motivo:</label>');
    }

    /** @test */
    public function can_view_list()
    {
        $user = $this->signInAsAdmin();
        $reuniao = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d'),
            'sala_reuniao_id' => factory('App\SalaReuniao')->create([
                'idregional' => $user->idregional
            ])
        ]);
        $coworking = factory('App\AgendamentoSala')->create([
            'dia' => now()->format('Y-m-d'),
            'idrepresentante' => $reuniao->idrepresentante,
            'sala_reuniao_id' => $reuniao->sala_reuniao_id,
            'periodo' => 'tarde'
        ]);

        $this->get(route('sala.reuniao.agendados.index'))
        ->assertOk()
        ->assertSeeText($reuniao->protocolo)
        ->assertSeeText($coworking->protocolo)
        ->assertSee($reuniao->representante->cpf_cnpj.'<br><small><strong>Agendado via: </strong> <i>Online</i></small>')
        ->assertSee($coworking->representante->cpf_cnpj.'<br><small><strong>Agendado via: </strong> <i>Online</i></small>')
        ->assertSee($reuniao->getTipoSalaHTML())
        ->assertSee($coworking->getTipoSalaHTML())
        ->assertSeeText($reuniao->sala->regional->regional)
        ->assertSeeText($coworking->sala->regional->regional)
        ->assertSeeText(formataData($reuniao->updated_at));
    }

    /** @test */
    public function can_view_list_with_presencial()
    {
        $user = $this->signInAsAdmin();
        $reuniao = factory('App\AgendamentoSala')->states('reuniao', 'presencial')->create([
            'dia' => now()->format('Y-m-d'),
            'sala_reuniao_id' => factory('App\SalaReuniao')->create([
                'idregional' => $user->idregional
            ])
        ]);
        $reuniao->representante = $reuniao->getRepresentante();

        $coworking = factory('App\AgendamentoSala')->states('presencial')->create([
            'dia' => now()->format('Y-m-d'),
            'idrepresentante' => $reuniao->idrepresentante,
            'sala_reuniao_id' => $reuniao->sala_reuniao_id,
            'periodo' => 'tarde'
        ]);
        $coworking->representante = $coworking->getRepresentante();

        $this->get(route('sala.reuniao.agendados.index'))
        ->assertOk()
        ->assertSeeText($reuniao->protocolo)
        ->assertSeeText($coworking->protocolo)
        ->assertSee($reuniao->representante->cpf_cnpj.'<br><small><strong>Agendado via: </strong> <i>Presencial</i></small>')
        ->assertSee($coworking->representante->cpf_cnpj.'<br><small><strong>Agendado via: </strong> <i>Presencial</i></small>')
        ->assertSee($reuniao->getTipoSalaHTML())
        ->assertSee($coworking->getTipoSalaHTML())
        ->assertSeeText($reuniao->sala->regional->regional)
        ->assertSeeText($coworking->sala->regional->regional)
        ->assertSeeText(formataData($reuniao->updated_at));
    }

    /** @test */
    public function can_to_confirm()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->create([
            'dia' => now()->format('Y-m-d'),
        ]);

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'confirma']))
        ->assertRedirect(route('sala.reuniao.agendados.index'));

        $this->get(route('sala.reuniao.agendados.index'))
        ->assertSee('<i class="icon fa fa-check"></i>Status do agendamento com o código '.$agenda->id.' foi editado com sucesso!');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'coworking',
            'status' => AgendamentoSala::STATUS_COMPARECEU,
        ]);
    }

    /** @test */
    public function log_is_generated_when_confirmed()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->create([
            'dia' => now()->format('Y-m-d'),
        ]);

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'confirma']));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') atualizou status para '. AgendamentoSala::STATUS_COMPARECEU .' *agendamento da sala de reunião / coworking* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function can_to_confirm_with_dia_before_today()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->create([
            'dia' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'confirma']))
        ->assertRedirect(route('sala.reuniao.agendados.index'));

        $this->get(route('sala.reuniao.agendados.index'))
        ->assertSee('<i class="icon fa fa-check"></i>Status do agendamento com o código '.$agenda->id.' foi editado com sucesso!');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'coworking',
            'status' => AgendamentoSala::STATUS_COMPARECEU,
        ]);
    }

    /** @test */
    public function cannot_to_confirm_with_dia_after_today()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->create();

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'confirma']))
        ->assertRedirect(route('sala.reuniao.agendados.index'));

        $this->get(route('sala.reuniao.agendados.index'))
        ->assertSee('<i class="icon fa fa-times"></i> Não pode atualizar o agendamento com ID '.$agenda->id.' devido ao status ou dia.');

        $this->assertDatabaseMissing('agendamentos_salas', [
            'tipo_sala' => 'coworking',
            'status' => AgendamentoSala::STATUS_COMPARECEU,
        ]);
    }

    /** @test */
    public function cannot_to_confirm_without_status_null()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->create([
            'status' => AgendamentoSala::STATUS_CANCELADO,
        ]);

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'confirma']))
        ->assertRedirect(route('sala.reuniao.agendados.index'));

        $this->get(route('sala.reuniao.agendados.index'))
        ->assertSee('<i class="icon fa fa-times"></i> Não pode atualizar o agendamento com ID '.$agenda->id.' devido ao status ou dia.');

        $this->assertDatabaseMissing('agendamentos_salas', [
            'tipo_sala' => 'coworking',
            'status' => AgendamentoSala::STATUS_COMPARECEU,
        ]);
    }

    /** @test */
    public function can_to_accept()
    {
        Mail::fake();

        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('justificado')->create();

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'aceito']))
        ->assertRedirect(route('sala.reuniao.agendados.index'));

        Mail::assertQueued(AgendamentoSalaMail::class);

        $this->get(route('sala.reuniao.agendados.index'))
        ->assertSee('<i class="icon fa fa-check"></i>Status do agendamento com o código '.$agenda->id.' foi editado com sucesso!');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'coworking',
            'status' => AgendamentoSala::STATUS_JUSTIFICADO,
        ]);
    }

    /** @test */
    public function log_is_generated_when_accepted()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('justificado')->create();

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'aceito']));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') atualizou status para '. AgendamentoSala::STATUS_JUSTIFICADO .' *agendamento da sala de reunião / coworking* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_to_accept_without_status_justificativa_enviada()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->create([
            'dia' => now()->format('Y-m-d'),
        ]);

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'aceito']))
        ->assertRedirect(route('sala.reuniao.agendados.index'));

        $this->get(route('sala.reuniao.agendados.index'))
        ->assertSee('<i class="icon fa fa-times"></i> Não pode atualizar o agendamento com ID '.$agenda->id.' devido ao status.');

        $this->assertDatabaseMissing('agendamentos_salas', [
            'tipo_sala' => 'coworking',
            'status' => AgendamentoSala::STATUS_JUSTIFICADO,
        ]);
    }

    /** @test */
    public function cannot_to_accept_before_dia()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('justificado')->create([
            'dia' => now()->addDay()->format('Y-m-d'),
        ]);

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'aceito']))
        ->assertRedirect(route('sala.reuniao.agendados.index'));

        $this->get(route('sala.reuniao.agendados.index'))
        ->assertSee('<i class="icon fa fa-times"></i> Não pode atualizar o agendamento com ID '.$agenda->id.' devido ao status ou dia.');

        $this->assertDatabaseMissing('agendamentos_salas', [
            'tipo_sala' => 'coworking',
            'status' => AgendamentoSala::STATUS_JUSTIFICADO,
        ]);
    }

    /** @test */
    public function can_to_refuse()
    {
        Mail::fake();

        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('justificado')->create();

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'recusa']), [
            'justificativa_admin' => 'fgfgffgffgfffggfgfg'
        ])
        ->assertRedirect(route('sala.reuniao.agendados.index'));

        Mail::assertQueued(AgendamentoSalaMail::class);

        $this->get(route('sala.reuniao.agendados.index'))
        ->assertSee('<i class="icon fa fa-check"></i>Status do agendamento com o código '.$agenda->id.' foi editado com sucesso!');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'coworking',
            'status' => AgendamentoSala::STATUS_NAO_COMPARECEU,
        ]);
    }

    /** @test */
    public function log_is_generated_when_refused()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('justificado')->create();

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'recusa']), [
            'justificativa_admin' => 'fgfgffgffgfffggfgfg'
        ]);

        $log = explode(PHP_EOL, tailCustom(storage_path($this->pathLogInterno()), 2));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') atualizou status para '. AgendamentoSala::STATUS_NAO_COMPARECEU .' *agendamento da sala de reunião / coworking* (id: 1)';
        $this->assertStringContainsString($txt, $log[0]);
    }

    /** @test */
    public function cannot_to_refuse_without_justificativa_admin()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('justificado')->create();

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'recusa']), [
            'justificativa_admin' => null
        ])
        ->assertSessionHasErrors([
            'justificativa_admin'
        ]);
    }

    /** @test */
    public function cannot_to_refuse_with_justificativa_admin_less_than_10_chars()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('justificado')->create();

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'recusa']), [
            'justificativa_admin' => 'asdertghy'
        ])
        ->assertSessionHasErrors([
            'justificativa_admin'
        ]);
    }

    /** @test */
    public function cannot_to_refuse_with_justificativa_admin_greater_than_1000_chars()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('justificado')->create();

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'recusa']), [
            'justificativa_admin' => $faker->sentence(2500)
        ])
        ->assertSessionHasErrors([
            'justificativa_admin'
        ]);
    }

    /** @test */
    public function cannot_to_refuse_without_status_justificativa_enviada()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('justificado')->create([
            'status' => null
        ]);

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'recusa']), [
            'justificativa_admin' => 'sderttppfkvmvb'
        ])
        ->assertRedirect(route('sala.reuniao.agendados.index'));

        $this->get(route('sala.reuniao.agendados.index'))
        ->assertSee('<i class="icon fa fa-times"></i> Não pode atualizar o agendamento com ID '.$agenda->id.' devido ao status.');

        $this->assertDatabaseMissing('agendamentos_salas', [
            'tipo_sala' => 'coworking',
            'status' => AgendamentoSala::STATUS_NAO_COMPARECEU,
        ]);
    }

    /** @test */
    public function cannot_to_refuse_before_dia()
    {
        $user = $this->signInAsAdmin();
        $agenda = factory('App\AgendamentoSala')->states('justificado')->create([
            'dia' => now()->addDay()->format('Y-m-d'),
        ]);

        $this->put(route('sala.reuniao.agendados.update', [$agenda->id, 'recusa']), [
            'justificativa_admin' => 'sderttppfkvmvb'
        ])
        ->assertRedirect(route('sala.reuniao.agendados.index'));

        $this->get(route('sala.reuniao.agendados.index'))
        ->assertSee('<i class="icon fa fa-times"></i> Não pode atualizar o agendamento com ID '.$agenda->id.' devido ao status ou dia.');

        $this->assertDatabaseMissing('agendamentos_salas', [
            'tipo_sala' => 'coworking',
            'status' => AgendamentoSala::STATUS_NAO_COMPARECEU,
        ]);
    }

    /** @test */
    public function can_view_all_filtros()
    {
        $user = $this->signInAsAdmin();

        $this->get(route('sala.reuniao.agendados.filtro'))
        ->assertSeeText('Seccional')
        ->assertSeeText('Status')
        ->assertSeeText('Sala')
        ->assertSeeText('De')
        ->assertSeeText('Até');
    }

    /** @test */
    public function atendente_and_gerente_seccional_cannot_view_all_filtros()
    {
        $atendente = factory('App\Perfil')->create([
            'idperfil' => 8,
            'nome' => 'Atendimento'
        ]);

        $gerente = factory('App\Perfil')->create([
            'idperfil' => 21,
            'nome' => 'Gerente Seccionais'
        ]);

        $user = factory('App\User')->create([
            'idperfil' => $atendente->idperfil
        ]);

        $agenda = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => factory('App\SalaReuniao')->create([
                'idregional' => $user->idregional
            ]),
            'dia' => date('Y-m-d'),
        ]);

        $this->signIn($user);
        Permissao::find(27)->update(['perfis' => '1,8']);

        $this->get(route('sala.reuniao.agendados.filtro'))
        ->assertDontSeeText('Seccional')
        ->assertSeeText('Status')
        ->assertSeeText('Sala')
        ->assertSeeText('De')
        ->assertSeeText('Até');

        $user2 = factory('App\User')->create([
            'idperfil' => $gerente->idperfil
        ]);

        $agenda = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => factory('App\SalaReuniao')->create([
                'idregional' => $user2->idregional
            ]),
            'dia' => date('Y-m-d'),
            'idrepresentante' => $agenda->idrepresentante
        ]);

        $this->signIn($user2);
        Permissao::find(27)->update(['perfis' => '1,21']);

        $this->get(route('sala.reuniao.agendados.filtro'))
        ->assertDontSeeText('Seccional')
        ->assertSeeText('Status')
        ->assertSeeText('Sala')
        ->assertSeeText('De')
        ->assertSeeText('Até');
    }

    /** @test */
    public function agendamentos_filter()
    {
        // Criando usuário Admin. A Regional Sede (idregional = 1) é criada junta
        $admin = $this->signInAsAdmin();

        // Criando regional seccional (idregional != 1)
        $regional_seccional = factory('App\Regional')->create([
            'idregional' => 2,
            'regional' => 'Seccional'
        ]);

        // Criando Agendamento pendente no passado na sede
        $agendamento_sede_pendente = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => factory('App\SalaReuniao')->create([
                'idregional' => 1
            ]),
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'protocolo' => 'RC-AGE-00000001'
        ]);

        // Criando Agendamento concluído no passado na sede
        $agendamento_sede_concluido = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => 1,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'protocolo' => 'RC-AGE-00000002',
            'status' => AgendamentoSala::STATUS_COMPARECEU,
            'idusuario' => 1,
            'idrepresentante' => $agendamento_sede_pendente->idrepresentante
        ]);

        // Criando Agendamento pendente no futuro na sede
        $agendamento_sede_pendente_futuro = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => 1,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'protocolo' => 'RC-AGE-00000003',
            'idrepresentante' => $agendamento_sede_pendente->idrepresentante
        ]);
        
        // Criando Agendamento pendente no passado na seccional
        $agendamento_seccional_pendente = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => factory('App\SalaReuniao')->create([
                'idregional' => 2
            ]),
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'protocolo' => 'RC-AGE-00000004',
            'idrepresentante' => $agendamento_sede_pendente->idrepresentante
        ]);

        // Criando Agendamento concluído no passado na seccional
        $agendamento_seccional_concluido = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $regional_seccional->idregional,
            'dia' => date('Y-m-d', strtotime('-1 day')),
            'protocolo' => 'RC-AGE-00000005',
            'status' => AgendamentoSala::STATUS_NAO_COMPARECEU,
            'idrepresentante' => $agendamento_sede_pendente->idrepresentante
        ]);

        // Criando Agendamento pendente no futuro na seccional
        $agendamento_seccional_pendente_futuro = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $regional_seccional->idregional,
            'dia' => date('Y-m-d', strtotime('+1 day')),
            'protocolo' => 'RC-AGE-00000006',
            'idrepresentante' => $agendamento_sede_pendente->idrepresentante
        ]);

        // Listando todos os agendamentos (qualquer regional, status e datas cobrindos todos os agendamentos)
        $this->get(route('sala.reuniao.agendados.filtro', [
            'regional' => 'Todas', 
            'status' => 'Qualquer', 
            'datemin' => date('Y-m-d', strtotime('-1 day')), 
            'datemax' => date('Y-m-d', strtotime('+1 day'))
        ]))->assertSeeText('RC-AGE-00000001') 
            ->assertSeeText('RC-AGE-00000002') 
            ->assertSeeText('RC-AGE-00000003') 
            ->assertSeeText('RC-AGE-00000004') 
            ->assertSeeText('RC-AGE-00000005')
            ->assertSeeText('RC-AGE-00000006');

        // Listando todos os agendamentos (qualquer regional, sem status e datas cobrindos todos os agendamentos)
        $this->get(route('sala.reuniao.agendados.filtro', [
            'regional' => 'Todas', 
            'status' => 'Sem status', 
            'datemin' => date('Y-m-d', strtotime('-1 day')), 
            'datemax' => date('Y-m-d', strtotime('+1 day'))
        ]))->assertSeeText('RC-AGE-00000001') 
            ->assertDontSeeText('RC-AGE-00000002') 
            ->assertSeeText('RC-AGE-00000003') 
            ->assertSeeText('RC-AGE-00000004') 
            ->assertDontSeeText('RC-AGE-00000005')
            ->assertSeeText('RC-AGE-00000006');

        // Listando todos os agendamentos da Sede (qualquer status e datas cobrindos todos os agendamentos)
        $this->get(route('sala.reuniao.agendados.filtro', [
            'regional' => 1, 
            'status' => 'Qualquer', 
            'datemin' => date('Y-m-d', strtotime('-1 day')), 
            'datemax' => date('Y-m-d', strtotime('+1 day'))
        ]))
            ->assertSeeText('RC-AGE-00000001') 
            ->assertSeeText('RC-AGE-00000002') 
            ->assertSeeText('RC-AGE-00000003') 
            ->assertDontSeeText('RC-AGE-00000004') 
            ->assertDontSeeText('RC-AGE-00000005')
            ->assertDontSeeText('RC-AGE-00000006');
        
        // Listando todos os agendamentos da Sede (sem status e datas cobrindos todos os agendamentos)
        $this->get(route('sala.reuniao.agendados.filtro', [
            'regional' => 1, 
            'status' => 'Sem status', 
            'datemin' => date('Y-m-d', strtotime('-1 day')), 
            'datemax' => date('Y-m-d', strtotime('+1 day'))
        ]))
            ->assertSeeText('RC-AGE-00000001') 
            ->assertDontSeeText('RC-AGE-00000002') 
            ->assertSeeText('RC-AGE-00000003') 
            ->assertDontSeeText('RC-AGE-00000004') 
            ->assertDontSeeText('RC-AGE-00000005')
            ->assertDontSeeText('RC-AGE-00000006');

        // Listando apenas os agendamentos com status "Compareceu" da Sede (datas cobrindos todos os agendamentos)
        $this->get(route('sala.reuniao.agendados.filtro', [
            'regional' => 1, 
            'status' => AgendamentoSala::STATUS_COMPARECEU, 
            'datemin' => date('Y-m-d', strtotime('-1 day')), 
            'datemax' => date('Y-m-d', strtotime('+1 day'))
        ]))
            ->assertDontSeeText('RC-AGE-00000001') 
            ->assertSeeText('RC-AGE-00000002') 
            ->assertDontSeeText('RC-AGE-00000003') 
            ->assertDontSeeText('RC-AGE-00000004') 
            ->assertDontSeeText('RC-AGE-00000005')
            ->assertDontSeeText('RC-AGE-00000006');

        // Listando apenas agendamentos da Sede do dia -1
        $this->get(route('sala.reuniao.agendados.filtro', [
            'regional' => 1, 
            'status' => 'Qualquer', 
            'datemin' => date('Y-m-d', strtotime('-1 day')), 
            'datemax' => date('Y-m-d', strtotime('-1 day'))
        ]))
            ->assertSeeText('RC-AGE-00000001') 
            ->assertSeeText('RC-AGE-00000002') 
            ->assertDontSeeText('RC-AGE-00000003') 
            ->assertDontSeeText('RC-AGE-00000004') 
            ->assertDontSeeText('RC-AGE-00000005')
            ->assertDontSeeText('RC-AGE-00000006');

        // Listando nenhum o agendamentos da Sede por causa de data sem agendamento
        $this->get(route('sala.reuniao.agendados.filtro', [
            'regional' => 1, 
            'status' => AgendamentoSala::STATUS_COMPARECEU, 
            'datemin' => date('Y-m-d'), 
            'datemax' => date('Y-m-d')
        ]))
            ->assertDontSeeText('RC-AGE-00000001') 
            ->assertDontSeeText('RC-AGE-00000002') 
            ->assertDontSeeText('RC-AGE-00000003') 
            ->assertDontSeeText('RC-AGE-00000004') 
            ->assertDontSeeText('RC-AGE-00000005')
            ->assertDontSeeText('RC-AGE-00000006');


        // Listando todos os agendamentos da Seccional (qualquer status e datas cobrindos todos os agendamentos)
        $this->get(route('sala.reuniao.agendados.filtro', [
            'regional' => $regional_seccional->idregional, 
            'status' => 'Qualquer', 
            'datemin' => date('Y-m-d', strtotime('-1 day')), 
            'datemax' => date('Y-m-d', strtotime('+1 day'))
        ]))
            ->assertDontSeeText('RC-AGE-00000001') 
            ->assertDontSeeText('RC-AGE-00000002') 
            ->assertDontSeeText('RC-AGE-00000003') 
            ->assertSeeText('RC-AGE-00000004') 
            ->assertSeeText('RC-AGE-00000005')
            ->assertSeeText('RC-AGE-00000006');

        // Listando apenas os agendamentos com status "Não Compareceu" da Seccional (datas cobrindos todos os agendamentos)
        $this->get(route('sala.reuniao.agendados.filtro', [
            'regional' => $regional_seccional->idregional,
            'status' => AgendamentoSala::STATUS_NAO_COMPARECEU, 
            'datemin' => date('Y-m-d', strtotime('-1 day')), 
            'datemax' => date('Y-m-d', strtotime('+1 day'))
        ]))
            ->assertDontSeeText('RC-AGE-00000001') 
            ->assertDontSeeText('RC-AGE-00000002') 
            ->assertDontSeeText('RC-AGE-00000003') 
            ->assertDontSeeText('RC-AGE-00000004') 
            ->assertSeeText('RC-AGE-00000005')
            ->assertDontSeeText('RC-AGE-00000006');

        // Listando apenas agendamentos da Seccional do dia +1
        $this->get(route('sala.reuniao.agendados.filtro', [
            'regional' => $regional_seccional->idregional,
            'status' => 'Qualquer', 
            'datemin' => date('Y-m-d', strtotime('+1 day')), 
            'datemax' => date('Y-m-d', strtotime('+1 day'))
        ]))
            ->assertDontSeeText('RC-AGE-00000001') 
            ->assertDontSeeText('RC-AGE-00000002') 
            ->assertDontSeeText('RC-AGE-00000003') 
            ->assertDontSeeText('RC-AGE-00000004') 
            ->assertDontSeeText('RC-AGE-00000005')
            ->assertSeeText('RC-AGE-00000006');

        // Listando nenhum o agendamentos da Seccional por causa de data sem agendamento
        $this->get(route('sala.reuniao.agendados.filtro', [
            'regional' => $regional_seccional->idregional,
            'status' => AgendamentoSala::STATUS_COMPARECEU, 
            'datemin' => date('Y-m-d'), 
            'datemax' => date('Y-m-d')
        ]))
            ->assertDontSeeText('RC-AGE-00000001') 
            ->assertDontSeeText('RC-AGE-00000002') 
            ->assertDontSeeText('RC-AGE-00000003') 
            ->assertDontSeeText('RC-AGE-00000004') 
            ->assertDontSeeText('RC-AGE-00000005')
            ->assertDontSeeText('RC-AGE-00000006');

        $this->get(route('sala.reuniao.agendados.filtro', [
            'regional' => $regional_seccional->idregional,
            'status' => AgendamentoSala::STATUS_COMPARECEU, 
            'datemin' => now()->addDay()->format('Y-m-d'), 
            'datemax' => date('Y-m-d')
        ]))
            ->assertDontSeeText('RC-AGE-00000001') 
            ->assertDontSeeText('RC-AGE-00000002') 
            ->assertDontSeeText('RC-AGE-00000003') 
            ->assertDontSeeText('RC-AGE-00000004') 
            ->assertDontSeeText('RC-AGE-00000005')
            ->assertDontSeeText('RC-AGE-00000006');
    }

    /** @test */
    public function search_criteria_for_agendamento_for_profiles_other_than_atendente_and_gerSeccional()
    {
        $user = $this->signInAsAdmin();

        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => factory('App\SalaReuniao')->create([
                'idregional' => $user->idregional,
            ])
        ]);

        $agendamento1 = factory('App\AgendamentoSala')->states('presencial')->create([
            'sala_reuniao_id' => 1
        ]);

        $this->get(route('sala.reuniao.agendados.busca', ['q' => $agendamento->id]))
            ->assertSeeText($agendamento->protocolo); 

        $this->get(route('sala.reuniao.agendados.busca', ['q' => $agendamento->representante->cpf_cnpj]))
            ->assertSee($agendamento1->getRepresentante()->cpf_cnpj.'<br><small><strong>Agendado via: </strong> <i>Presencial</i></small>')
            ->assertSee($agendamento->getRepresentante()->cpf_cnpj.'<br><small><strong>Agendado via: </strong> <i>Online</i></small>')
            ->assertSeeText($agendamento1->protocolo)
            ->assertSeeText($agendamento->protocolo); 

        $this->get(route('sala.reuniao.agendados.busca', ['q' => $agendamento->protocolo]))
            ->assertSeeText($agendamento->protocolo); 
            
        $this->get(route('sala.reuniao.agendados.busca', ['q' => 'Erro busca']))
            ->assertDontSeeText($agendamento->protocolo);
    }

    /** @test */
    public function search_criteria_for_agendamento_atendente()
    {
        $perfil = factory('App\Perfil')->create([
            'idperfil' => 8
        ]);
        $user = factory('App\User')->create([
            'idperfil' => $perfil->idperfil
        ]);

        $user = $this->signIn($user);
        Permissao::find(27)->update(['perfis' => '1,8']);

        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => factory('App\SalaReuniao')->create([
                'idregional' => $user->idregional,
            ])
        ]);

        $agendamento2 = factory('App\AgendamentoSala')->create([
            'protocolo' => 'RC-AGE-YYYYYYYY',
            'idrepresentante' => $agendamento->idrepresentante
        ]);

        $this->get(route('sala.reuniao.agendados.busca', ['q' => $agendamento->id]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo);

        $this->get(route('sala.reuniao.agendados.busca', ['q' => $agendamento->representante->cpf_cnpj]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo); 

        $this->get(route('sala.reuniao.agendados.busca', ['q' => $agendamento->protocolo]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo);
            
        $this->get(route('sala.reuniao.agendados.busca', ['q' => 'Critério de busca sem resultado']))
            ->assertDontSeeText($agendamento->protocolo);
    }

    /** @test */
    public function search_criteria_for_agendamento_gerente_seccional()
    {
        $perfil = factory('App\Perfil')->create([
            'idperfil' => 21
        ]);
        $user = factory('App\User')->create([
            'idperfil' => $perfil->idperfil
        ]);

        $user = $this->signIn($user);
        Permissao::find(27)->update(['perfis' => '1,21']);

        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => factory('App\SalaReuniao')->create([
                'idregional' => $user->idregional,
            ])
        ]);

        $agendamento2 = factory('App\AgendamentoSala')->create([
            'protocolo' => 'RC-AGE-YYYYYYYY',
            'idrepresentante' => $agendamento->idrepresentante
        ]);

        $this->get(route('sala.reuniao.agendados.busca', ['q' => $agendamento->id]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo);

        $this->get(route('sala.reuniao.agendados.busca', ['q' => $agendamento->representante->cpf_cnpj]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo); 

        $this->get(route('sala.reuniao.agendados.busca', ['q' => $agendamento->protocolo]))
            ->assertSeeText($agendamento->protocolo)
            ->assertDontSeeText($agendamento2->protocolo);
            
        $this->get(route('sala.reuniao.agendados.busca', ['q' => 'Critério de busca sem resultado']))
            ->assertDontSeeText($agendamento->protocolo);
    }

    /** 
     * =======================================================================================================
     * TESTES AGENDAMENTO NA ÁREA DO RC
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_cannot_access_routes_agendamento()
    {
        $this->get(route('representante.agendar.inserir.view'))->assertRedirect(route('representante.login'));
        $this->get(route('representante.agendar.inserir.view', 'agendar'))->assertRedirect(route('representante.login'));

        $agendamento = factory('App\AgendamentoSala')->create();

        $this->get(route('representante.agendar.inserir.view', ['editar', $agendamento->id]))->assertRedirect(route('representante.login'));
        $this->get(route('representante.agendar.inserir.view', ['cancelar', $agendamento->id]))->assertRedirect(route('representante.login'));
        $this->get(route('representante.agendar.inserir.view', ['justificar', $agendamento->id]))->assertRedirect(route('representante.login'));

        $this->post(route('representante.agendar.inserir.post', 'agendar'))->assertRedirect(route('representante.login'));
        $this->post(route('representante.agendar.inserir.post', 'verificar'))->assertRedirect(route('representante.login'));
        $this->put(route('representante.agendar.inserir.put', ['editar', $agendamento->id]))->assertRedirect(route('representante.login'));
        $this->put(route('representante.agendar.inserir.put', ['cancelar', $agendamento->id]))->assertRedirect(route('representante.login'));
        $this->put(route('representante.agendar.inserir.put', ['justificar', $agendamento->id]))->assertRedirect(route('representante.login'));
    }

    /** @test */
    public function view_aba_agendar_sala()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('representante.dashboard'))
        ->assertOk()
        ->assertSee('<i class="fas fa-business-time"></i>&nbsp;&nbsp;Agendar Salas&nbsp;&nbsp;&nbsp;');
    }

    /** @test */
    public function cannot_view_button_agendar_sala_without_salas()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('representante.agendar.inserir.view'))
        ->assertOk()
        ->assertSee('<p><i class="fas fa-info-circle text-primary"></i> <b><em>No momento não há salas disponíveis para novos agendamentos.</em></b></p>')
        ->assertDontSee('<a href="'. route('representante.agendar.inserir.view', 'agendar') .'" class="btn btn-primary link-nostyle branco">Agendar sala</a>');
    }

    /** @test */
    public function view_button_agendar_sala_with_salas()
    {
        factory('App\SalaReuniao')->create();
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('representante.agendar.inserir.view'))
        ->assertOk()
        ->assertDontSee('<p><i class="fas fa-info-circle text-primary"></i> <b><em>No momento não há salas disponíveis para novos agendamentos.</em></b></p>')
        ->assertSee('<a href="'. route('representante.agendar.inserir.view', 'agendar') .'" class="btn btn-primary link-nostyle branco">Agendar sala</a>');
    }

    /** @test */
    public function view_message_erro_when_agendar_sala_without_situacao_em_dia()
    {
        $representante = factory('App\Representante')->states('irregular')->create();
        $this->actingAs($representante, 'representante');

        $amanha = Carbon::tomorrow();
        while($amanha->isWeekend())
            $amanha->addDay();

        $dados = factory('App\SalaReuniao')->create();

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-exclamation-triangle"></i>&nbsp;Para liberar o seu agendamento entre em contato com o setor de atendimento da <a href="'.route('regionais.siteGrid').'" target="_blank">seccional</a> de interesse.');

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking', 'sala_reuniao_id' => $dados->id, 'dia' => $amanha->format('d/m/Y'), 'periodo' => '09:00 - 12:00', 'aceite' => 'on'
        ])->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-exclamation-triangle"></i>&nbsp;Para liberar o seu agendamento entre em contato com o setor de atendimento da <a href="'.route('regionais.siteGrid').'" target="_blank">seccional</a> de interesse.');
    }

    /** @test */
    public function view_salas_in_form_agendar_sala()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $salas = factory('App\SalaReuniao', 3)->create();

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSeeText($salas[0]->regional->regional)
        ->assertSeeText($salas[1]->regional->regional)
        ->assertSeeText($salas[2]->regional->regional);
    }

    /** @test */
    public function view_salas_enabled()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $salas = factory('App\SalaReuniao', 3)->create();
        $ids = $salas->sortBy('regional.regional')->pluck('id')->all();

        $this->get(route('sala.reuniao.regionais.ativas', 'coworking'))
        ->assertJson(
            $ids
        );
    }

    /** @test */
    public function non_authenticated_cannot_get_salas_enabled()
    {
        $salas = factory('App\SalaReuniao', 3)->create();
        $ids = $salas->sortBy('regional.regional')->pluck('id')->all();

        $this->get(route('sala.reuniao.regionais.ativas', 'coworking'))
        ->assertNoContent();
    }

    /** @test */
    public function view_total_and_itens_in_form_agendar_sala()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda = factory('App\AgendamentoSala')->create();

        $this->get(route('sala.reuniao.dias.horas', [
            'sala_id' => $agenda->sala->id, 'dia' => onlyDate($agenda->dia), 'tipo' => 'coworking'
        ]))
        ->assertJsonFragment([
            'itens' => $agenda->sala->getItensHtml('coworking'),
            'total' => $agenda->sala->getParticipantesAgendar('coworking')
        ]);
    }

    /** @test */
    public function non_authenticated_cannot_get_total_and_itens()
    {
        $agenda = factory('App\AgendamentoSala')->create();

        $this->get(route('sala.reuniao.dias.horas', [
            'sala_id' => $agenda->sala->id, 'dia' => onlyDate($agenda->dia), 'tipo' => 'coworking'
        ]))
        ->assertNoContent();
    }

    /** @test */
    public function non_authenticated_cannot_verify_gerenti()
    {
        $agenda = factory('App\AgendamentoSala')->create();

        $this->post(route('representante.agendar.inserir.post', 'verificar'))
        ->assertRedirect(route('representante.login'));
    }

    /** @test */
    public function cannot_verify_invalid_format_cpf()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '56983238010',
        ])
        ->assertJson([
            'participante_irregular' => '<strong>Formato do CPF inválido!</strong>'
        ])
        ->assertSessionMissing('participantes_verificados')
        ->assertSessionMissing('participantes_invalidos');

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380.10',
        ])
        ->assertJson([
            'participante_irregular' => '<strong>Formato do CPF inválido!</strong>'
        ])
        ->assertSessionMissing('participantes_verificados')
        ->assertSessionMissing('participantes_invalidos');
    }

    /** @test */
    public function remove_verify_gerenti_after_submit()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ])
        ->assertJson([
            'participante_irregular' => null
        ])
        ->assertSessionHas('participantes_verificados')
        ->assertSessionMissing('participantes_invalidos');

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'reuniao',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'], 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
            'aceite' => 'on'
        ])->assertStatus(302);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento criado com sucesso! Foi enviado um e-mail com os detalhes.')
        ->assertSessionMissing('participantes_verificados')
        ->assertSessionMissing('participantes_invalidos');
    }

    /** @test */
    public function remove_verify_gerenti_in_route_home_agendar()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ])
        ->assertJson([
            'participante_irregular' => null
        ])
        ->assertSessionHas('participantes_verificados')
        ->assertSessionMissing('participantes_invalidos');

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSessionMissing('participantes_verificados')
        ->assertSessionMissing('participantes_invalidos');
    }

    /** @test */
    public function can_submit_agendar_sala_coworking()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->assertEquals(AgendamentoSala::count(), 0);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'], 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'aceite' => 'on'
        ])->assertStatus(302);

        Mail::assertQueued(AgendamentoSalaMail::class);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento criado com sucesso! Foi enviado um e-mail com os detalhes.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'coworking',
            'idrepresentante' => 1,
        ]);

        $this->assertEquals(AgendamentoSala::count(), 1);
    }

    /** @test */
    public function log_is_generated_when_agendamento_is_created()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'], 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'aceite' => 'on'
        ]);

        $agenda = AgendamentoSala::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $string = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $string .= $representante->nome.' (CPF / CNPJ: '.$representante->cpf_cnpj.') *agendou* reserva da sala em *'.$agenda->sala->regional->regional;
        $string .= '* no dia '.onlyDate($agenda->dia).' para '.$agenda->tipo_sala.', no período ' .$agenda->periodo . ' e foi criado um novo registro no termo de consentimento, com a id: 1';
        $this->assertStringContainsString($string, $log);
    }

    /** @test */
    public function can_submit_agendar_sala_reuniao()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw([
            'sala_reuniao_id' => factory('App\SalaReuniao')->create([
                'participantes_reuniao' => 5
            ])
        ]);
        

        $this->assertEquals(AgendamentoSala::count(), 0);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ])
        ->assertJson([
            'participante_irregular' => null
        ])
        ->assertSessionHas('participantes_verificados')
        ->assertSessionMissing('participantes_invalidos');

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ])
        ->assertJson([
            'participante_irregular' => null
        ])
        ->assertSessionHas('participantes_verificados')
        ->assertSessionMissing('participantes_invalidos');

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '225.536.748-30',
        ])
        ->assertJson([
            'participante_irregular' => null
        ])
        ->assertSessionHas('participantes_verificados')
        ->assertSessionMissing('participantes_invalidos');

        // Teste com CPF ativo e em dia (569.832.380-10), não encontrado (819.219.230-08) e cancelado (225.536.748-30)
        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'reuniao',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'], 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08', '225.536.748-30'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS', 'NOME PARTICIPANTE TRES'],
            'aceite' => 'on'
        ])->assertStatus(302);

        Mail::assertQueued(AgendamentoSalaMail::class);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento criado com sucesso! Foi enviado um e-mail com os detalhes.')
        ->assertSessionMissing('participantes_verificados');

        $agenda['participantes'] = json_encode(AgendamentoSala::find(1)->getParticipantes(), JSON_FORCE_OBJECT);

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'reuniao',
            'idrepresentante' => 1,
            'participantes' => $agenda['participantes']
        ]);

        $this->assertEquals(AgendamentoSala::count(), 1);
    }

    /** @test */
    public function cannot_submit_agendar_sala_without_tipo_sala()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => '',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'], 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'tipo_sala'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_tipo_sala_invalid()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'reunion',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'], 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'tipo_sala'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_without_sala_reuniao_id()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => '', 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'sala_reuniao_id'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_sala_reuniao_id_invalid()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => '23', 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'sala_reuniao_id'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_without_dia()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => '', 
            'periodo' => $agenda['periodo'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'dia'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_dia_format_invalid()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => '12-23/2024', 
            'periodo' => $agenda['periodo'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'dia'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_dia_before_tomorrow()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => now()->format('d/m/Y'), 
            'periodo' => $agenda['periodo'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'dia'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_dia_after_1_month()
    {
        $mes_seguinte = Carbon::tomorrow()->addMonth();
        while($mes_seguinte->isWeekend())
            $mes_seguinte->addDay();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => $mes_seguinte->format('d/m/Y'), 
            'periodo' => $agenda['periodo'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'dia'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_without_periodo()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => '',
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'periodo'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_periodo_invalid()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => '18:00 - 19:00',
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'periodo'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_periodo_invalid_format()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => '18:00 * 19:00',
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'periodo'
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => '18:00-19:00',
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'periodo'
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => '18:00-19:0',
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'periodo'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_without_participantes_cpf()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => [],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participantes_cpf'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_without_verify_gerenti()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participantes_cpf'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('É obrigatório ter participante');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_if_invalid_cpf_gerenti()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '681.267.125-89',
        ])
        ->assertJson([
            'participante_irregular' => '<strong>CPF:</strong> 681.267.125-89'
        ])
        ->assertSessionHas('participantes_verificados')
        ->assertSessionHas('participantes_invalidos');

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['681.267.125-89'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participantes_cpf'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('É obrigatório ter participante');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_cpf_not_array()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => '569.832.380-10',
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participantes_cpf'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('Formato inválido do campo Participantes');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_cpf_invalid_cpfs()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '111.111.111-11',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '222.333.444-99',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['111.111.111-11', '222.333.444-99'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participantes_cpf.*'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('CPF inválido');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_cpf_equals()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '569.832.380-10'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participantes_cpf.*'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('Existe CPF repetido');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_cpf_rc_in_participantes_cpf()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => $representante->cpf_cnpj,
        ])
        ->assertJson([
            'participante_irregular' => '<strong>Não pode inserir o próprio CPF!</strong>'
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => [$representante->cpf_cnpj, '569.832.380-10'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participantes_nome'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('Total de nomes difere do total de CPFs ou encontrado CPF irregular junto ao Conselho');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_without_participantes_nome()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => [],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participantes_nome'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('É obrigatório ter participante');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_nome_not_array()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => 'fgfgf',
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participantes_nome'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('Formato inválido do campo Participantes');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_nome_size_less_than_participantes_cpf()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['SÓ UM NOME', ''],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participantes_nome'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('Total de nomes difere do total de CPFs ou encontrado CPF irregular junto ao Conselho');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_nome_equals()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['TUDO IGUAL', 'TUDO IGUAL'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participantes_nome.*'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('Existe nome repetido');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_nome_with_numbers()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['TUDO IGUAL 2', 'TUDO 1GUAL'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participantes_nome.*'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('Não pode conter número no nome');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_nome_less_than_5_chars()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['TUDO', 'TUD'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participantes_nome.*'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('O nome deve ter 5 caracteres ou mais');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_nome_greater_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => [$faker->sentence(300), $faker->sentence(400)],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participantes_nome.*'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('O nome deve ter 191 caracteres ou menos');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_vetados_with_same_hour()
    {
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create([
            'idrepresentante' => $representante1->id
        ]);
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participante_vetado'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('Os seguintes participantes já estão agendados neste mesmo dia e período:<br><strong>569.832.380-10<br>819.219.230-08</strong>');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_vetados_with_periodo_todo_true()
    {
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create([
            'idrepresentante' => $representante1->id
        ]);
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => '09:00 - 12:00',
            'periodo_todo' => 1,
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participante_vetado'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('Os seguintes participantes já estão agendados neste mesmo dia e período:<br><strong>569.832.380-10<br>819.219.230-08</strong>');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_vetados_with_periodo_todo_false()
    {
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create([
            'idrepresentante' => $representante1->id,
            'periodo' => '09:00 - 12:00',
            'periodo_todo' => 1,
        ]);
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => '10:00 - 11:00',
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participante_vetado'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('Os seguintes participantes já estão agendados neste mesmo dia e período:<br><strong>569.832.380-10<br>819.219.230-08</strong>');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_vetados_with_periodo_todo_false_and_range()
    {
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create([
            'idrepresentante' => $representante1->id,
            'periodo' => '10:00 - 11:00',
        ]);
        $agenda1->sala->update(['horarios_reuniao' => '09:30,10:30,11:30']);
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda1->sala->id,
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => '10:30 - 11:30',
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participante_vetado'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('Os seguintes participantes já estão agendados neste mesmo dia e período:<br><strong>569.832.380-10<br>819.219.230-08</strong>');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_vetados_msg_singular()
    {
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create([
            'idrepresentante' => $representante1->id
        ]);
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participante_vetado'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('O seguinte participante já está agendado neste mesmo dia e período:<br><strong>569.832.380-10</strong>');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_participantes_vetados_msg_plural()
    {
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create([
            'idrepresentante' => $representante1->id
        ]);
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participante_vetado'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('Os seguintes participantes já estão agendados neste mesmo dia e período:<br><strong>569.832.380-10<br>819.219.230-08</strong>');
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_without_aceite()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
        ])
        ->assertSessionHasErrors([
            'aceite'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_reuniao_with_aceite_invalid()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '819.219.230-08',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'aceite' => 'ok'
        ])
        ->assertSessionHasErrors([
            'aceite'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_dia_lotado()
    {
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create([
            'idrepresentante' => $representante1->id,
            'periodo' => '09:00 - 12:00',
            'periodo_todo' => 1,
        ]);
        factory('App\AgendamentoSala')->states('reuniao')->create([
            'idrepresentante' => $representante1->id,
            'periodo' => '14:00 - 17:00',
            'periodo_todo' => 1,
            'sala_reuniao_id' => $agenda1->sala_reuniao_id
        ]);
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '651.337.265-89',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda1->sala_reuniao_id,
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => '09:00 - 10:00',
            'participantes_cpf' => ['651.337.265-89'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'dia',
            'periodo'
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda1->sala_reuniao_id,
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => '14:30 - 15:30',
            'participantes_cpf' => ['651.337.265-89'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'dia',
            'periodo'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_periodo_lotado()
    {
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create([
            'idrepresentante' => $representante1->id,
            'periodo' => '09:00 - 12:00',
            'periodo_todo' => 1,
        ]);
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '651.337.265-89',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda1->sala_reuniao_id,
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => '09:00 - 10:00',
            'participantes_cpf' => ['651.337.265-89'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'periodo'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_agendado()
    {
        // Agendado em outra regional, mas no mesmo dia e periodo

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        factory('App\AgendamentoSala')->states('reuniao')->create([
            'periodo' => '09:00 - 10:00'
        ]);
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '651.337.265-89',
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => '09:00 - 10:00',
            'participantes_cpf' => ['651.337.265-89'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'periodo'
        ]);
    }

    /** @test */
    public function cannot_submit_agendar_sala_with_participante()
    {
        // Agendado em outra regional, mas no mesmo dia e periodo como participante
        $representante = factory('App\Representante')->create();
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '11748345000144'
        ]);
        factory('App\AgendamentoSala')->states('reuniao')->create([
            'idrepresentante' => $representante1->id,
            'participantes' => json_encode([apenasNumeros($representante->cpf_cnpj) => 'NOME PARTICIPANTE UM'], JSON_FORCE_OBJECT),
            'periodo' => '09:00 - 10:00'
        ]);

        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'periodo'
        ]);
    }

    /** @test */
    public function cannot_view_agendar_sala_after_created_4_by_month_and_next_month_with_status_null()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agendas = factory('App\AgendamentoSala', 4)->create();

        $dia = Carbon::parse($agendas->get(0)->dia)->addMonth();
        while($dia->isWeekend())
            $dia->addDay();

        factory('App\AgendamentoSala', 4)->create([
            'dia' => $dia->format('Y-m-d')
        ]);

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'agendar']))
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Já possui o limite de 4 agendamentos confirmados ou com presença a confirmar no mês atual e/ou seguinte.');
    }

    /** @test */
    public function cannot_view_agendar_sala_after_created_4_by_month_presencial_and_next_month_online()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agendas = factory('App\AgendamentoSala', 4)->states('presencial')->create();

        $dia = Carbon::parse($agendas->get(0)->dia)->addMonth();
        while($dia->isWeekend())
            $dia->addDay();

        factory('App\AgendamentoSala', 4)->create([
            'dia' => $dia->format('Y-m-d')
        ]);

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'agendar']))
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Já possui o limite de 4 agendamentos confirmados ou com presença a confirmar no mês atual e/ou seguinte.');
    }

    /** @test */
    public function cannot_view_agendar_sala_after_created_4_by_month_and_next_month_with_status_compareceu_or_null()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agendas = factory('App\AgendamentoSala', 4)->create([
            'status' => AgendamentoSala::STATUS_COMPARECEU
        ]);

        $dia = Carbon::parse($agendas->get(0)->dia)->addMonth();
        while($dia->isWeekend())
            $dia->addDay();

        factory('App\AgendamentoSala', 4)->create([
            'dia' => $dia->format('Y-m-d'),
        ]);

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'agendar']))
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Já possui o limite de 4 agendamentos confirmados ou com presença a confirmar no mês atual e/ou seguinte.');
    }

    /** @test */
    public function cannot_submit_agendar_sala_after_created_4_by_month_with_status_null()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        factory('App\AgendamentoSala', 4)->create();
        $agenda = factory('App\AgendamentoSala')->raw();

        $dia = Carbon::parse($agenda['dia'])->addDay();
        while($dia->isWeekend())
            $dia->addDay();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => $dia->format('d/m/Y'), 
            'periodo' => '09:00 - 10:00',
            'aceite' => 'on'
        ])
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Já possui o limite de 4 agendamentos confirmados ou com presença a confirmar no mês atual e/ou seguinte.');
    }

    /** @test */
    public function cannot_submit_agendar_sala_after_created_4_by_month_with_status_compareceu()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        factory('App\AgendamentoSala', 4)->create([
            'status' => AgendamentoSala::STATUS_COMPARECEU
        ]);
        $agenda = factory('App\AgendamentoSala')->raw();

        $dia = Carbon::parse($agenda['dia'])->addDay();
        while($dia->isWeekend())
            $dia->addDay();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => $dia->format('d/m/Y'), 
            'periodo' => '13:00 - 17:00',
            'aceite' => 'on'
        ])
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Já possui o limite de 4 agendamentos confirmados ou com presença a confirmar no mês atual e/ou seguinte.');
    }

    /** @test */
    public function cannot_submit_agendar_sala_after_created_4_by_month_with_status_compareceu_or_null()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        factory('App\AgendamentoSala', 2)->create([
            'status' => AgendamentoSala::STATUS_COMPARECEU
        ]);
        factory('App\AgendamentoSala', 2)->create();
        $agenda = factory('App\AgendamentoSala')->raw();

        $dia = Carbon::parse($agenda['dia'])->addDay();
        while($dia->isWeekend())
            $dia->addDay();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => $dia->format('d/m/Y'), 
            'periodo' => '13:00 - 17:00',
            'aceite' => 'on'
        ])
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Já possui o limite de 4 agendamentos confirmados ou com presença a confirmar no mês atual e/ou seguinte.');
    }

    /** @test */
    public function get_lotados_reuniao()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create();
        factory('App\AgendamentoSala')->states('reuniao')->create([
            'sala_reuniao_id' => $agenda1->sala_reuniao_id,
            'periodo' => '9:00 - 12:00',
            'periodo_todo' => 1
        ]);
        factory('App\AgendamentoSala')->states('reuniao')->create([
            'sala_reuniao_id' => $agenda1->sala_reuniao_id,
            'periodo' => '14:00 - 17:00',
            'periodo_todo' => 1
        ]);

        $dia = Carbon::parse($agenda1->dia);
        $this->get(route('sala.reuniao.dias.horas', [
            'sala_id' => $agenda1->sala_reuniao_id,
            'dia' => '',
            'tipo' => 'reuniao',
        ]))
        ->assertJsonFragment([
            [$dia->month, $dia->day, 'lotado']
        ]);
    }

    /** @test */
    public function get_lotados_coworking()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);
        $agenda1 = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id
        ]);
        factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
            'periodo' => '9:00 - 12:00',
            'periodo_todo' => 1
        ]);
        factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $agenda1->sala_reuniao_id,
            'periodo' => '13:00 - 17:00',
            'periodo_todo' => 1
        ]);

        $dia = Carbon::parse($agenda1->dia);
        $this->get(route('sala.reuniao.dias.horas', [
            'sala_id' => $agenda1->sala_reuniao_id,
            'dia' => '',
            'tipo' => 'coworking',
        ]))
        ->assertJsonFragment([
            [$dia->month, $dia->day, 'lotado']
        ]);
    }

    /** @test */
    public function get_liberado_periodo_reuniao()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda1 = factory('App\AgendamentoSala')->states('reuniao')->create();
        $horarios = $agenda1->sala->formatarHorariosAgendamento($agenda1->sala->getHorarios('reuniao'));
        unset($horarios['09:00']);
        unset($horarios['manha']);

        $dia = Carbon::parse($agenda1->dia);
        $this->get(route('sala.reuniao.dias.horas', [
            'sala_id' => $agenda1->sala_reuniao_id,
            'dia' => $dia->format('d/m/Y'),
            'tipo' => 'reuniao',
        ]))
        ->assertJsonFragment([
            'horarios' => $horarios
        ]);
    }

    /** @test */
    public function get_liberado_periodo_coworking()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);
        $agenda1 = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id
        ]);

        $horarios = $agenda1->sala->formatarHorariosAgendamento($agenda1->sala->getHorarios('coworking'));
        unset($horarios['09:00']);
        unset($horarios['manha']);

        $dia = Carbon::parse($agenda1->dia);
        $this->get(route('sala.reuniao.dias.horas', [
            'sala_id' => $agenda1->sala_reuniao_id,
            'dia' => $dia->format('d/m/Y'),
            'tipo' => 'coworking',
        ]))
        ->assertJsonFragment([
            'horarios' => $horarios
        ]);
    }

    /** @test */
    public function view_agendamento_after_created()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->create();

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<p class="pb-0 branco" data-clarity-mask="True">Protocolo: <strong>'.$agenda->protocolo.'</strong></p>')
        ->assertSee('<p class="pb-0 branco" data-clarity-mask="True">Regional: <strong>'.$agenda->sala->regional->regional.'</strong></p>')
        ->assertSee('Sala: <strong>'.$agenda->getTipoSala().'</strong>')
        ->assertSee('&nbsp;&nbsp;|&nbsp;&nbsp;Dia: <strong>'.onlyDate($agenda->dia).'</strong>')
        ->assertSee('&nbsp;&nbsp;|&nbsp;&nbsp;Período: <strong>'.$agenda->getPeriodo().'</strong>')
        ->assertSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $agenda->id]).'" class="btn btn-danger btn-sm link-nostyle mt-2">Cancelar</a>');
    }

    /** @test */
    public function view_agendamento_participantes_after_created()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $cpfs = array();
        $nomes = array();

        foreach($agenda->getParticipantes() as $cpf => $nome){
            array_push($cpfs, $cpf);
            array_push($nomes, $nome);
        }

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<p class="pb-0 branco" data-clarity-mask="True"><i class="fas fa-users text-dark"></i> Participantes: ')
        ->assertSee('CPF: <strong>'.formataCpfCnpj($cpfs[0]) . '</strong>&nbsp;&nbsp;|&nbsp;&nbsp;Nome: <strong>' .$nomes[0].'</strong>')
        ->assertSee('CPF: <strong>'.formataCpfCnpj($cpfs[1]) . '</strong>&nbsp;&nbsp;|&nbsp;&nbsp;Nome: <strong>' .$nomes[1].'</strong>')
        ->assertSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'editar', 'id' => $agenda->id]).'" class="btn btn-secondary btn-sm link-nostyle mt-2">Editar Participantes</a>');
    }

    /** @test */
    public function view_agendamento_participando_after_created()
    {
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);
        $agenda1 = factory('App\AgendamentoSala')->create([
            'idrepresentante' => $representante1->id
        ]);
        $agenda2 = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->addDays(7)->format('Y-m-d'),
            'idrepresentante' => $representante1->id
        ]);
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'participantes' => json_encode(['56983238010' => 'NOME PARTICIPANTE UM', '86294373085' => 'NOME PARTICIPANTE DOIS'], JSON_FORCE_OBJECT),
            'idrepresentante' => $representante1->id,
            'protocolo' => 'RC-AGE-XXXXXX14'
        ]);

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda3 = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d'),
            'idrepresentante' => $representante1->id,
            'participantes' => json_encode([apenasNumeros($representante->cpf_cnpj) => 'NOME PARTICIPANTE UM'], JSON_FORCE_OBJECT),
        ]);
        
        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<p class="pb-0 branco" data-clarity-mask="True">Protocolo: <strong>'.$agenda->protocolo.'</strong></p>')
        ->assertSee('Representante Responsável: ')
        ->assertSee('CPF / CNPJ: <strong>'.$representante1->cpf_cnpj . '</strong>')
        ->assertSee('Nome: <strong>'.$representante1->nome . '</strong>')
        ->assertSee('<p class="pb-0 branco" data-clarity-mask="True">Regional: <strong>'.$agenda->sala->regional->regional.'</strong></p>')
        ->assertSee('Sala: <strong>'.$agenda->getTipoSala().'</strong>')
        ->assertSee('&nbsp;&nbsp;|&nbsp;&nbsp;Dia: <strong>'.onlyDate($agenda->dia).'</strong>')
        ->assertSee('&nbsp;&nbsp;|&nbsp;&nbsp;Período: <strong>'.$agenda->getPeriodo().'</strong>')
        ->assertDontSee('<p class="pb-0 branco" data-clarity-mask="True">Protocolo: <strong>'.$agenda2->protocolo.'</strong></p>')
        ->assertDontSee('<p class="pb-0 branco" data-clarity-mask="True">Protocolo: <strong>'.$agenda1->protocolo.'</strong></p>')
        ->assertSee('<p class="pb-0 branco" data-clarity-mask="True">Protocolo: <strong>'.$agenda3->protocolo.'</strong></p>');
    }

    /** @test */
    public function can_to_edit_participantes_reuniao()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $itens = array();
        foreach($agenda->sala->getItensHtml($agenda->tipo_sala) as $i => $item)
            $i == 0 ? array_push($itens, $item) : array_push($itens, '&nbsp;&nbsp;&nbsp;<strong>|</strong>&nbsp;&nbsp;&nbsp;'.$item);

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'editar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSeeInOrder($itens)
        ->assertSeeText('Salvar');

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'editar',
            'id' => $agenda->id
        ]), [
            'participantes_cpf' => ['569.832.380-10'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
        ])->assertStatus(302);

        Mail::assertQueued(AgendamentoSalaMail::class);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Participantes foram alterados com sucesso! Foi enviado um e-mail com os detalhes.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'reuniao',
            'idrepresentante' => 1,
            'participantes' => json_encode([
                '56983238010' => 'NOME PARTICIPANTE UM'
            ], JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function log_is_generated_when_agendamento_is_edited()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'editar',
            'id' => $agenda->id
        ]), [
            'participantes_cpf' => ['569.832.380-10'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
        ])->assertStatus(302);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $string = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $string .= $representante->nome.' (CPF / CNPJ: '.$representante->cpf_cnpj.') *editou os participantes* da reserva da sala em *'.$agenda->sala->regional->regional;
        $string .= '* no dia '.onlyDate($agenda->dia).' para '.$agenda->tipo_sala.', no período ' .$agenda->periodo;
        $this->assertStringContainsString($string, $log);
    }

    /** @test */
    public function cannot_send_mail_when_not_changed_participantes_reuniao()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'participantes' => json_encode([
                '56983238010' => 'NOME PARTICIPANTE UM'
            ], JSON_FORCE_OBJECT)
        ]);

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'editar',
            'id' => $agenda->id
        ]), [
            'participantes_cpf' => ['569.832.380-10'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
        ])->assertStatus(302);

        Mail::assertNotQueued(AgendamentoSalaMail::class);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-info-circle"></i>&nbsp;&nbsp;Não houve alterações nos participantes.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'reuniao',
            'idrepresentante' => 1,
            'participantes' => $agenda['participantes']
        ]);
    }

    /** @test */
    public function cannot_to_edit_participantes_reuniao_equal_or_after_dia()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertDontSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'editar', 'id' => $agenda->id]).'" class="btn btn-secondary btn-sm link-nostyle">Editar Participantes</a>');

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'editar', 'id' => $agenda->id]))
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Não é possível editar o agendamento.');

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '569.832.380-10',
        ]);

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'editar',
            'id' => $agenda->id
        ]), [
            'participantes_cpf' => ['569.832.380-10'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
        ])->assertStatus(302);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Não é possível editar o agendamento.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'reuniao',
            'idrepresentante' => 1,
            'participantes' => $agenda['participantes']
        ]);
    }

    /** @test */
    public function cannot_to_edit_participantes_reuniao_without_verify_gerenti()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'editar',
            'id' => $agenda->id
        ]), [
            'participantes_cpf' => ['651.337.265-89'],
            'participantes_nome' => ['NOME PARTICIPANTE TRES'],
        ])
        ->assertSessionHasErrors([
            'participantes_cpf'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('É obrigatório ter participante');
    }

    /** @test */
    public function cannot_to_edit_participantes_reuniao_if_invalid_cpf_gerenti()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $this->post(route('representante.agendar.inserir.post', 'verificar'), [
            'participantes_cpf' => '681.267.125-89',
        ])
        ->assertJson([
            'participante_irregular' => '<strong>CPF:</strong> 681.267.125-89'
        ])
        ->assertSessionHas('participantes_verificados')
        ->assertSessionHas('participantes_invalidos');
        
        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'editar',
            'id' => $agenda->id
        ]), [
            'participantes_cpf' => ['681.267.125-89'],
            'participantes_nome' => ['NOME PARTICIPANTE TRES'],
        ])
        ->assertSessionHasErrors([
            'participantes_cpf'
        ]);

        $this->get(route('representante.agendar.inserir.view', 'agendar'))
        ->assertSee('<p class="alert alert-danger" data-clarity-mask="True">')
        ->assertSee('É obrigatório ter participante');
    }

    /** @test */
    public function can_to_edit_participantes_reuniao_nome_without_verify_gerenti()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'editar',
            'id' => $agenda->id
        ]), [
            'participantes_cpf' => ['569.832.380-10', '819.219.230-08'],
            'participantes_nome' => ['NOME PARTICIPANTE MUDOU', 'NOME PARTICIPANTE DOIS'],
        ]);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Participantes foram alterados com sucesso! Foi enviado um e-mail com os detalhes.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'reuniao',
            'idrepresentante' => 1,
            'participantes' => json_encode([
                '56983238010' => 'NOME PARTICIPANTE MUDOU',
                '81921923008' => 'NOME PARTICIPANTE DOIS'
            ], JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function can_to_cancel_reuniao()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $agenda->id]).'" class="btn btn-danger btn-sm link-nostyle mt-2">Cancelar</a>');

        $itens = array();
        foreach($agenda->sala->getItensHtml($agenda->tipo_sala) as $i => $item)
            $i == 0 ? array_push($itens, $item) : array_push($itens, '&nbsp;&nbsp;&nbsp;<strong>|</strong>&nbsp;&nbsp;&nbsp;'.$item);

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSeeInOrder($itens)
        ->assertSee('<button type="submit" class="btn btn-danger">');

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'cancelar',
            'id' => $agenda->id
        ]))->assertStatus(302);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento cancelado com sucesso!');

        $this->assertDatabaseHas('agendamentos_salas', [
            'status' => 'Cancelado'
        ]);
    }

    /** @test */
    public function can_to_cancel_coworking()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->create();

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $agenda->id]).'" class="btn btn-danger btn-sm link-nostyle mt-2">Cancelar</a>');

        $itens = array();
        foreach($agenda->sala->getItensHtml($agenda->tipo_sala) as $i => $item)
            $i == 0 ? array_push($itens, $item) : array_push($itens, '&nbsp;&nbsp;&nbsp;<strong>|</strong>&nbsp;&nbsp;&nbsp;'.$item);

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSeeInOrder($itens)
        ->assertSee('<button type="submit" class="btn btn-danger">');

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'cancelar',
            'id' => $agenda->id
        ]))->assertStatus(302);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento cancelado com sucesso!');

        $this->assertDatabaseHas('agendamentos_salas', [
            'status' => 'Cancelado'
        ]);
    }

    /** @test */
    public function log_is_generated_when_agendamento_is_canceled()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'cancelar',
            'id' => $agenda->id
        ]));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $string = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $string .= $representante->nome.' (CPF / CNPJ: '.$representante->cpf_cnpj.') *cancelou* a reserva da sala em *'.$agenda->sala->regional->regional;
        $string .= '* no dia '.onlyDate($agenda->dia).' para '.$agenda->tipo_sala.', no período ' .$agenda->periodo;
        $this->assertStringContainsString($string, $log);
    }

    /** @test */
    public function cannot_to_cancel_equal_or_after_dia()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertDontSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $agenda->id]).'" class="btn btn-danger btn-sm link-nostyle">Cancelar</a>');

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $agenda->id]))
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Não é possível cancelar o agendamento.');

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'cancelar',
            'id' => $agenda->id
        ]))->assertStatus(302);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Não é possível cancelar o agendamento.');

        $this->assertDatabaseMissing('agendamentos_salas', [
            'status' => 'Cancelado'
        ]);
    }

    /** @test */
    public function can_to_justify_reuniao()
    {
        Mail::fake();
        Storage::fake('local');

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'justificar', 'id' => $agenda->id]).'" class="btn btn-sm btn-dark link-nostyle mt-2">Justificar</a>');

        $itens = array();
        foreach($agenda->sala->getItensHtml($agenda->tipo_sala) as $i => $item)
            $i == 0 ? array_push($itens, $item) : array_push($itens, '&nbsp;&nbsp;&nbsp;<strong>|</strong>&nbsp;&nbsp;&nbsp;'.$item);

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'justificar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSeeInOrder($itens)
        ->assertSeeText('Justificar');

        $file = UploadedFile::fake()->image('teste.png', 250, 250);
        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'justificar', 'id' => $agenda->id
        ]), [
            'justificativa' => 'dfdfdfdfdfdfdfdfdfdfdfdfdf',
            'anexo_sala' => $file,
        ])
        ->assertStatus(302);

        Mail::assertQueued(AgendamentoSalaMail::class);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento justificado com sucesso! Está em análise do atendente. Foi enviado um e-mail com a sua justificativa.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'status' => 'Justificativa Enviada'
        ]);

        Storage::disk('local')->assertExists('representantes/agendamento_sala/'.$agenda->fresh()->anexo);
    }

    /** @test */
    public function can_to_justify_coworking()
    {
        Mail::fake();
        Storage::fake('local');

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'justificar', 'id' => $agenda->id]).'" class="btn btn-sm btn-dark link-nostyle mt-2">Justificar</a>');

        $itens = array();
        foreach($agenda->sala->getItensHtml($agenda->tipo_sala) as $i => $item)
            $i == 0 ? array_push($itens, $item) : array_push($itens, '&nbsp;&nbsp;&nbsp;<strong>|</strong>&nbsp;&nbsp;&nbsp;'.$item);

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'justificar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSeeInOrder($itens)
        ->assertSeeText('Justificar');

        $file = UploadedFile::fake()->image('teste.png', 250, 250);
        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'justificar', 'id' => $agenda->id
        ]), [
            'justificativa' => 'dfdfdfdfdfdfdfdfdfdfdfdfdf',
            'anexo_sala' => $file,
        ])
        ->assertStatus(302);

        Mail::assertQueued(AgendamentoSalaMail::class);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento justificado com sucesso! Está em análise do atendente. Foi enviado um e-mail com a sua justificativa.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'status' => 'Justificativa Enviada'
        ]);

        Storage::disk('local')->assertExists('representantes/agendamento_sala/'.$agenda->fresh()->anexo);
    }

    /** @test */
    public function log_is_generated_when_agendamento_is_justified()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'justificar', 'id' => $agenda->id
        ]), [
            'justificativa' => 'dfdfdfdfdfdfdfdfdfdfdfdfdf',
        ]);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $string = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $string .= $representante->nome.' (CPF / CNPJ: '.$representante->cpf_cnpj.') *justificou e está em análise do atendente* o não comparecimento do agendamento da sala em *'.$agenda->sala->regional->regional;
        $string .= '* no dia '.onlyDate($agenda->dia).' para '.$agenda->tipo_sala.', no período ' .$agenda->periodo;
        $this->assertStringContainsString($string, $log);
    }

    /** @test */
    public function can_to_justify_without_anexo_sala()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'justificar', 'id' => $agenda->id]).'" class="btn btn-sm btn-dark link-nostyle mt-2">Justificar</a>');

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'justificar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSeeText('Justificar');

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'justificar', 'id' => $agenda->id
        ]), [
            'justificativa' => 'dfdfdfdfdfdfdfdfdfdfdfdfdf',
            'anexo_sala' => '',
        ])
        ->assertStatus(302);

        Mail::assertQueued(AgendamentoSalaMail::class);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento justificado com sucesso! Está em análise do atendente. Foi enviado um e-mail com a sua justificativa.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'status' => 'Justificativa Enviada'
        ]);
    }

    /** @test */
    public function cannot_to_justify_without_justificativa()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'justificar', 'id' => $agenda->id
        ]), [
            'justificativa' => '',
            'anexo_sala' => '',
        ])
        ->assertSessionHasErrors([
            'justificativa',
        ]);
    }

    /** @test */
    public function cannot_to_justify_with_justificativa_less_than_10_chars()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'justificar', 'id' => $agenda->id
        ]), [
            'justificativa' => 'qwerty123',
            'anexo_sala' => '',
        ])
        ->assertSessionHasErrors([
            'justificativa',
        ]);
    }

    /** @test */
    public function cannot_to_justify_with_justificativa_greater_than_1000_chars()
    {
        $faker = \Faker\Factory::create();
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'justificar', 'id' => $agenda->id
        ]), [
            'justificativa' => $faker->sentence(3000),
            'anexo_sala' => '',
        ])
        ->assertSessionHasErrors([
            'justificativa',
        ]);
    }

    /** @test */
    public function cannot_to_justify_with_anexo_sala_invalid()
    {
        Storage::fake('local');

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        foreach(['teste.gif', 'teste.doc', 'teste.exe', 'teste.zip'] as $f)
        {
            $file = UploadedFile::fake()->create($f, 1000);
            $this->put(route('representante.agendar.inserir.put', [
                'acao' => 'justificar', 'id' => $agenda->id
            ]), [
                'justificativa' => 'fgfgfgfgerfg',
                'anexo_sala' => $file,
            ])
            ->assertSessionHasErrors([
                'anexo_sala',
            ]);
        }
    }

    /** @test */
    public function cannot_to_justify_with_anexo_sala_greater_than_2MB()
    {
        Storage::fake('local');

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        $file = UploadedFile::fake()->create('teste.pdf', 2049, 'application/pdf');
        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'justificar', 'id' => $agenda->id
        ]), [
            'justificativa' => 'fgfgfgfgerfg',
            'anexo_sala' => $file,
        ])
        ->assertSessionHasErrors([
            'anexo_sala',
        ]);
    }

    /** @test */
    public function cannot_to_justify_after_2_days()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->create([
            'dia' => now()->subDays(3)->format('Y-m-d')
        ]);

        $this->get(route('representante.agendar.inserir.view', [
            'acao' => 'justificar', 'id' => $agenda->id
        ]))
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Não é possível justificar o agendamento.');

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'justificar', 'id' => $agenda->id
        ]), [
            'justificativa' => 'fgfgfgfgerfg',
            'anexo_sala' => '',
        ])
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-times"></i>&nbsp;&nbsp;Não é possível justificar o agendamento.');
    }

    /** @test */
    public function cannot_create_agendamento_without_sala_enabled()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->raw([
            'sala_reuniao_id' => null
        ]);
        $agenda['dia'] = Carbon::parse($agenda['dia'])->format('d/m/Y');

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<p><i class="fas fa-info-circle text-primary"></i> <b><em>No momento não há salas disponíveis para novos agendamentos.</em></b></p>');

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'agendar']))
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-info-circle"></i> No momento não há salas disponíveis para novos agendamentos.');

        $this->post(route('representante.agendar.inserir.post', 'agendar'), $agenda)
        ->assertSessionHasErrors([
            'sala_reuniao_id'
        ]);
    }

    /** @test */
    public function can_to_edit_participantes_reuniao_without_sala_enabled()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $agenda->sala->update([
            'participantes_reuniao' => 0,
            'participantes_coworking' => 0
        ]);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<p><i class="fas fa-info-circle text-primary"></i> <b><em>No momento não há salas disponíveis para novos agendamentos.</em></b></p>');

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'editar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSeeText('Salvar');

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'editar',
            'id' => $agenda->id
        ]), [
            'participantes_cpf' => ['569.832.380-10'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
        ])->assertStatus(302);

        Mail::assertQueued(AgendamentoSalaMail::class);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Participantes foram alterados com sucesso! Foi enviado um e-mail com os detalhes.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'reuniao',
            'idrepresentante' => 1,
            'participantes' => json_encode([
                '56983238010' => 'NOME PARTICIPANTE UM'
            ], JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function cannot_add_participante_when_to_edit_participantes_reuniao_without_sala_enabled()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $agenda->sala->update([
            'participantes_reuniao' => 0,
            'participantes_coworking' => 0
        ]);

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'editar',
            'id' => $agenda->id
        ]), [
            'participantes_cpf' => ['569.832.380-10'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
        ]);

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'editar',
            'id' => $agenda->id
        ]), [
            'participantes_cpf' => ['819.219.230-08', '569.832.380-10'],
            'participantes_nome' => ['NOME PARTICIPANTE DOIS', 'NOME PARTICIPANTE UM'],
        ]);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertDontSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Participantes foram alterados com sucesso! Foi enviado um e-mail com os detalhes.');
    }

    /** @test */
    public function can_to_cancel_without_sala_enabled()
    {        
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->create();

        $agenda->sala->update([
            'participantes_reuniao' => 0,
            'participantes_coworking' => 0
        ]);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<p><i class="fas fa-info-circle text-primary"></i> <b><em>No momento não há salas disponíveis para novos agendamentos.</em></b></p>');

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSee('<button type="submit" class="btn btn-danger">');

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'cancelar',
            'id' => $agenda->id
        ]))->assertStatus(302);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento cancelado com sucesso!');

        $this->assertDatabaseHas('agendamentos_salas', [
            'status' => 'Cancelado'
        ]);
    }

    /** @test */
    public function can_to_justify_without_sala_enabled()
    {
        Mail::fake();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');
        $agenda = factory('App\AgendamentoSala')->create([
            'dia' => now()->subDays(1)->format('Y-m-d')
        ]);
        $agenda->sala->update([
            'participantes_reuniao' => 0,
            'participantes_coworking' => 0
        ]);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<p><i class="fas fa-info-circle text-primary"></i> <b><em>No momento não há salas disponíveis para novos agendamentos.</em></b></p>');

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'justificar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSeeText('Justificar');

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'justificar', 'id' => $agenda->id
        ]), [
            'justificativa' => 'dfdfdfdfdfdfdfdfdfdfdfdfdf',
            'anexo_sala' => '',
        ])
        ->assertStatus(302);

        Mail::assertQueued(AgendamentoSalaMail::class);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento justificado com sucesso! Está em análise do atendente. Foi enviado um e-mail com a sua justificativa.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'status' => 'Justificativa Enviada'
        ]);
    }

    /** 
     * =======================================================================================================
     * Testes da rota via ajax que verifica dias e períodos comm e sem bloqueios
     * =======================================================================================================
     */

    /** @test */
    public function get_full_days()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create();
        factory('App\AgendamentoSala')->states('reuniao')->create([
            'sala_reuniao_id' => $sala->id,
        ]);
        $agendamento = factory('App\AgendamentoSala')->states('reuniao')->create([
            'sala_reuniao_id' => $sala->id,
            'periodo' => '09:00 - 12:00',
            'periodo_todo' => 1
        ]);
        factory('App\AgendamentoSala')->states('reuniao')->create([
            'sala_reuniao_id' => $sala->id,
            'periodo' => '14:00 - 17:00',
            'periodo_todo' => 1
        ]);
        
        $dia = Carbon::parse($agendamento->dia);
        $lotado = [$dia->month, $dia->day, 'lotado'];

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'reuniao', 'sala_id' => $agendamento->sala->id, 'dia' => ''
        ]))
        ->assertJsonFragment([$lotado]);
    }

    /** @test */
    public function get_full_days_if_weekends()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create();

        $sabado = Carbon::tomorrow();
        while(!$sabado->isSaturday())
            $sabado->addDay();
        $lotado = [$sabado->month, $sabado->day, 'lotado'];

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'reuniao', 'sala_id' => $sala->id, 'dia' => ''
        ]))
        ->assertJsonFragment([$lotado]);

        $domingo = Carbon::tomorrow();
        while(!$domingo->isSunday())
            $domingo->addDay();
        $lotado = [$domingo->month, $domingo->day, 'lotado'];

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'reuniao', 'sala_id' => $sala->id, 'dia' => ''
        ]))
        ->assertJsonFragment([$lotado]);
    }

    /** @test */
    public function get_full_days_with_bloqueio()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create();
        $bloqueio = factory('App\SalaReuniaoBloqueio')->create([
            'sala_reuniao_id' => $sala->id,
            'horarios' => implode(',', $sala->getTodasHoras()),
        ]);

        $dia = Carbon::tomorrow();
        while($dia->isWeekend())
            $dia->addDay();
        $lotado = [$dia->month, $dia->day, 'lotado'];

        $dia->addDays(8);
        while($dia->isWeekend())
            $dia->addDay();
        $nao_lotado = [$dia->month, $dia->day, 'lotado'];

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'reuniao', 'sala_id' => $sala->id, 'dia' => ''
        ]))
        ->assertJsonFragment([$lotado])
        ->assertJsonMissingExact([$nao_lotado]);
    }

    /** @test */
    // Situação em que o bloqueio é criado para algumas horas e não cancela o agendamento já existente.
    // Então mesmo com a hora disponível, a quantidade de agendados já foi preenchida, a não ser que ocorra o cancelamento.
    public function get_full_days_with_bloqueio_and_created_agendado()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);
        
        $dia = Carbon::tomorrow();
        while($dia->isWeekend())
            $dia->addDay();
            
        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
        ]);
    
        $bloqueio = factory('App\SalaReuniaoBloqueio')->create([
            'sala_reuniao_id' => $sala->id,
            'horarios' => '09:00,10:00,11:00'
        ]);

        $lotado = [$dia->month, $dia->day, 'lotado'];

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => ''
        ]))
        ->assertJsonFragment([$lotado]);
    }

    /** @test */
    // Situação em que o bloqueio é criado para algumas horas e cancela o agendamento já existente no horário bloqueado.
    public function get_empty_days_with_bloqueio_and_after_cancel_created_agendado()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);
        
        $dia = Carbon::tomorrow();
        while($dia->isWeekend())
            $dia->addDay();
            
        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
            'periodo' => '13:00 - 17:00',
            'periodo_todo' => 1
        ]);

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create([
            'sala_reuniao_id' => $sala->id,
            'horarios' => '09:00,10:00,11:00',
        ]);

        $lotado = [$dia->month, $dia->day, 'lotado'];

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => ''
        ]))
        ->assertJsonFragment([$lotado]);

        $agendamento->update(['status' => AgendamentoSala::STATUS_CANCELADO]);

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => ''
        ]))
        ->assertJsonMissing([$lotado]);
    }

    /** @test */
    public function get_full_days_with_bloqueio_and_data_final_null()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);
        
        $bloqueio = factory('App\SalaReuniaoBloqueio')->create([
            'sala_reuniao_id' => $sala->id,
            'horarios' => implode(',', $sala->getTodasHoras()),
            'dataFinal' => null
        ]);

        $lotados = array();
        $dia = Carbon::tomorrow();
        while($dia->lte(Carbon::today()->addMonth()))
        {
            array_push($lotados, [$dia->month, $dia->day, 'lotado']);
            $dia->addDay();
        }

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => ''
        ]))
        ->assertJson($lotados);
    }

    /** @test */
    public function get_full_days_when_weekends_and_empty_days_for_agendamentos()
    {
        $representante = factory('App\Representante')->create();
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 3
        ]);

        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
            'idrepresentante' => $representante->id,
        ]);
        $agendamento1 = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
            'idrepresentante' => $representante1->id,
        ]);

        $diaAge = Carbon::parse($agendamento->dia);
        $lotados = array();
        $dia = Carbon::tomorrow();
        while($dia->lte(Carbon::today()->addMonth()))
        {
            if($dia->isWeekend())
                array_push($lotados, [$dia->month, $dia->day, 'lotado']);
            $dia->addDay();
        }

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => ''
        ]))
        ->assertJson($lotados)
        ->assertJsonMissingExact([
            $diaAge->month, $diaAge->day, 'lotado'
        ]);
    }

    /** @test */
    public function get_agendado_days()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);
        
        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
        ]);

        $diaAge = Carbon::parse($agendamento->dia);
        $outroDia = Carbon::parse($agendamento->dia)->addDays(7);

        $agendamento1 = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
            'periodo' => '13:00 - 17:00',
            'periodo_todo' => 1,
            'dia' => $outroDia->format('Y-m-d')
        ]);

        $agendado = array();
        array_push($agendado, [$diaAge->month, $diaAge->day, 'agendado'], [$outroDia->month, $outroDia->day, 'agendado']);

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => ''
        ]))
        ->assertJsonFragment([$agendado[0]])
        ->assertJsonFragment([$agendado[1]]);
    }

    /** @test */
    public function get_periodo()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);

        $agendamento = factory('App\AgendamentoSala')->raw();
        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => onlyDate($agendamento['dia'])
        ]))
        ->assertJsonFragment([
            'horarios' => $horarios,
        ]);
    }

    /** @test */
    public function remove_periodo()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);

        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
        ]);
        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));
        unset($horarios['09:00']);
        unset($horarios['manha']);

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => onlyDate($agendamento['dia'])
        ]))
        ->assertJsonMissing([
            '09:00' => '09:00 - 10:00',
        ])
        ->assertJsonFragment([
            'horarios' => $horarios,
        ]);
    }

    /** @test */
    public function remove_periodos()
    {
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);

        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
        ]);

        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
            'periodo' => '11:00 - 12:00',
            'idrepresentante' => $representante1->id
        ]);
        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));
        unset($horarios['09:00']);
        unset($horarios['11:00']);
        unset($horarios['manha']);

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => onlyDate($agendamento['dia'])
        ]))
        ->assertJsonMissing([
            '09:00' => '09:00 - 10:00',
            '11:00' => '11:00 - 12:00',
        ])
        ->assertJsonFragment([
            'horarios' => $horarios,
        ]);
    }

    /** @test */
    public function remove_periodos_if_weekend()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);

        $sabado = Carbon::tomorrow();
        while(!$sabado->isSaturday())
            $sabado->addDay();
        $lotado = [$sabado->month, $sabado->day, 'lotado'];
        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => $sabado->format('d/m/Y')
        ]))
        ->assertJsonMissing([
            'horarios' => $horarios,
        ]);

        $domingo = Carbon::tomorrow();
        while(!$domingo->isSunday())
            $domingo->addDay();
        $lotado = [$domingo->month, $domingo->day, 'lotado'];

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => $domingo->format('d/m/Y')
        ]))
        ->assertJsonMissing([
            'horarios' => $horarios,
        ]);
    }

    /** @test */
    public function remove_hour_with_bloqueio()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create([
            'sala_reuniao_id' => $sala->id,
            'horarios' => '09:00,10:00',
        ]);

        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));
        unset($horarios['09:00']);
        unset($horarios['10:00']);
        unset($horarios['11:00']);

        $dia = Carbon::tomorrow();
        while($dia->isWeekend())
            $dia->addDay();
            
        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => $dia->format('d/m/Y')
        ]))
        ->assertJsonFragment([
            "manha" => "11:00 - 12:00",
            "tarde" => "13:00 - 17:00"
        ])
        ->assertJsonMissing([
            '09:00' => '09:00 - 10:00',
            '10:00' => '10:00 - 11:00',
            '11:00' => '11:00 - 12:00',
        ]);
    }

    /** @test */
    public function remove_periodo_with_bloqueio()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create([
            'sala_reuniao_id' => $sala->id,
            'horarios' => implode(',', $sala->getHorarios('coworking')),
        ]);

        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));

        $dia = Carbon::tomorrow();
        while($dia->isWeekend())
            $dia->addDay();
            
        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => $dia->format('d/m/Y')
        ]))
        ->assertJsonMissing([
            'horarios' => $horarios,
        ]);
    }

    /** @test */
    public function remove_periodo_with_bloqueio_and_data_final_null()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);

        $bloqueio = factory('App\SalaReuniaoBloqueio')->create([
            'sala_reuniao_id' => $sala->id,
            'horarios' => implode(',', $sala->getHorarios('coworking')),
            'dataFinal' => null
        ]);

        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));

        $dia = Carbon::tomorrow()->addDays(7);
        while($dia->isWeekend())
            $dia->addDay();

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => $dia->format('d/m/Y')
        ]))
        ->assertJsonMissing([
            'horarios' => $horarios,
        ]);
    }

    /** @test */
    public function remove_periodo_todo_when_by_hour()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create();

        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));
        unset($horarios['manha']);
        unset($horarios['09:00']);

        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
        ]);

        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
        ]);

        $dia = Carbon::parse($agendamento->dia)->format('d/m/Y');
        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => $dia
        ]))
        ->assertJsonFragment([
            'horarios' => $horarios,
        ]);
    }

    /** @test */
    public function remove_hours_when_periodo_todo()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create();

        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));
        unset($horarios['manha']);
        unset($horarios['09:00']);
        unset($horarios['10:00']);
        unset($horarios['11:00']);

        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
            'periodo' => '09:00 - 12:00',
            'periodo_todo' => 1
        ]);

        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
            'periodo' => '09:00 - 12:00',
            'periodo_todo' => 1
        ]);

        $dia = Carbon::parse($agendamento->dia)->format('d/m/Y');
        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => $dia
        ]))
        ->assertJsonFragment([
            'horarios' => $horarios,
        ]);
    }

    /** @test */
    public function remove_hours_within_range()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $sala = factory('App\SalaReuniao')->create([
            'participantes_coworking' => 1
        ]);

        $agendamento = factory('App\AgendamentoSala')->create([
            'sala_reuniao_id' => $sala->id,
        ]);

        $sala->update(['horarios_coworking' => '09:30,10:30,11:30']);
        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));
        unset($horarios['manha']);
        unset($horarios['09:30']);

        $dia = Carbon::parse($agendamento->dia)->format('d/m/Y');

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => $dia
        ]))
        ->assertJsonFragment([
            'horarios' => $horarios,
        ]);

        // Periodo todo com 30 minutos de duração e agendado das 9 as 10
        $sala->update(['horarios_coworking' => '11:30']);
        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => $dia
        ]))
        ->assertJsonFragment([
            'horarios' => $horarios,
        ]);

        // Periodo todo com 1h 30 minutos de duração e agendado das 9 as 10
        $sala->update(['horarios_coworking' => '10:30,11:30']);
        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => $dia
        ]))
        ->assertJsonFragment([
            'horarios' => $horarios,
        ]);

        // Periodo todo com 2h 30 minutos de duração e agendado das 9 as 10
        $sala->update(['horarios_coworking' => '09:30']);
        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => $dia
        ]))
        ->assertJsonMissing([
            'horarios' => $horarios,
        ]);

        // Agendado das 9 as 10 e das 14 as 17
        $sala->update(['horarios_coworking' => '09:00,10:00,11:00,13:00']);

        $agendamento = factory('App\AgendamentoSala')->states('reuniao')->create([
            'sala_reuniao_id' => $sala->id,
            'periodo_todo' => 1,
            'periodo' => '14:00 - 17:00',
        ]);
        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));
        unset($horarios['09:00']);
        unset($horarios['manha']);
        unset($horarios['tarde']);

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => $dia
        ]))
        ->assertJsonFragment([
            'horarios' => $horarios,
        ]);

        AgendamentoSala::find(1)->delete();
        AgendamentoSala::find(2)->delete();

        // Agendado periodo todo das 11:30 as 12 e horas das 9, 10 livres para agendar
        $agendamento = factory('App\AgendamentoSala')->states('reuniao')->create([
            'sala_reuniao_id' => $sala->id,
            'periodo_todo' => 1,
            'periodo' => '11:30 - 12:00',
        ]);
        $sala->update(['horarios_coworking' => '09:00,10:00,11:30']);
        $horarios = $sala->formatarHorariosAgendamento($sala->getHorarios('coworking'));
        unset($horarios['11:30']);
        unset($horarios['manha']);

        $this->get(route('sala.reuniao.dias.horas', [
            'tipo' => 'coworking', 'sala_id' => $sala->id, 'dia' => $dia
        ]))
        ->assertJsonFragment([
            'horarios' => $horarios,
        ]);
    }

    /* ROTINAS KERNEL */

    private function create_agendamentos_rotina()
    {
        // atualizar status
        $agendamento = factory('App\AgendamentoSala')->create([
            'dia' => now()->subDays(3)->format('Y-m-d')
        ]);

        // manter
        $agendamento1 = factory('App\AgendamentoSala')->create([
            'dia' => now()->format('Y-m-d'),
            'idrepresentante' => factory('App\Representante')->create([
                'cpf_cnpj' => '11122233344'
            ])
        ]);

        // atualizar status
        $agendamento2 = factory('App\AgendamentoSala')->create([
            'dia' => now()->subDays(4)->format('Y-m-d'),
            'idrepresentante' => factory('App\Representante')->create([
                'cpf_cnpj' => '22233344455'
            ])
        ])->fresh();

        // manter
        $agendamento3 = factory('App\AgendamentoSala')->states('justificado')->create([
            'idrepresentante' => factory('App\Representante')->create([
                'cpf_cnpj' => '44455566677'
            ])
        ]);

        /* ANEXOS */

        $rep = factory('App\Representante')->create([
            'cpf_cnpj' => '15489632587'
        ]);
        $img = $rep->id . '-' . time() . '.png';
        $file = UploadedFile::fake()->image($img, 250, 250);
        $file->storeAs("representantes/agendamento_sala", $img);

        // excluir anexo
        $agendamento4 = factory('App\AgendamentoSala')->states('justificado')->create([
            'anexo' => $img,
            'idrepresentante' => $rep->id,
            'updated_at' => now()->subMonth()->toDateTimeString(),
            'status' => AgendamentoSala::STATUS_JUSTIFICADO,
        ]);

        $rep = factory('App\Representante')->create([
            'cpf_cnpj' => '87456852369'
        ]);
        $pdf = $rep->id . '-' . time() . '.pdf';
        $file = UploadedFile::fake()->create($pdf, 1000, 'application/pdf');
        $file->storeAs("representantes/agendamento_sala", $pdf);

        // excluir anexo
        $agendamento5 = factory('App\AgendamentoSala')->states('justificado')->create([
            'status' => AgendamentoSala::STATUS_NAO_COMPARECEU,
            'anexo' => $pdf,
            'idrepresentante' => $rep->id,
            'updated_at' => now()->subMonth()->subDays(10)->toDateTimeString()
        ]);

        $rep = factory('App\Representante')->create([
            'cpf_cnpj' => '52369874123'
        ]);
        $img = $rep->id . '-' . time() . '.jpg';
        $file = UploadedFile::fake()->image($img);
        $file->storeAs("representantes/agendamento_sala", $img);

        // manter anexo
        $agendamento6 = factory('App\AgendamentoSala')->states('justificado')->create([
            'status' => AgendamentoSala::STATUS_JUSTIFICADO,
            'anexo' => $img,
            'idrepresentante' => $rep->id,
            'updated_at' => now()->subDays(29)->toDateTimeString()
        ]);

        $rep = factory('App\Representante')->create([
            'cpf_cnpj' => '87589874445'
        ]);
        $img = $rep->id . '-' . time() . '.jpg';

        // não remover anexo para disparo de log
        $agendamento7 = factory('App\AgendamentoSala')->states('justificado')->create([
            'status' => AgendamentoSala::STATUS_JUSTIFICADO,
            'anexo' => $img,
            'idrepresentante' => $rep->id,
            'updated_at' => now()->subMonth()->toDateTimeString()
        ]);

        return [
            $agendamento, $agendamento1, $agendamento2, $agendamento3, $agendamento4, $agendamento5, $agendamento6, $agendamento7
        ];
    }

    /** @test */
    public function rotina_envio_emails_agendados_no_dia_kernel()
    {
        Mail::fake();
        $user = $this->signInAsAdmin();
        $users = \App\User::select('email','idregional','idperfil')->where('idperfil', 1)->get();
        $agendamentos = $this->create_agendamentos_rotina();

        $service = resolve('App\Contracts\MediadorServiceInterface');
        $service->getService('SalaReuniao')->agendados()->executarRotinaAgendadosDoDia($users);

        Mail::assertSent(InternoAgendamentoSalaMail::class);
    }

    /** @test */
    public function rotina_agendamentos_update_status_kernel()
    {
        $agendamentos = $this->create_agendamentos_rotina();

        $service = resolve('App\Contracts\MediadorServiceInterface');
        $service->getService('SalaReuniao')->agendados()->executarRotina();

        $this->assertDatabaseHas('agendamentos_salas', [
            'idrepresentante' => $agendamentos[0]->idrepresentante,
            'id' => $agendamentos[0]->id,
            'status' => AgendamentoSala::STATUS_NAO_COMPARECEU
        ]);

        $this->assertDatabaseHas('agendamentos_salas', [
            'idrepresentante' => $agendamentos[1]->idrepresentante,
            'id' => $agendamentos[1]->id,
            'status' => $agendamentos[1]->status
        ]);

        $this->assertDatabaseHas('agendamentos_salas', [
            'idrepresentante' => $agendamentos[2]->idrepresentante,
            'id' => $agendamentos[2]->id,
            'status' => AgendamentoSala::STATUS_NAO_COMPARECEU
        ]);

        $this->assertDatabaseHas('agendamentos_salas', [
            'idrepresentante' => $agendamentos[3]->idrepresentante,
            'id' => $agendamentos[3]->id,
            'status' => $agendamentos[3]->status
        ]);
    }

    /** @test */
    public function rotina_agendamentos_remove_anexos_kernel()
    {
        $user = $this->signInAsAdmin();
        $agendamentos = $this->create_agendamentos_rotina();

        $service = resolve('App\Contracts\MediadorServiceInterface');
        $service->getService('SalaReuniao')->agendados()->executarRotinaRemoveAnexos();

        Storage::disk('local')->assertMissing('representantes/agendamento_sala/'.$agendamentos[4]->anexo);
        $this->assertDatabaseHas('agendamentos_salas', [
            'id' => $agendamentos[4]->id,
            'anexo' => $agendamentos[4]->anexo . ' - [removido]'
        ]);
        $this->get(route('sala.reuniao.agendados.view', $agendamentos[4]->id))
        ->assertSeeText($agendamentos[4]->anexo . ' - [removido]');

        Storage::disk('local')->assertMissing('representantes/agendamento_sala/'.$agendamentos[5]->anexo);
        $this->assertDatabaseHas('agendamentos_salas', [
            'id' => $agendamentos[5]->id,
            'anexo' => $agendamentos[5]->anexo . ' - [removido]'
        ]);
        $this->get(route('sala.reuniao.agendados.view', $agendamentos[5]->id))
        ->assertSeeText($agendamentos[5]->anexo . ' - [removido]');

        Storage::disk('local')->assertExists('representantes/agendamento_sala/'.$agendamentos[6]->anexo);
        $this->assertDatabaseHas('agendamentos_salas', [
            'id' => $agendamentos[6]->id,
            'anexo' => $agendamentos[6]->anexo
        ]);
        $this->get(route('sala.reuniao.agendados.view', $agendamentos[6]->id))
        ->assertSeeText('Comprovante');

        // remover o anexo criado no teste que não deve ser removido na rotina
        Storage::disk('local')->delete('representantes/agendamento_sala/'.$agendamentos[6]->anexo);
    }

    /** @test */
    public function log_is_generated_when_anexo_removed_kernel()
    {
        $agendamentos = $this->create_agendamentos_rotina();

        $service = resolve('App\Contracts\MediadorServiceInterface');
        $service->getService('SalaReuniao')->agendados()->executarRotinaRemoveAnexos();

        // remover o anexo criado no teste que não deve ser removido na rotina
        Storage::disk('local')->delete('representantes/agendamento_sala/'.$agendamentos[6]->anexo);

        $log = explode(PHP_EOL, tailCustom(storage_path($this->pathLogInterno()), 3));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [Rotina Portal - Sala de Reunião] - ';
        $txt = $inicio . 'Removido anexo do agendamento de sala com ID ' . $agendamentos[4]->id.'.';
        $this->assertStringContainsString($txt, $log[0]);

        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [Rotina Portal - Sala de Reunião] - ';
        $txt = $inicio . 'Removido anexo do agendamento de sala com ID ' . $agendamentos[5]->id.'.';
        $this->assertStringContainsString($txt, $log[1]);

        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [Rotina Portal - Sala de Reunião] - ';
        $txt = $inicio . 'Não foi removido anexo do agendamento de sala com ID ' . $agendamentos[7]->id.'.';
        $this->assertStringContainsString($txt, $log[2]);
    }
}
