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

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $faker = \Faker\Factory::create();

        $this->signIn();
        $this->assertAuthenticated('web');

        $suspenso = factory('App\SuspensaoExcecao')->create();
        
        $this->get(route('sala.reuniao.suspensao.lista'))->assertForbidden();
        $this->get(route('sala.reuniao.suspensao.view', $suspenso->id))->assertForbidden();
        $this->get(route('sala.reuniao.suspensao.edit', [$suspenso->id, 'suspensao']))->assertForbidden();
        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->format('Y-m-d'),
            'justificativa' => $faker->sentence(100)
        ])->assertForbidden();
        $this->get(route('sala.reuniao.suspensao.criar'))->assertForbidden();
        $this->post(route('sala.reuniao.suspensao.store'), [
            'cpf_cnpj' => '11748345000144',
            'data_inicial' => now()->format('Y-m-d'),
            'data_final' => now()->addMonth()->format('Y-m-d'),
            'justificativa' => $faker->sentence(100)
        ])->assertForbidden();
        $this->get(route('sala.reuniao.suspensao.busca'))->assertForbidden();
    }

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
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') criou período *suspensão do representante no agendamento de salas* (id: 1)';
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();

        $this->get(route('sala.reuniao.suspensao.lista'))
        ->assertOk()
        ->assertSeeText('Cadastrado no Portal')
        ->assertSee($suspenso->getCpfCnpj())
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'suspensao']), [
            'data_final' => '30',
            'justificativa' => $justificativa
        ]);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou período *suspensão do representante no agendamento de salas* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function suspensao_can_be_edited_add_days_with_data_final_null()
    {
        $justificativa = 'Teste editar suspensão com justificativa';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create([
            'data_final' => null
        ])->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        ])->fresh();
        
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
        ])->fresh();
        
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
        ])->fresh();
        
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
        ])->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();

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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();

        $this->put(route('sala.reuniao.suspensao.update', [$suspenso->id, 'excecao']), [
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->format('Y-m-d'),
            'justificativa' => $justificativa
        ]);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou período *exceção do representante no agendamento de salas* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function excecao_can_be_removed()
    {
        $justificativa = 'Teste exceção com justificativa sendo removida';
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('excecao')->create()->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        ])->fresh();
        
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
        ])->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        
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
        $suspenso = factory('App\SuspensaoExcecao')->states('excecao')->create()->fresh();

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

    /* VISUALIZAR DETALHES DA SUSPENSÃO E HISTÓRICO */

    /** @test */
    public function view_details_suspensao_without_excecao()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();

        $this->get(route('sala.reuniao.suspensao.view', $suspenso->id))
        ->assertOk()
        ->assertSee('<h4>Detalhes da suspensão do CPF / CNPJ <strong>'.$suspenso->getCpfCnpj().'</strong></h4>')
        ->assertSee('<p><b><span class="text-danger">Período da Suspensão:</span> </b>'.$suspenso->mostraPeriodo().' (<em>'.$suspenso->mostraPeriodoEmDias().'</em>)</p>')
        ->assertSee('<h5 class="mt-4"><b>Histórico de justificativas:</b></h5>')
        ->assertSee('<p>'.$suspenso->getJustificativasDesc()[0].'</p>');
    }

    /** @test */
    public function view_details_suspensao_with_excecao()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('excecao')->create()->fresh();
        
        $this->get(route('sala.reuniao.suspensao.view', $suspenso->id))
        ->assertOk()
        ->assertSee('<h4>Detalhes da suspensão do CPF / CNPJ <strong>'.$suspenso->getCpfCnpj().'</strong></h4>')
        ->assertSee('<p><b><span class="text-danger">Período da Suspensão:</span> </b>'.$suspenso->mostraPeriodo().' (<em>'.$suspenso->mostraPeriodoEmDias().'</em>)</p>')
        ->assertSee('<p><b><span class="text-success">Período da Exceção:</span> </b>'.$suspenso->mostraPeriodoExcecao().' (<em>'.$suspenso->mostraPeriodoExcecaoEmDias().'</em>)</p>')
        ->assertSee('<h5 class="mt-4"><b>Histórico de justificativas:</b></h5>')
        ->assertSee('<p>'.$suspenso->getJustificativasDesc()[1].'</p>')
        ->assertSee('<p>'.$suspenso->getJustificativasDesc()[0].'</p>');
    }

    /** @test */
    public function view_details_suspensao_with_agendamento()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->states('justificativa_recusada')->create()->fresh();
        $protocolo = $suspenso->agendamento->protocolo;
        
        $this->get(route('sala.reuniao.suspensao.view', $suspenso->id))
        ->assertOk()
        ->assertSee('<h4>Detalhes da suspensão do CPF / CNPJ <strong>'.$suspenso->getCpfCnpj().'</strong></h4>')
        ->assertSee('<p><b><span class="text-danger">Período da Suspensão:</span> </b>'.$suspenso->mostraPeriodo().' (<em>'.$suspenso->mostraPeriodoEmDias().'</em>)</p>')
        ->assertSee('<h5 class="mt-4"><b>Histórico de justificativas:</b></h5>')
        ->assertSee('<a href="'.route('sala.reuniao.agendados.busca', ['q' => $protocolo]).'" target="_blank">'.$protocolo.'</a>&nbsp;&nbsp;<b>|</b>')
        ->assertSee('<p>'.$suspenso->getJustificativasDesc()[0].'</p>');
    }

    /* AGENDAMENTO ADMIN UPDATE */

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

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'agendamento_sala_id' => $agendamento->id,
            'situacao' => SuspensaoExcecao::SITUACAO_SUSPENSAO
        ]);
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

    /** @test */
    public function suspensao_is_updated_by_agendamento_refused()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create([
            'data_inicial' => now()->subDays(15)->format('Y-m-d'),
            'data_final' => now()->subDays(15)->addDays(30)->format('Y-m-d'),
        ])->fresh();

        $agendamento = factory('App\AgendamentoSala')->states('justificado')->create([
            'idrepresentante' => 1
        ]);

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'data_inicial' => now()->subDays(15)->format('Y-m-d'),
            'data_final' => now()->subDays(15)->addDays(30)->format('Y-m-d'),
            'agendamento_sala_id' => null,
            'situacao' => SuspensaoExcecao::SITUACAO_SUSPENSAO
        ]);
        
        $this->put(route('sala.reuniao.agendados.update', [$agendamento->id, 'recusa']), [
            'justificativa_admin' => 'fgfgffgffgfffggfgfg'
        ]);

        $this->get(route('sala.reuniao.suspensao.lista'))
        ->assertOk()
        ->assertSeeText('Cadastrado no Portal')
        ->assertSee($agendamento->representante->cpf_cnpj)
        ->assertSee('<span class="text-danger"><b>'.SuspensaoExcecao::SITUACAO_SUSPENSAO.'</b></span>');

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'data_inicial' => now()->format('Y-m-d'),
            'data_final' => now()->addDays(30)->format('Y-m-d'),
            'agendamento_sala_id' => $agendamento->id,
            'situacao' => SuspensaoExcecao::SITUACAO_SUSPENSAO
        ]);

        $this->assertEquals(SuspensaoExcecao::count(), 1);
    }

    /** @test */
    public function log_is_generated_when_suspensao_is_updated_by_agendamento_refused_with_tempo_indeterminado()
    {
        $user = $this->signInAsAdmin();
        $suspenso = factory('App\SuspensaoExcecao')->create([
            'data_inicial' => now()->subDays(15)->format('Y-m-d'),
            'data_final' => null,
        ])->fresh();
        $agendamento = factory('App\AgendamentoSala')->states('justificado')->create([
            'idrepresentante' => 1
        ]);
        
        $this->put(route('sala.reuniao.agendados.update', [$agendamento->id, 'recusa']), [
            'justificativa_admin' => 'fgfgffgffgfffggfgfg'
        ]);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';

        $texto = '[Funcionário(a) '.$user->nome.'] | [Ação - suspensão] - Após análise da justificativa enviada pelo representante, o agendamento com o protocolo '. $agendamento->protocolo;
        $texto .= ' teve o status atualizado para ' . $agendamento::STATUS_NAO_COMPARECEU . ' devido a recusa.';
        $texto .= ' A justificativa do funcionário foi enviada por e-mail para o representante e está no agendamento. Então, o CPF / CNPJ ';
        $texto .= $agendamento->representante->cpf_cnpj . ' foi mantida a suspensão por tempo indeterminado';
        $texto .= ' a contar do dia ' . now()->format('d/m/Y') . '. Data da justificativa: ' . formataData(now());
        $this->assertStringContainsString($texto, $log);
    }

    /* ÁREA DO RC */

    /** @test */
    public function representante_cannot_created_agendamento_when_suspensao_is_created()
    {
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        $representante = $suspenso->representante;

        $this->actingAs($representante, 'representante');

        $agenda = factory('App\AgendamentoSala')->raw();

        $justificativa = $suspenso->getJustificativasDesc($suspenso->getJustificativasByAcao('suspensão'))[0];

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-ban"></i>&nbsp;&nbsp;Está suspenso pelo período de <b>' . $suspenso->mostraPeriodo().'</b>')
        ->assertSee('<br><br>Durante a suspensão não pode criar novos agendamentos e nem participar de novas reuniões.')
        ->assertSee('<br>Os agendamentos e participações já criados não são afetados.')
        ->assertSee('<br><b>Última justificativa de suspensão:</b> '.$suspenso->removeNomeAcaoJustificativa($justificativa, 'suspensão'));

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => Carbon::parse($agenda['dia'])->format('d/m/Y'), 
            'periodo' => 'tarde',
            'aceite' => 'on'
        ])
        ->assertRedirect(route('representante.agendar.inserir.view'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-ban"></i>&nbsp;&nbsp;Está suspenso pelo período de <b>' . $suspenso->mostraPeriodo().'</b>')
        ->assertSee('<br><br>Durante a suspensão não pode criar novos agendamentos e nem participar de novas reuniões.')
        ->assertSee('<br>Os agendamentos e participações já criados não são afetados.')
        ->assertSee('<br><b>Última justificativa de suspensão:</b> '.$suspenso->removeNomeAcaoJustificativa($justificativa, 'suspensão'));
    }

    /** @test */
    public function representante_cannot_created_agendamento_with_participante_suspenso()
    {
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);

        $this->actingAs($representante1, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '862.943.730-85'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participante_suspenso'
        ]);
    }

    /** @test */
    public function representante_cannot_created_agendamento_with_participante_suspenso_msg_singular()
    {
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);

        $this->actingAs($representante1, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '862.943.730-85'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participante_suspenso'
        ]);

        $this->assertEquals(session()->get('errors')->first('participante_suspenso'), 
        'O seguinte participante está suspenso para novos agendamentos:<br><strong>862.943.730-85</strong>');
    }

    /** @test */
    public function representante_cannot_created_agendamento_with_participante_suspenso_msg_plural()
    {
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        $suspenso2 = factory('App\SuspensaoExcecao')->create([
            'cpf_cnpj' => '56983238010'
        ])->fresh();
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);

        $this->actingAs($representante1, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw();

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '862.943.730-85'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'aceite' => 'on'
        ])
        ->assertSessionHasErrors([
            'participante_suspenso'
        ]);

        $this->assertEquals(session()->get('errors')->first('participante_suspenso'), 
        'Os seguintes participantes estão suspensos para novos agendamentos:<br><strong>862.943.730-85<br>569.832.380-10</strong>');
    }

    /** @test */
    public function representante_can_cancel_agendamento_when_suspensao_is_created()
    {
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        $representante = $suspenso->representante;

        $this->actingAs($representante, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $justificativa = $suspenso->getJustificativasDesc($suspenso->getJustificativasByAcao('suspensão'))[0];

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-ban"></i>&nbsp;&nbsp;Está suspenso pelo período de <b>' . $suspenso->mostraPeriodo().'</b>')
        ->assertSee('<br><br>Durante a suspensão não pode criar novos agendamentos e nem participar de novas reuniões.')
        ->assertSee('<br>Os agendamentos e participações já criados não são afetados.')
        ->assertSee('<br><b>Última justificativa de suspensão:</b> '.$suspenso->removeNomeAcaoJustificativa($justificativa, 'suspensão'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'cancelar', 'id' => $agenda->id]).'" class="btn btn-danger btn-sm link-nostyle mt-2">Cancelar</a>');

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
    public function representante_can_edit_agendamento_when_suspensao_is_created()
    {
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        $representante = $suspenso->representante;
        $this->actingAs($representante, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $justificativa = $suspenso->getJustificativasDesc($suspenso->getJustificativasByAcao('suspensão'))[0];

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-ban"></i>&nbsp;&nbsp;Está suspenso pelo período de <b>' . $suspenso->mostraPeriodo().'</b>')
        ->assertSee('<br><br>Durante a suspensão não pode criar novos agendamentos e nem participar de novas reuniões.')
        ->assertSee('<br>Os agendamentos e participações já criados não são afetados.')
        ->assertSee('<br><b>Última justificativa de suspensão:</b> '.$suspenso->removeNomeAcaoJustificativa($justificativa, 'suspensão'));

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'editar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSeeText('Salvar');

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'editar',
            'id' => $agenda->id
        ]), [
            'participantes_cpf' => ['56983238010'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
        ])->assertStatus(302);

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
    public function representante_can_justify_agendamento_when_suspensao_is_created()
    {
        $suspenso = factory('App\SuspensaoExcecao')->create()->fresh();
        $representante = $suspenso->representante;
        $this->actingAs($representante, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create([
            'dia' => now()->format('Y-m-d')
        ]);

        $justificativa = $suspenso->getJustificativasDesc($suspenso->getJustificativasByAcao('suspensão'))[0];

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-ban"></i>&nbsp;&nbsp;Está suspenso pelo período de <b>' . $suspenso->mostraPeriodo().'</b>')
        ->assertSee('<br><br>Durante a suspensão não pode criar novos agendamentos e nem participar de novas reuniões.')
        ->assertSee('<br>Os agendamentos e participações já criados não são afetados.')
        ->assertSee('<br><b>Última justificativa de suspensão:</b> '.$suspenso->removeNomeAcaoJustificativa($justificativa, 'suspensão'));

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<a href="'.route('representante.agendar.inserir.view', ['acao' => 'justificar', 'id' => $agenda->id]).'" class="btn btn-sm btn-dark link-nostyle mt-2">Justificar</a>');

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'justificar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSeeText('Justificar');

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'justificar', 'id' => $agenda->id
        ]), [
            'justificativa' => 'dfdfdfdfdfdfdfdfdfdfdfdfdf',
        ])
        ->assertStatus(302);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento justificado com sucesso! Está em análise do atendente. Foi enviado um e-mail com a sua justificativa.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'status' => 'Justificativa Enviada'
        ]);
    }

    /** @test */
    public function representante_can_created_agendamento_when_excecao_is_created()
    {
        $suspenso = factory('App\SuspensaoExcecao')->states('excecao')->create()->fresh();
        $representante = $suspenso->representante;

        $this->actingAs($representante, 'representante');

        $agenda = factory('App\AgendamentoSala')->raw();

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Está liberado temporariamente pelo período de <b>'.$suspenso->mostraPeriodoExcecao().'</b>')
        ->assertSee(' o acesso para criar novos agendamentos e participar de novas reuniões, independentemente do dia do agendamento.');

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => 'coworking',
            'sala_reuniao_id' => $agenda['sala_reuniao_id'], 
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'aceite' => 'on'
        ])->assertStatus(302);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento criado com sucesso! Foi enviado um e-mail com os detalhes.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'coworking',
            'idrepresentante' => 1,
        ]);
    }

    /** @test */
    public function representante_can_create_agendamento_with_participante_excecao()
    {
        $suspenso = factory('App\SuspensaoExcecao')->states('excecao')->create()->fresh();
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);

        $this->actingAs($representante1, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->raw([
            'participantes' => json_encode(['56983238010' => 'NOME PARTICIPANTE UM', '86294373085' => 'NOME PARTICIPANTE DOIS'], JSON_FORCE_OBJECT)
        ]);

        $this->post(route('representante.agendar.inserir.post', 'agendar'), [
            'tipo_sala' => $agenda['tipo_sala'],
            'sala_reuniao_id' => $agenda['sala_reuniao_id'],
            'dia' => onlyDate($agenda['dia']), 
            'periodo' => $agenda['periodo'],
            'participantes_cpf' => ['569.832.380-10', '862.943.730-85'],
            'participantes_nome' => ['NOME PARTICIPANTE UM', 'NOME PARTICIPANTE DOIS'],
            'aceite' => 'on'
        ])->assertStatus(302);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Agendamento criado com sucesso! Foi enviado um e-mail com os detalhes.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'reuniao',
            'idrepresentante' => $representante1->id,
            'participantes' => $agenda['participantes']
        ]);
    }

    /** @test */
    public function representante_can_edit_agendamento_with_participante_excecao()
    {
        $suspenso = factory('App\SuspensaoExcecao')->states('excecao')->create()->fresh();
        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);

        $this->actingAs($representante1, 'representante');

        $agenda = factory('App\AgendamentoSala')->states('reuniao')->create();

        $this->get(route('representante.agendar.inserir.view', ['acao' => 'editar', 'id' => $agenda->id]))
        ->assertOk()
        ->assertSeeText('Salvar');

        $this->put(route('representante.agendar.inserir.put', [
            'acao' => 'editar',
            'id' => $agenda->id
        ]), [
            'participantes_cpf' => ['56983238010'],
            'participantes_nome' => ['NOME PARTICIPANTE UM'],
        ])->assertStatus(302);

        $this->get(route('representante.agendar.inserir.view'))
        ->assertSee('<i class="fas fa-check"></i>&nbsp;&nbsp;Participantes foram alterados com sucesso! Foi enviado um e-mail com os detalhes.');

        $this->assertDatabaseHas('agendamentos_salas', [
            'tipo_sala' => 'reuniao',
            'idrepresentante' => $representante1->id,
            'participantes' => json_encode([
                '56983238010' => 'NOME PARTICIPANTE UM'
            ], JSON_FORCE_OBJECT)
        ]);
    }

    /* ROTINAS KERNEL */

    private function create_suspensoes_rotina()
    {
        // excluir
        $suspenso = factory('App\SuspensaoExcecao')->create([
            'data_final' => now()->subDay()->format('Y-m-d')
        ])->fresh();

        // atualizar situação para liberado
        $suspenso1 = factory('App\SuspensaoExcecao')->create([
            'cpf_cnpj' => '22233366699989',
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->addDay()->format('Y-m-d'),
        ])->fresh();

        // excluir
        $suspenso2 = factory('App\SuspensaoExcecao')->create([
            'cpf_cnpj' => '11111111111',
            'data_final' => now()->subDay()->format('Y-m-d')
        ])->fresh();

        // atualizar situação para suspenso
        $suspenso3 = factory('App\SuspensaoExcecao')->states('excecao')->create([
            'cpf_cnpj' => '22333699545',
            'data_inicial_excecao' => now()->subDay()->format('Y-m-d'),
            'data_final_excecao' => now()->subDay()->format('Y-m-d'),
        ])->fresh();

        // manter
        $suspenso4 = factory('App\SuspensaoExcecao')->create([
            'cpf_cnpj' => '22233366699',
            'data_final' => null
        ])->fresh();

        // atualizar situação para liberado
        $suspenso5 = factory('App\SuspensaoExcecao')->create([
            'cpf_cnpj' => '33366699990',
            'data_inicial_excecao' => now()->format('Y-m-d'),
            'data_final_excecao' => now()->format('Y-m-d'),
        ])->fresh();

        $representante1 = factory('App\Representante')->create([
            'cpf_cnpj' => '73525258000185'
        ]);

        // atualizar relação
        $suspenso6 = factory('App\SuspensaoExcecao')->states('excecao')->create([
            'cpf_cnpj' => '73525258000185',
        ])->fresh();

        return [
            $suspenso, $suspenso1, $suspenso2, $suspenso3, $suspenso4, $suspenso5, $suspenso6
        ];
    }

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
        ]);

        // manter
        $agendamento3 = factory('App\AgendamentoSala')->states('justificado')->create([
            'idrepresentante' => factory('App\Representante')->create([
                'cpf_cnpj' => '44455566677'
            ])
        ]);

        return [
            $agendamento, $agendamento1, $agendamento2, $agendamento3
        ];
    }

    /** @test */
    public function rotina_suspensoes_kernel()
    {
        $suspensoes = $this->create_suspensoes_rotina();

        $service = resolve('App\Contracts\MediadorServiceInterface');
        $service->getService('SalaReuniao')->suspensaoExcecao()->executarRotina($service);

        $this->assertSoftDeleted('suspensoes_excecoes', [
            'idrepresentante' => 1,
            'id' => $suspensoes[0]->id,
        ]);

        $this->assertSoftDeleted('suspensoes_excecoes', [
            'cpf_cnpj' => $suspensoes[2]->cpf_cnpj,
            'id' => $suspensoes[2]->id,
        ]);

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'cpf_cnpj' => $suspensoes[1]->cpf_cnpj,
            'id' => $suspensoes[1]->id,
            'situacao' => SuspensaoExcecao::SITUACAO_EXCECAO
        ]);

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'cpf_cnpj' => $suspensoes[5]->cpf_cnpj,
            'id' => $suspensoes[5]->id,
            'situacao' => SuspensaoExcecao::SITUACAO_EXCECAO
        ]);

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'cpf_cnpj' => $suspensoes[3]->cpf_cnpj,
            'id' => $suspensoes[3]->id,
            'situacao' => SuspensaoExcecao::SITUACAO_SUSPENSAO
        ]);

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'cpf_cnpj' => $suspensoes[4]->cpf_cnpj,
            'id' => $suspensoes[4]->id,
            'situacao' => SuspensaoExcecao::SITUACAO_SUSPENSAO
        ]);

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'cpf_cnpj' => null,
            'idrepresentante' => $suspensoes[6]->idrepresentante,
            'id' => $suspensoes[6]->id,
            'situacao' => SuspensaoExcecao::SITUACAO_EXCECAO
        ]);
    }

    /** @test */
    public function rotina_suspensoes_by_agendamentos_kernel()
    {
        $agendamentos = $this->create_agendamentos_rotina();

        $service = resolve('App\Contracts\MediadorServiceInterface');
        $service->getService('SalaReuniao')->agendados()->executarRotina();

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'cpf_cnpj' => null,
            'idrepresentante' => $agendamentos[0]->idrepresentante,
            'agendamento_sala_id' => $agendamentos[0]->id,
            'situacao' => SuspensaoExcecao::SITUACAO_SUSPENSAO,
            'data_inicial' => now()->format('Y-m-d'),
            'data_final' => now()->addDays(30)->format('Y-m-d'),
            'justificativa' => json_encode([
                '[Rotina Portal - Sala de Reunião] | [Ação - suspensão] - Após verificação dos agendamentos, o agendamento com o protocolo '. $agendamentos[0]->protocolo.
                ' teve o status atualizado para ' . $agendamentos[0]::STATUS_NAO_COMPARECEU . ' devido ao não envio de justificativa. Então, o CPF / CNPJ '.
                $agendamentos[0]->representante->cpf_cnpj.' foi suspenso automaticamente por 30 dias a contar do dia ' . now()->format('d/m/Y') . '. Data da justificativa: ' . formataData(now())
            ], JSON_FORCE_OBJECT)
        ]);

        $this->assertDatabaseHas('suspensoes_excecoes', [
            'cpf_cnpj' => null,
            'idrepresentante' => $agendamentos[2]->idrepresentante,
            'agendamento_sala_id' => $agendamentos[2]->id,
            'situacao' => SuspensaoExcecao::SITUACAO_SUSPENSAO,
            'data_inicial' => now()->format('Y-m-d'),
            'data_final' => now()->addDays(30)->format('Y-m-d'),
            'justificativa' => json_encode([
                '[Rotina Portal - Sala de Reunião] | [Ação - suspensão] - Após verificação dos agendamentos, o agendamento com o protocolo '. $agendamentos[2]->protocolo.
                ' teve o status atualizado para ' . $agendamentos[2]::STATUS_NAO_COMPARECEU . ' devido ao não envio de justificativa. Então, o CPF / CNPJ '.
                $agendamentos[2]->representante->cpf_cnpj.' foi suspenso automaticamente por 30 dias a contar do dia ' . now()->format('d/m/Y') . '. Data da justificativa: ' . formataData(now())
            ], JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function log_is_generated_when_suspensoes_by_agendamentos_kernel()
    {
        $agendamentos = $this->create_agendamentos_rotina();

        $service = resolve('App\Contracts\MediadorServiceInterface');
        $service->getService('SalaReuniao')->agendados()->executarRotina();


        $log = explode(PHP_EOL, tailCustom(storage_path($this->pathLogInterno()), 2));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [Rotina Portal - Sala de Reunião] | [Ação - suspensão] - ';
        $txt = $inicio . 'Após verificação dos agendamentos, o agendamento com o protocolo '. $agendamentos[0]->protocolo. ' teve o status atualizado para ';
        $txt .= $agendamentos[0]::STATUS_NAO_COMPARECEU . ' devido ao não envio de justificativa. Então, o CPF / CNPJ '.$agendamentos[0]->representante->cpf_cnpj;
        $txt .= ' foi suspenso automaticamente por 30 dias a contar do dia ' . now()->format('d/m/Y') . '. Data da justificativa: ' . formataData(now());
        $this->assertStringContainsString($txt, $log[0]);

        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [Rotina Portal - Sala de Reunião] | [Ação - suspensão] - ';
        $txt = $inicio . 'Após verificação dos agendamentos, o agendamento com o protocolo '. $agendamentos[2]->protocolo. ' teve o status atualizado para ';
        $txt .= $agendamentos[2]::STATUS_NAO_COMPARECEU . ' devido ao não envio de justificativa. Então, o CPF / CNPJ '.$agendamentos[2]->representante->cpf_cnpj;
        $txt .= ' foi suspenso automaticamente por 30 dias a contar do dia ' . now()->format('d/m/Y') . '. Data da justificativa: ' . formataData(now());
        $this->assertStringContainsString($txt, $log[1]);
    }
}
