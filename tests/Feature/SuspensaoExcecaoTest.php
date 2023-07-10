<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Permissao;
use Carbon\Carbon;
use App\SuspensaoExcecao;

class SuspensaoExcecaoTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * =======================================================================================================
     * TESTES GERENCIAR SUSPENSÃO / EXCEÇÃO
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $suspenso = factory('App\SuspensaoExcecao')->create();
        
        $this->get(route('sala.reuniao.suspensao.lista'))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.suspensao.view', $suspenso->id))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']))->assertRedirect(route('login'));
        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.suspensao.criar'))->assertRedirect(route('login'));
        $this->post(route('sala.reuniao.suspensao.store'))->assertRedirect(route('login'));
        $this->get(route('sala.reuniao.suspensao.busca'))->assertRedirect(route('login'));
    }

    // /** @test */
    // public function non_authorized_users_cannot_access_links()
    // {
    //     $faker = \Faker\Factory::create();

    //     $this->signIn();
    //     $this->assertAuthenticated('web');

    //     $suspenso = factory('App\SuspensaoExcecao')->create();
        
    //     $this->get(route('sala.reuniao.suspensao.lista'))->assertForbidden();
    //     $this->get(route('sala.reuniao.suspensao.view', $suspenso->id))->assertForbidden();
    //     $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']))->assertForbidden();
    //     $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
    //         'data_inicial_excecao' => now()->format('Y-m-d'),
    //         'data_final_excecao' => now()->format('Y-m-d'),
    //         'justificativa' => $faker->sentence(100)
    //     ])->assertForbidden();
    //     $this->get(route('sala.reuniao.suspensao.criar'))->assertForbidden();
    //     $this->post(route('sala.reuniao.suspensao.store'))->assertForbidden();
    //     $this->get(route('sala.reuniao.suspensao.busca'))->assertForbidden();
    // }

    /** @test */
    public function suspensao_can_be_created_without_representante()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')->make()->toArray();
        
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso)
        ->assertRedirect(route('sala.reuniao.suspensao.lista'));

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'cpf_cnpj' => $suspenso['cpf_cnpj'],
            'idrepresentante' => null,
            'data_inicial' => now()->format('Y-m-d'),
            'data_final' => now()->addDays(30)->format('Y-m-d'),
            'data_inicial_excecao' => null,
            'data_final_excecao' => null,
            'justificativa' => json_encode([
                '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - ' . $suspenso['justificativa'] . ' Data da justificativa: ' . formataData(now())
            ], JSON_FORCE_OBJECT),
            'situacao' => SuspensaoExcecao::SITUACAO_SUSPENSAO
        ]);
    }

    /** @test */
    public function suspensao_can_be_created_with_representante()
    {
        $user = $this->signInAsAdmin();
        $representante = factory('App\Representante')->create();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')->make()->toArray();
                    
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso)
        ->assertRedirect(route('sala.reuniao.suspensao.lista'));

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'cpf_cnpj' => null,
            'idrepresentante' => 1,
            'data_inicial' => now()->format('Y-m-d'),
            'data_final' => now()->addDays(30)->format('Y-m-d'),
            'data_inicial_excecao' => null,
            'data_final_excecao' => null,
            'justificativa' => json_encode([
                '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - ' . $suspenso['justificativa'] . ' Data da justificativa: ' . formataData(now())
            ], JSON_FORCE_OBJECT),
            'situacao' => SuspensaoExcecao::SITUACAO_SUSPENSAO
        ]);
    }

    /** @test */
    public function suspensao_can_be_created_with_cnpj()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')
        ->make([
            'cpf_cnpj' => '73525258000185'
        ])->toArray();
        
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso)
        ->assertRedirect(route('sala.reuniao.suspensao.lista'));

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'cpf_cnpj' => $suspenso['cpf_cnpj'],
            'idrepresentante' => null,
            'data_inicial' => now()->format('Y-m-d'),
            'data_final' => now()->addDays(30)->format('Y-m-d'),
            'data_inicial_excecao' => null,
            'data_final_excecao' => null,
            'justificativa' => json_encode([
                '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - ' . $suspenso['justificativa'] . ' Data da justificativa: ' . formataData(now())
            ], JSON_FORCE_OBJECT),
            'situacao' => SuspensaoExcecao::SITUACAO_SUSPENSAO
        ]);
    }

    /** @test */
    public function log_is_generated_when_suspensao_is_created()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')->make()->toArray();
                    
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') criou *suspensão* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function suspensao_cannot_be_created_without_cpf_cnpj()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')
        ->make([
            'cpf_cnpj' => null,
        ])->toArray();
                    
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso)
        ->assertSessionHasErrors([
            'cpf_cnpj'
        ]);
    }

    /** @test */
    public function suspensao_cannot_be_created_with_cpf_cnpj_invalid()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')
        ->make([
            'cpf_cnpj' => '111.111.111-11',
        ])->toArray();
                    
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso)
        ->assertSessionHasErrors([
            'cpf_cnpj'
        ]);
    }

    /** @test */
    public function suspensao_cannot_be_created_without_data_inicial()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')
        ->make([
            'data_inicial' => null,
        ])->toArray();
                    
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso)
        ->assertSessionHasErrors([
            'data_inicial'
        ]);
    }

    /** @test */
    public function suspensao_cannot_be_created_with_data_inicial_invalid()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')
        ->make([
            'data_inicial' => now()->format('d/m/Y'),
        ])->toArray();
                    
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso)
        ->assertSessionHasErrors([
            'data_inicial'
        ]);
    }

    /** @test */
    public function suspensao_cannot_be_created_with_data_inicial_before_today()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')
        ->make([
            'data_inicial' => now()->subDay()->format('Y-m-d'),
        ])->toArray();
                    
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso)
        ->assertSessionHasErrors([
            'data_inicial'
        ]);
    }

    /** @test */
    public function suspensao_can_be_created_without_data_final()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')
        ->make([
            'data_final' => null,
        ])->toArray();
                    
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso)
        ->assertRedirect(route('sala.reuniao.suspensao.lista'));

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'data_final' => null,
            'justificativa' => json_encode([
                '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - ' . $suspenso['justificativa'] . ' Data da justificativa: ' . formataData(now())
            ], JSON_FORCE_OBJECT),
            'situacao' => SuspensaoExcecao::SITUACAO_SUSPENSAO
        ]);
    }

    /** @test */
    public function suspensao_cannot_be_created_with_data_final_invalid()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')
        ->make([
            'data_final' => now()->format('d/m/Y'),
        ])->toArray();
                    
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso)
        ->assertSessionHasErrors([
            'data_final'
        ]);
    }

    /** @test */
    public function suspensao_cannot_be_created_with_data_final_before_data_inicial()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')
        ->make([
            'data_final' => now()->subDay()->format('Y-m-d'),
        ])->toArray();
                    
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso)
        ->assertSessionHasErrors([
            'data_final'
        ]);
    }

    /** @test */
    public function suspensao_cannot_be_created_without_justificativa()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')
        ->make([
            'justificativa' => null,
        ])->toArray();
                    
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso)
        ->assertSessionHasErrors([
            'justificativa'
        ]);
    }

    /** @test */
    public function suspensao_cannot_be_created_with_justificativa_less_than_10_chars()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')
        ->make([
            'justificativa' => 'apdertflo',
        ])->toArray();
                    
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso)
        ->assertSessionHasErrors([
            'justificativa'
        ]);
    }

    /** @test */
    public function suspensao_cannot_be_created_with_justificativa_more_than_1000_chars()
    {
        $faker = \Faker\Factory::create();

        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('request_suspensao')
        ->make([
            'justificativa' => $faker->sentence(300),
        ])->toArray();
                    
        $this->get(route('sala.reuniao.suspensao.lista'))->assertOk();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertOk();

        $this->post(route('sala.reuniao.suspensao.store'), $suspenso)
        ->assertSessionHasErrors([
            'justificativa'
        ]);
    }

    /** @test */
    public function view_suspensao_is_created()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);

        $this->get(route('sala.reuniao.suspensao.lista'))
        ->assertOk()
        ->assertSeeText('Cadastrado no Portal')
        ->assertSee($suspenso->cpf_cnpj)
        ->assertSeeText('Suspenso por 30 dias')
        ->assertSee('<span class="text-danger"><b>'.SuspensaoExcecao::SITUACAO_SUSPENSAO.'</b></span>')
        ->assertSee('<a href="' .route('sala.reuniao.suspensao.view', $suspenso->id). '" class="btn btn-sm btn-primary">Ver</a>&nbsp;&nbsp;&nbsp;')
        ->assertSee('<a href="' .route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']). '" class="btn btn-sm btn-warning">Editar Suspensão</a>&nbsp;&nbsp;&nbsp;')
        ->assertSee('<a href="' .route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']). '" class="btn btn-sm btn-success">Editar Exceção</a>');
    }

    /* EDITAR SUSPENSÃO */

    /** @test */
    public function suspensao_can_be_edited_add_30_days()
    {
        $justificativa = 'Teste editar suspensão com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'suspensao']), [
            'data_final' => '30',
            'justificativa' => $justificativa
        ])
        ->assertRedirect(route('sala.reuniao.suspensao.lista'));

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'data_final' => Carbon::parse($suspenso->data_final)->addDays(30)->format('Y-m-d'),
            'justificativa' => json_encode([
                $suspenso->getJustificativas()[0],
                '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - ' . $justificativa . ' Data da justificativa: ' . formataData(now())
            ], JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function suspensao_can_be_edited_add_60_days()
    {
        $justificativa = 'Teste editar suspensão com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'suspensao']), [
            'data_final' => '60',
            'justificativa' => $justificativa
        ])
        ->assertRedirect(route('sala.reuniao.suspensao.lista'));

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'data_final' => Carbon::parse($suspenso->data_final)->addDays(60)->format('Y-m-d'),
            'justificativa' => json_encode([
                $suspenso->getJustificativas()[0],
                '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - ' . $justificativa . ' Data da justificativa: ' . formataData(now())
            ], JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function suspensao_can_be_edited_add_90_days()
    {
        $justificativa = 'Teste editar suspensão com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'suspensao']), [
            'data_final' => '90',
            'justificativa' => $justificativa
        ])
        ->assertRedirect(route('sala.reuniao.suspensao.lista'));

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'data_final' => Carbon::parse($suspenso->data_final)->addDays(90)->format('Y-m-d'),
            'justificativa' => json_encode([
                $suspenso->getJustificativas()[0],
                '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - ' . $justificativa . ' Data da justificativa: ' . formataData(now())
            ], JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function suspensao_can_be_edited_with_data_final_null()
    {
        $justificativa = 'Teste editar suspensão com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'suspensao']), [
            'data_final' => '00',
            'justificativa' => $justificativa
        ])
        ->assertRedirect(route('sala.reuniao.suspensao.lista'));

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'data_final' => null,
            'justificativa' => json_encode([
                $suspenso->getJustificativas()[0],
                '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - ' . $justificativa . ' Data da justificativa: ' . formataData(now())
            ], JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function log_is_generated_when_suspensao_is_updated()
    {
        $justificativa = 'Teste editar suspensão com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'suspensao']), [
            'data_final' => '30',
            'justificativa' => $justificativa
        ]);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou período *suspensão* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function suspensao_can_be_edited_add_days_with_data_final_null()
    {
        $justificativa = 'Teste editar suspensão com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create([
            'data_final' => null
        ]);
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']))
        ->assertOk()
        ->assertSee('<option value="30" >+ 30 dias: '. onlyDate($suspenso->addDiasDataFinal(30)) .'</option>')
        ->assertDontSee('<option value="00" >Tempo Indeterminado</option>');

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'suspensao']), [
            'data_final' => '30',
            'justificativa' => $justificativa
        ])
        ->assertRedirect(route('sala.reuniao.suspensao.lista'));

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'data_final' => now()->addDays(30)->format('Y-m-d'),
            'justificativa' => json_encode([
                $suspenso->getJustificativas()[0],
                '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - ' . $justificativa . ' Data da justificativa: ' . formataData(now())
            ], JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function suspensao_cannot_be_edited_with_data_final_invalid()
    {
        $justificativa = 'Teste editar suspensão com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'suspensao']), [
            'data_final' => '40',
            'justificativa' => $justificativa
        ])
        ->assertSessionHasErrors([
            'data_final'
        ]);
    }

    /** @test */
    public function suspensao_cannot_be_edited_with_data_final_00_when_data_final_null()
    {
        $justificativa = 'Teste editar suspensão com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create([
            'data_final' => null
        ]);
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'suspensao']), [
            'data_final' => '00',
            'justificativa' => $justificativa
        ])
        ->assertSessionHasErrors([
            'data_final'
        ]);
    }

    /** @test */
    public function suspensao_cannot_be_edited_without_justificativa()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create([
            'data_final' => null
        ]);
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'suspensao']), [
            'data_final' => '30',
            'justificativa' => null
        ])
        ->assertSessionHasErrors([
            'justificativa'
        ]);
    }

    /** @test */
    public function suspensao_cannot_be_edited_with_justificativa_less_than_10_chars()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create([
            'data_final' => null
        ]);
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'suspensao']), [
            'data_final' => '30',
            'justificativa' => 'dfertyphl'
        ])
        ->assertSessionHasErrors([
            'justificativa'
        ]);
    }

    /** @test */
    public function suspensao_cannot_be_edited_with_justificativa_more_than_10_chars()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create([
            'data_final' => null
        ]);
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'suspensao']), [
            'data_final' => '30',
            'justificativa' => $faker->sentence(300)
        ])
        ->assertSessionHasErrors([
            'justificativa'
        ]);
    }

    /** @test */
    public function view_suspensao_is_updated()
    {
        $justificativa = 'Teste editar suspensão com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'suspensao']), [
            'data_final' => '30',
            'justificativa' => $justificativa
        ]);

        $this->get(route('sala.reuniao.suspensao.lista'))
        ->assertOk()
        ->assertSeeText('Cadastrado no Portal')
        ->assertSee($suspenso->cpf_cnpj)
        ->assertSeeText('Suspenso por 60 dias')
        ->assertSee('<span class="text-danger"><b>'.SuspensaoExcecao::SITUACAO_SUSPENSAO.'</b></span>')
        ->assertSee('<a href="' .route('sala.reuniao.suspensao.view', $suspenso->id). '" class="btn btn-sm btn-primary">Ver</a>&nbsp;&nbsp;&nbsp;')
        ->assertSee('<a href="' .route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']). '" class="btn btn-sm btn-warning">Editar Suspensão</a>&nbsp;&nbsp;&nbsp;')
        ->assertSee('<a href="' .route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']). '" class="btn btn-sm btn-success">Editar Exceção</a>');

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'suspensao']), [
            'data_final' => '90',
            'justificativa' => $justificativa
        ]);

        $this->get(route('sala.reuniao.suspensao.lista'))
        ->assertOk()
        ->assertSeeText('Suspenso por 150 dias');
    }

    /* EDITAR EXCEÇÃO */

    /** @test */
    public function excecao_can_be_edited()
    {
        $justificativa = 'Teste exceção com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->format('Y-m-d'),
            'justificativa' => $justificativa
        ])
        ->assertRedirect(route('sala.reuniao.suspensao.lista'));

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'cpf_cnpj' => null,
            'idrepresentante' => 1,
            'data_inicial' => now()->format('Y-m-d'),
            'data_final' => now()->addDays(30)->format('Y-m-d'),
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->format('Y-m-d'),
            'justificativa' => json_encode([
                $suspenso->getJustificativas()[0],
                '[Funcionário(a) '.$user->nome.'] | [Ação - exceção] - ' . $justificativa . ' Data da justificativa: ' . formataData(now())
            ], JSON_FORCE_OBJECT),
            'situacao' => SuspensaoExcecao::SITUACAO_EXCECAO
        ]);
    }

    /** @test */
    public function log_is_generated_when_excecao_is_updated()
    {
        $justificativa = 'Teste exceção com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->format('Y-m-d'),
            'justificativa' => $justificativa
        ]);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou período *exceção* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function excecao_can_be_removed()
    {
        $justificativa = 'Teste exceção com justificativa sendo removida';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('excecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => null,
            'data_final_excecao' => null,
            'justificativa' => $justificativa
        ])
        ->assertRedirect(route('sala.reuniao.suspensao.lista'));

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'data_inicial_excecao' => null,
            'data_final_excecao' => null,
            'justificativa' => json_encode([
                $suspenso->getJustificativas()[0],
                $suspenso->getJustificativas()[1],
                '[Funcionário(a) '.$user->nome.'] | [Ação - exceção] - ' . $justificativa . ' Data da justificativa: ' . formataData(now())
            ], JSON_FORCE_OBJECT),
            'situacao' => SuspensaoExcecao::SITUACAO_SUSPENSAO
        ]);
    }

    /** @test */
    public function excecao_cannot_be_edited_without_data_inicial_and_with_data_final()
    {
        $justificativa = 'Teste exceção com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => null,
            'data_final_excecao' => now()->format('Y-m-d'),
            'justificativa' => $justificativa
        ])
        ->assertSessionHasErrors([
            'data_inicial_excecao'
        ]);
    }

    /** @test */
    public function excecao_cannot_be_edited_with_data_inicial_and_without_data_final()
    {
        $justificativa = 'Teste exceção com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => null,
            'data_final_excecao' => now()->format('Y-m-d'),
            'justificativa' => $justificativa
        ])
        ->assertSessionHasErrors([
            'data_final_excecao'
        ]);
    }

    /** @test */
    public function excecao_cannot_be_edited_with_data_inicial_excecao_invalid()
    {
        $justificativa = 'Teste exceção com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => now()->format('d/m/Y'),
            'data_final_excecao' => now()->format('Y-m-d'),
            'justificativa' => $justificativa
        ])
        ->assertSessionHasErrors([
            'data_inicial_excecao'
        ]);
    }

    /** @test */
    public function excecao_cannot_be_edited_with_data_final_excecao_invalid()
    {
        $justificativa = 'Teste exceção com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->format('d/m/Y'),
            'justificativa' => $justificativa
        ])
        ->assertSessionHasErrors([
            'data_final_excecao'
        ]);
    }

    /** @test */
    public function excecao_cannot_be_edited_with_data_final_excecao_before_data_inicial_excecao()
    {
        $justificativa = 'Teste exceção com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->subDay()->format('Y-m-d'),
            'justificativa' => $justificativa
        ])
        ->assertSessionHasErrors([
            'data_final_excecao'
        ]);
    }

    /** @test */
    public function excecao_cannot_be_edited_with_data_inicial_excecao_before_today()
    {
        $justificativa = 'Teste exceção com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => now()->subDay()->format('Y-m-d'),
            'data_final_excecao' => now()->format('Y-m-d'),
            'justificativa' => $justificativa
        ])
        ->assertSessionHasErrors([
            'data_inicial_excecao'
        ]);
    }

    /** @test */
    public function excecao_cannot_be_edited_with_data_inicial_excecao_before_data_inicial_suspensao()
    {
        $justificativa = 'Teste exceção com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create([
            'data_inicial' => now()->addDay()->format('Y-m-d')
        ]);
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->format('Y-m-d'),
            'justificativa' => $justificativa
        ])
        ->assertSessionHasErrors([
            'data_inicial_excecao'
        ]);
    }

    /** @test */
    public function excecao_cannot_be_edited_with_data_final_excecao_after_data_final_suspensao()
    {
        $justificativa = 'Teste exceção com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create([
            'data_inicial' => now()->subDays(29)->format('Y-m-d'),
            'data_final' => now()->format('Y-m-d')
        ]);
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->addDay()->format('Y-m-d'),
            'justificativa' => $justificativa
        ])
        ->assertSessionHasErrors([
            'data_final_excecao'
        ]);
    }

    /** @test */
    public function excecao_cannot_be_edited_with_data_final_excecao_after_15_days_data_inicial_excecao()
    {
        $justificativa = 'Teste exceção com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->addDays(15)->format('Y-m-d'),
            'justificativa' => $justificativa
        ])
        ->assertSessionHasErrors([
            'data_final_excecao'
        ]);
    }

    /** @test */
    public function excecao_cannot_be_edited_without_justificativa()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->format('Y-m-d'),
            'justificativa' => null
        ])
        ->assertSessionHasErrors([
            'justificativa'
        ]);
    }

    /** @test */
    public function excecao_cannot_be_edited_with_justificativa_less_than_10_chars()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->format('Y-m-d'),
            'justificativa' => 'apdertflo'
        ])
        ->assertSessionHasErrors([
            'justificativa'
        ]);
    }

    /** @test */
    public function excecao_cannot_be_edited_with_justificativa_more_than_1000_chars()
    {
        $faker = \Faker\Factory::create();

        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create();
        $suspenso->updateRelacaoByIdRep(1);
        
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'excecao']))->assertOk();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->format('Y-m-d'),
            'justificativa' => $faker->sentence(300)
        ])
        ->assertSessionHasErrors([
            'justificativa'
        ]);
    }

    /** @test */
    public function view_excecao_is_updated()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('excecao')->create();
        $suspenso->updateRelacaoByIdRep(1);

        $this->get(route('sala.reuniao.suspensao.lista'))
        ->assertOk()
        ->assertSeeText('Cadastrado no Portal')
        ->assertSee($suspenso->cpf_cnpj)
        ->assertSeeText('Liberado por 1 dia')
        ->assertSee('<span class="text-success"><b>'.SuspensaoExcecao::SITUACAO_EXCECAO.'<b></span>');

        $this->get(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']))
        ->assertOk();

        $justificativa = 'Teste exceção com justificativa sendo removida';
        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => null,
            'data_final_excecao' => null,
            'justificativa' => $justificativa
        ]);

        $this->get(route('sala.reuniao.suspensao.lista'))
        ->assertOk()
        ->assertSeeText('Cadastrado no Portal')
        ->assertSee($suspenso->cpf_cnpj)
        ->assertSee('<span class="text-danger"><b>'.SuspensaoExcecao::SITUACAO_SUSPENSAO.'</b></span>');
    }

    /* AGENDAMENTO UPDATE */

    /** @test */
    public function suspensao_is_created_by_agendamento_refused()
    {
        $user = $this->signInAsAdmin();
        $agendamento = factory('App\AgendamentoSala')->states('justificado')->create();
        
        $this->put(route('sala.reuniao.agendados.update', [$agendamento->id, 'recusa']), [
            'justificativa_admin' => 'fgfgffgffgfffggfgfg'
        ]);

        $this->get(route('sala.reuniao.suspensao.lista'))
        ->assertOk()
        ->assertSeeText('Cadastrado no Portal')
        ->assertSee($agendamento->representante->cpf_cnpj)
        ->assertSee('<span class="text-danger"><b>'.SuspensaoExcecao::SITUACAO_SUSPENSAO.'</b></span>');
    }

    /** @test */
    public function log_is_generated_when_suspensao_is_created_by_agendamento_refused()
    {
        $user = $this->signInAsAdmin();
        $agendamento = factory('App\AgendamentoSala')->states('justificado')->create();
        
        $this->put(route('sala.reuniao.agendados.update', [$agendamento->id, 'recusa']), [
            'justificativa_admin' => 'fgfgffgffgfffggfgfg'
        ]);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';

        $texto = '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - Após análise da justificativa enviada pelo representante, o agendamento com o protocolo '. $agendamento->protocolo;
        $texto .= ' teve o status atualizado para ' . $agendamento::STATUS_NAO_COMPARECEU . ' devido a recusa.';
        $texto .= ' A justificativa do funcionário foi enviada por e-mail para o representante e está no agendamento. Então, o CPF / CNPJ ';
        $texto .= $agendamento->representante->cpf_cnpj . ' foi suspenso automaticamente por 30 dias';
        $texto .= ' a contar do dia ' . now()->format('d/m/Y') . '. Data da justificativa: ' . formataData(now());
        $this->assertStringContainsString($texto, $log);
    }
}
