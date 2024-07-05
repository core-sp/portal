<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\PreRegistro;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Anexo;
use Illuminate\Foundation\Testing\WithFaker;

class PreRegistroCpfTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function can_new_pre_registro_pf()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.preregistro.view'))->assertOk();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistro = $externo->load('preRegistro')->preRegistro;

        $this->assertDatabaseHas('pre_registros', [
            'id' => $preRegistro->id,
        ]);

        $this->assertDatabaseHas('pre_registros_cpf', [
            'id' => $preRegistro->pessoaFisica->id,
        ]);
    }

    /** @test */
    public function cannot_new_pre_registro_pf_without_check()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.preregistro.view'))->assertOk();
        $this->get(route('externo.inserir.preregistro.view'))
        ->assertRedirect(route('externo.preregistro.view'));
    }

    /** @test */
    public function cannot_new_pre_registro_pf_when_access_others_routes()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.preregistro.view'))->assertOk();

        $this->assertEquals(PreRegistro::count(), 0);

        $this->post(route('externo.inserir.preregistro.ajax', [
            'classe' => 'preRegistro',
            'campo' => 'segmento',
            'valor' => 'Abrasivos'
            ]))->assertStatus(401);
        $this->assertEquals(PreRegistro::count(), 0);

        $this->put(route('externo.verifica.inserir.preregistro'))->assertNotFound();
        $this->assertEquals(PreRegistro::count(), 0);

        $this->put(route('externo.inserir.preregistro'))->assertUnauthorized();
        $this->assertEquals(PreRegistro::count(), 0);
    }

    /** @test */
    public function log_is_generated_when_form_cpf_is_created()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));     

        $pr = PreRegistro::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário Externo com cpf: ' . $pr->userExterno->cpf_cnpj.', iniciou o processo de solicitação de registro com a id: ' . $pr->id;
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function view_msg_update()
    {
        $externo = $this->signInAsUserExterno();
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));

        PreRegistro::first()->update(['updated_at' => PreRegistro::first()->updated_at->subHour()]);
        $atual = PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s');

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaFisica',
            'campo' => 'sexo',
            'valor' => 'O'
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
        $this->assertNotEquals($atual, PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
    }

    /** @test */
    public function can_create_new_register_pre_registros_cpf_after_negado()
    {
        $externo = $this->signInAsUserExterno();
        factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create(),
        ]);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $this->assertDatabaseHas('pre_registros_cpf', [
            'pre_registro_id' => 2
        ]);
    }

    /** @test */
    public function cannot_create_new_register_pre_registros_cpf_after_aprovado()
    {
        $externo = $this->signInAsUserExterno();
        factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create(),
        ]);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertRedirect(route('externo.preregistro.view'));
        
        $this->assertDatabaseMissing('pre_registros_cpf', [
            'pre_registro_id' => 2
        ]);
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CPF VIA AJAX - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function can_update_table_pre_registros_cpf_by_ajax()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf->toArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cpf_by_ajax_with_upperCase()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('low')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id,
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            if(isset($value))
                $preRegistroCpf[$key] = mb_strtoupper($value, 'UTF-8');

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf->toArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cpf_by_ajax_when_exists_others_pre_registros()
    {
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1970-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ]);

        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1975-10-15',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => $preRegistroCpf_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ]);

        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf->toArray());
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2->attributesToArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cpf_by_ajax_when_exists_others_pre_registros_with_same_user_and_negado()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create([
                'contabil_id' => null,
            ])
        ]);

        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create([
                'contabil_id' => null,
            ])
        ]);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => factory('App\PreRegistro')->make(),
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf->toArray());
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_when_exists_others_pre_registros_with_same_user()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create([
                'contabil_id' => null,
            ])
        ]);

        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create([
                'contabil_id' => null,
            ])
        ]);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertRedirect(route('externo.preregistro.view'));

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => factory('App\PreRegistro')->make(),
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(401);

        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf->toArray());
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ])->makeHidden(['pre_registro_id']);

        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridicaErro',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_with_input_type_text_more_191_chars()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = [
            'nome_social' => $this->faker()->text(500),
            'naturalidade_cidade' => $this->faker()->text(500),
            'nome_mae' => $this->faker()->text(500),
            'nome_pai' => $this->faker()->text(500),
            'identidade' => $this->faker()->text(500),
            'orgao_emissor' => $this->faker()->text(500),
            'titulo_eleitor' => $this->faker()->text(500),
            'zona' => $this->faker()->text(500),
            'secao' => $this->faker()->text(500),
            'ra_reservista' => $this->faker()->text(500),
        ];
        
        foreach($preRegistroCpf as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('valor');
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_under_18_years_old()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaFisica',
            'campo' => 'dt_nascimento',
            'valor' => Carbon::today()->subYears(17)->format('Y-m-d')
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', [
            'dt_nascimento' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_with_sexo_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaFisica',
            'campo' => 'sexo',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', [
            'sexo' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_with_estado_civil_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaFisica',
            'campo' => 'estado_civil',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', [
            'estado_civil' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_with_nacionalidade_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaFisica',
            'campo' => 'nacionalidade',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', [
            'nacionalidade' => 'BRASILEIRA'
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_with_naturalidade_estado_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaFisica',
            'campo' => 'naturalidade_estado',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', [
            'naturalidade_estado' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_with_tipo_identidade_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaFisica',
            'campo' => 'tipo_identidade',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', [
            'tipo_identidade' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_with_dt_expedicao_after_today()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaFisica',
            'campo' => 'dt_expedicao',
            'valor' => Carbon::today()->addDay()->format('Y-m-d')
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', [
            'dt_expedicao' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_without_date_type()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $datas = [
            'dt_nascimento' => null, 
            'dt_expedicao' => null
        ];

        foreach($datas as $key => $value) 
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => 'texto'
            ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', $datas);
    }

    /** @test */
    public function can_update_table_pre_registros_cpf_by_ajax_when_clean_inputs()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistroCpf = factory('App\PreRegistroCpf')->create();

        $preRegistroCpf = $preRegistroCpf->makeHidden(['id', 'pre_registro_id', 'updated_at', 'created_at'])->attributesToArray();

        foreach($preRegistroCpf as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => ''
            ])->assertStatus(200);
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf);
    }

    // Status do pré-registro

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistroCpf = factory('App\PreRegistroCpf')->create();

        $preRegistroCpf = $preRegistroCpf->makeHidden(['id', 'pre_registro_id', 'updated_at', 'created_at']);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                foreach($preRegistroCpf->attributesToArray() as $key => $value)
                    $this->post(route('externo.inserir.preregistro.ajax'), [
                        'classe' => 'pessoaFisica',
                        'campo' => $key,
                        'valor' => ''
                    ])->assertStatus(401);
        }
    }

    /** @test */
    public function can_update_table_pre_registros_cpf_by_ajax_with_status_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistroCpf = factory('App\PreRegistroCpf')->create();

        $preRegistroCpf = $preRegistroCpf->makeHidden(['id', 'pre_registro_id', 'updated_at', 'created_at']);

        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            foreach($preRegistroCpf->attributesToArray() as $key => $value)
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'pessoaFisica',
                    'campo' => $key,
                    'valor' => ''
                ])->assertStatus(200);
        }
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CPF VIA SUBMIT - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function can_submit_pre_registro_cpf()
    {
        Mail::fake();

        $externo = $this->signInAsUserExterno();

        $pr = factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertOk();

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $pr->preRegistro->status = PreRegistro::STATUS_ANALISE_INICIAL;

        Mail::assertQueued(PreRegistroMail::class);

        $this->assertDatabaseHas('pre_registros_cpf', $pr->attributesToArray());

        $this->assertDatabaseHas('pre_registros', Arr::except($pr->preRegistro->attributesToArray(), [
            'historico_contabil', 'historico_status', 'historico_justificativas', 'campos_espelho', 'updated_at'
        ]));

        $this->assertDatabaseHas('contabeis', $pr->preRegistro->contabil->attributesToArray());

        $this->assertDatabaseHas('anexos', [
            'pre_registro_id' => 1
        ]);

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registro_cpf_if_nacionalidade_different_option_brasileira()
    {        
        $externo = $this->signInAsUserExterno();

        $pr = factory('App\PreRegistroCpf')->create([
            'nacionalidade' => 'CHILENA',
            'naturalidade_cidade' => null,
            'naturalidade_estado' => null,
            'titulo_eleitor' => null,
            'zona' => null,
            'secao' => null,
            'ra_reservista' => null,
        ]);

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('pre_registros_cpf', $pr->attributesToArray());

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registros_cpf_when_exists_others_pre_registros()
    {
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1970-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1975-10-15',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ])->attributesToArray();

        $externo = $this->signInAsUserExterno();

        $pr = factory('App\PreRegistroCpf')->create()->attributesToArray();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('pre_registros_cpf', $pr);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());

        $this->assertEquals(PreRegistro::find(3)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registros_cpf_when_exists_others_pre_registros_with_same_user_and_negado()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();

        $pr = factory('App\PreRegistroCpf')->create()->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('pre_registros_cpf', $pr);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());

        $this->assertEquals(PreRegistro::find(3)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function cannot_submit_pre_registros_cpf_when_exists_others_pre_registros_with_same_user()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();

        $pr = factory('App\PreRegistroCpf')->raw();
        Anexo::find(3)->delete();
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertStatus(500);

        $this->put(route('externo.inserir.preregistro'))
        ->assertUnauthorized();

        $this->assertDatabaseMissing('pre_registros_cpf', $pr);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2);
        $this->assertDatabaseMissing('pre_registros_cpf', ['id' => 3]);
    }

    /** @test */
    public function can_submit_pre_registros_cpf_when_exists_others_pre_registros_with_same_contabil()
    {
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1970-03-10',
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1975-10-15',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ])->attributesToArray();

        $externo = $this->signInAsUserExterno();

        $pr = factory('App\PreRegistroCpf')->create()->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('pre_registros_cpf', $pr);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());

        $this->assertEquals(PreRegistro::find(3)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registro_cpf_without_optional_inputs()
    {
        $externo = $this->signInAsUserExterno();

        $prCpf = factory('App\PreRegistroCpf')->create([
            'sexo' => 'F',
            'nome_social' => null,
            'estado_civil' => null,
            'nome_pai' => null,
            'ra_reservista' => null,
        ])->attributesToArray();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cpf', $prCpf);

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function cannot_submit_pre_registro_cpf_without_required_inputs()
    {
        $externo = $this->signInAsUserExterno();

        $prCpf = factory('App\PreRegistroCpf')->create([
            'sexo' => null,
            'dt_nascimento' => null,
            'nacionalidade' => null,
            'nome_mae' => null,
            'tipo_identidade' => null,
            'identidade' => null,
            'orgao_emissor' => null,
            'dt_expedicao' => null,
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors([
            'sexo','dt_nascimento', 'nacionalidade','nome_mae','tipo_identidade','identidade','orgao_emissor','dt_expedicao'
        ]);

        $this->assertDatabaseHas('pre_registros', $prCpf->preRegistro->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cpf', $prCpf->attributesToArray());

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_CRIADO);
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'nome_social' => 'Nome'
        ]);
                
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'nome_social' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_with_numbers()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'nome_social' => 'Nome Socia1'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_sexo()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'sexo' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('sexo');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_sexo_value_wrong()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'sexo' => 'B'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('sexo');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_dt_nascimento()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_incorrect_format()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '2000/12/21'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_without_date_type()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => 'texto'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_under_18_years_old()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => Carbon::today()->subYears(17)->format('Y-m-d')
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_estado_civil_wrong_value()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'estado_civil' => 'Qualquer um'
        ]);
       
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('estado_civil');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_nacionalidade()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'nacionalidade' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nacionalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nacionalidade_with_value_wrong()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'nacionalidade' => 'Qualquer'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nacionalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_naturalidade_cidade_when_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'naturalidade_cidade' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('naturalidade_cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_naturalidade_cidade_less_than_4_chars_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'naturalidade_cidade' => 'Qua'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('naturalidade_cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_naturalidade_cidade_more_than_191_chars_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'naturalidade_cidade' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('naturalidade_cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_naturalidade_estado_when_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'naturalidade_estado' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('naturalidade_estado');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_naturalidade_estado_with_value_wrong_when_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'naturalidade_estado' => 'UF'
        ]);
            
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('naturalidade_estado');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_nome_mae()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'nome_mae' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'nome_mae' => 'Nome'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'nome_mae' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_with_numbers()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'nome_mae' => 'N0me Mãe'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_pai_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'nome_pai' => 'Nome'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_pai_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'nome_pai' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_pai_with_numbers()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'nome_pai' => 'Nom3 pai'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_tipo_identidade()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'tipo_identidade' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('tipo_identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_tipo_identidade_with_wrong_value()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'tipo_identidade' => 'Teste'
        ]);
    
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('tipo_identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_identidade()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'identidade' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_identidade_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'identidade' => '123'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_identidade_more_than_30_chars()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'identidade' => '1234567890123456789012345678901'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_orgao_emissor()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'orgao_emissor' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_orgao_emissor_less_than_3_chars()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'orgao_emissor' => 'SS'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_orgao_emissor_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'orgao_emissor' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_dt_expedicao()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'dt_expedicao' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_expedicao_incorrect_format()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'dt_expedicao' => '2000/12/21'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_expedicao_without_date_type()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'dt_expedicao' => 'text'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_expedicao_after_today()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'dt_expedicao' => Carbon::today()->addDay()->format('Y-m-d')
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_titulo_eleitor_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'titulo_eleitor' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('titulo_eleitor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_titulo_eleitor_less_than_12_chars_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'titulo_eleitor' => '23569874521'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('titulo_eleitor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_titulo_eleitor_more_than_15_chars_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'titulo_eleitor' => '2356987452123658'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('titulo_eleitor');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_zona_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'zona' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('zona');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_zona_more_than_6_chars_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'zona' => '7536985'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('zona');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_secao_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'secao' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('secao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_secao_more_than_8_chars_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'secao' => '753698575'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('secao');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_ra_reservista_if_sexo_m_and_under_45_years_old()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => now()->subYears(35)->format('Y-m-d'),
            'ra_reservista' => '',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('ra_reservista');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_ra_reservista_less_than_12_chars_if_sexo_m_and_under_45_years_old()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => now()->subYears(35)->format('Y-m-d'),
            'ra_reservista' => '55522211174',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('ra_reservista');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_ra_reservista_more_than_15_chars_if_sexo_m_and_under_45_years_old()
    {
        $externo = $this->signInAsUserExterno();

        $dados = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => now()->subYears(35)->format('Y-m-d'),
            'ra_reservista' => '5552221117488874',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('ra_reservista');
    }

    /** @test */
    public function log_is_generated_when_form_pf_is_submitted()
    {
        $externo = $this->signInAsUserExterno();
        
        $dados = factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $pr = PreRegistro::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário Externo com cpf: ' . $pr->userExterno->cpf_cnpj;
        $txt .= ', atualizou o status para ' . $pr::STATUS_ANALISE_INICIAL . ' da solicitação de registro com a id: ' . $pr->id;
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_submit_pre_registro_with_status_different_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CRIADO, PreRegistro::STATUS_CORRECAO]))
                in_array($status, [PreRegistro::STATUS_APROVADO, PreRegistro::STATUS_NEGADO]) ? 
                $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertNotFound() : 
                $this->put(route('externo.inserir.preregistro'))->assertUnauthorized();
        }
    }

    /** @test */
    public function can_submit_pre_registro_with_status_aguardando_correcao_or_sendo_elaborado()
    {
        Mail::fake();
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistroCpf')->create()->preRegistro;

        $s = [PreRegistro::STATUS_CRIADO => PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_CORRECAO => PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach([PreRegistro::STATUS_CRIADO, PreRegistro::STATUS_CORRECAO] as $status)
        {
            $preRegistro->update(['status' => $status]);
            if($status == PreRegistro::STATUS_CORRECAO)
                $preRegistro->pessoaFisica->update(['nome_pai' => mb_strtoupper($this->faker()->text(50), 'UTF-8')]);
            $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertViewIs('site.userExterno.inserir-pre-registro');
            $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));
            Mail::assertQueued(PreRegistroMail::class);
            $this->assertEquals(PreRegistro::first()->status, $s[$status]);
        }
    }

    /** @test */
    public function log_is_generated_when_status_aguardando_correcao()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário Externo com cpf: ' . $preRegistro->userExterno->cpf_cnpj;
        $txt .= ', atualizou o status para ' . PreRegistro::STATUS_ANALISE_CORRECAO . ' da solicitação de registro com a id: ' . $preRegistro->id;
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function filled_campos_espelho_when_form_pf_is_submitted()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistroCpf')->create()->preRegistro;
           
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $t = json_decode($preRegistro->fresh()->campos_espelho, true);
        $t2 = array_merge($preRegistro->arrayValidacaoInputs(), $preRegistro->contabil->arrayValidacaoInputs(), $preRegistro->pessoaFisica->arrayValidacaoInputs(), 
        ['path' => $preRegistro->anexos->count(), "opcional_celular" => $preRegistro->opcional_celular, "opcional_celular_1" => '']);

        $this->assertEquals($t, $t2);
    }

    /** @test */
    public function filled_campos_editados_pre_registros_cpf_when_form_is_submitted_when_status_aguardando_correcao()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
      
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'justificar',
            'campo' => 'nome_social',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->signInAsUserExterno('user_externo', $externo);

        $campos = [
            'nome_social' => null,
            'sexo' => 'F',
            'naturalidade_cidade' => "CIDADE NOVA",
            'naturalidade_estado' => "RJ",
            'nome_mae' => "NOVA MÃE",
            'nome_pai' => "NOVO PAI",
            'identidade' => "00000123678",
            'orgao_emissor' => "PPP",
            'titulo_eleitor' => "098765432109",
            'zona' => "445",
            'secao' => "8765",
            'ra_reservista' => null,
        ];

        foreach($campos as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));
        
        $arrayFinal = array_diff(array_keys(PreRegistro::first()->getCamposEditados()), array_keys($campos));
        $this->assertEquals($arrayFinal, array());
        $arrayFinal = array_diff(array_keys($campos), array_keys(PreRegistro::first()->getCamposEditados()));
        $this->assertEquals($arrayFinal, array());
    }

    /** @test */
    public function view_justifications_pf()
    {
        $externo = $this->signInAsUserExterno();

        factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->signInAsUserExterno('user_externo', $externo);

        foreach($keys as $campo)
            $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
            ->assertSeeInOrder([
                '<a class="nav-link" data-toggle="pill" href="#parte_dados_gerais">',
                'Dados Gerais&nbsp',
                '<span class="badge badge-danger">',
                '</a>',
            ])
            ->assertSee('value="'. route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo]) .'"');
    }

    /** @test */
    public function view_justifications_text_pf()
    {
        $externo = $this->signInAsUserExterno();

        factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        foreach($keys as $campo)
            $this->get(route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo]))
            ->assertJsonFragment(['justificativa' => PreRegistro::first()->getJustificativaPorCampo($campo)]);
    }

    /** 
     * ===================================================================================================================
     * TESTES PRE-REGISTRO-CPF VIA AJAX - LOGIN CONTABILIDADE RESPONSÁVEL PELO GERENCIAMENTO PARA O USUARIO EXTERNO COMUM
     * ===================================================================================================================
     */

    /** @test */
    public function can_new_pre_registro_pf_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $this->get(route('externo.preregistro.view'))->assertOk();
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistro = $externo->preRegistros->first();

        $this->assertDatabaseHas('pre_registros', [
            'id' => $preRegistro->id,
        ]);

        $this->assertDatabaseHas('pre_registros_cpf', [
            'id' => $preRegistro->pessoaFisica->id,
        ]);
    }

    /** @test */
    public function log_is_generated_when_form_cpf_is_created_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $pr = PreRegistro::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Contabilidade com cnpj '.$externo->cnpj.', criou a solicitação de registro com a id: 1 junto com a conta do Usuário Externo com o cpf '.$pr->userExterno->cpf_cnpj;
        $txt .= ' que foi notificado pelo e-mail ' . $pr->userExterno->email;
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function view_msg_update_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        
        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));

        PreRegistro::first()->update(['updated_at' => PreRegistro::first()->updated_at->subHour()]);
        $atual = PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s');

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaFisica',
            'campo' => 'sexo',
            'valor' => 'O'
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
        $this->assertNotEquals($atual, PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
    }

    /** @test */
    public function can_create_new_register_pre_registros_cpf_after_negado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create(),
        ]);

        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        
        $this->assertDatabaseHas('pre_registros_cpf', [
            'pre_registro_id' => 2
        ]);
    }

    /** @test */
    public function cannot_create_new_register_pre_registros_cpf_after_aprovado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create(),
        ]);

        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make([
            'cpf_cnpj' => PreRegistro::first()->userExterno->cpf_cnpj
        ])->toArray();

        $this->post(route('externo.contabil.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));        
        
        $this->assertDatabaseMissing('pre_registros_cpf', [
            'pre_registro_id' => 2
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_cpf_by_ajax_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => $externo->preRegistros->first()->id
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf->toArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cpf_by_ajax_with_upperCase_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('low')->make([
            'pre_registro_id' => $externo->preRegistros->first()->id
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            if(isset($value))
                $preRegistroCpf[$key] = mb_strtoupper($value, 'UTF-8');

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf->toArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cpf_by_ajax_when_exists_others_pre_registros_by_contabilidade()
    {
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1970-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ]);

        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1975-10-15',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => $preRegistroCpf_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ]);

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create());
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => $externo->preRegistros->first()->id
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => $externo->preRegistros->first()->id]), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf->toArray());
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2->attributesToArray());
    }

    /** @test */
    public function can_update_table_pre_registros_cpf_by_ajax_when_exists_others_pre_registros_with_same_user_and_negado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create([
                'contabil_id' => null,
            ])
        ]);

        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create([
                'contabil_id' => null,
            ])
        ]);

        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => factory('App\PreRegistro')->make([
                'user_externo_id' => $preRegistroCpf_1->preRegistro->userExterno->id,
            ]),
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 3]), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf->toArray());
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_when_exists_others_pre_registros_with_same_user_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create([
                'contabil_id' => null,
            ])
        ]);

        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create([
                'contabil_id' => null,
                'user_externo_id' => $preRegistroCpf_1->preRegistro->userExterno->id,
            ])
        ]);

        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make([
            'cpf_cnpj' => $preRegistroCpf_1->preRegistro->userExterno->cpf_cnpj
        ])->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => factory('App\PreRegistro')->make([
                'user_externo_id' => 1,
            ]),
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 3]), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(500);

        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf->toArray());
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2->attributesToArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_wrong_input_name_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => 1
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaFisica',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_without_classe_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => 1
        ])->makeHidden(['pre_registro_id']);

        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => '',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_wrong_classe_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => 1
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridicaErro',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_without_campo_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => 1
        ])->makeHidden(['pre_registro_id']);
        
        foreach($preRegistroCpf->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaFisica',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf->toArray());
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_with_input_type_text_more_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCpf = [
            'nome_social' => $this->faker()->text(500),
            'naturalidade_cidade' => $this->faker()->text(500),
            'nome_mae' => $this->faker()->text(500),
            'nome_pai' => $this->faker()->text(500),
            'identidade' => $this->faker()->text(500),
            'orgao_emissor' => $this->faker()->text(500),
            'titulo_eleitor' => $this->faker()->text(500),
            'zona' => $this->faker()->text(500),
            'secao' => $this->faker()->text(500),
            'ra_reservista' => $this->faker()->text(500),
        ];
        
        foreach($preRegistroCpf as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('valor');
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_under_18_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaFisica',
            'campo' => 'dt_nascimento',
            'valor' => Carbon::today()->subYears(17)->format('Y-m-d')
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', [
            'dt_nascimento' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_with_sexo_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaFisica',
            'campo' => 'sexo',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', [
            'sexo' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_with_estado_civil_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaFisica',
            'campo' => 'estado_civil',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', [
            'estado_civil' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_with_nacionalidade_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaFisica',
            'campo' => 'nacionalidade',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', [
            'nacionalidade' => 'BRASILEIRA'
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_with_naturalidade_estado_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaFisica',
            'campo' => 'naturalidade_estado',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', [
            'naturalidade_estado' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_with_tipo_identidade_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaFisica',
            'campo' => 'tipo_identidade',
            'valor' => 'teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', [
            'tipo_identidade' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_with_dt_expedicao_after_today_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaFisica',
            'campo' => 'dt_expedicao',
            'valor' => Carbon::today()->addDay()->format('Y-m-d')
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', [
            'dt_expedicao' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cpf_by_ajax_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $datas = [
            'dt_nascimento' => null, 
            'dt_expedicao' => null
        ];

        foreach($datas as $key => $value) 
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => 'texto'
            ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cpf', $datas);
    }

    /** @test */
    public function can_update_table_pre_registros_cpf_by_ajax_when_clean_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCpf = factory('App\PreRegistroCpf')->create();

        $preRegistroCpf = $preRegistroCpf->makeHidden(['id', 'pre_registro_id', 'updated_at', 'created_at'])->attributesToArray();

        foreach($preRegistroCpf as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => ''
            ])->assertStatus(200);
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf);
    }

    // Status do pré-registro

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCpf = factory('App\PreRegistroCpf')->create();

        $preRegistroCpf = $preRegistroCpf->makeHidden(['id', 'pre_registro_id', 'updated_at', 'created_at']);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                foreach($preRegistroCpf->attributesToArray() as $key => $value)
                    $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                        'classe' => 'pessoaFisica',
                        'campo' => $key,
                        'valor' => ''
                    ])->assertStatus(401);
        }
    }

    /** @test */
    public function can_update_table_pre_registros_cpf_by_ajax_with_status_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCpf = factory('App\PreRegistroCpf')->create();

        $preRegistroCpf = $preRegistroCpf->makeHidden(['id', 'pre_registro_id', 'updated_at', 'created_at']);

        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            foreach($preRegistroCpf->attributesToArray() as $key => $value)
                $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                    'classe' => 'pessoaFisica',
                    'campo' => $key,
                    'valor' => ''
                ])->assertStatus(200);
        }
    }

    /** 
     * =====================================================================================================================
     * TESTES PRE-REGISTRO-CPF VIA SUBMIT - LOGIN CONTABILIDADE RESPONSÁVEL PELO GERENCIAMENTO PARA O USUARIO EXTERNO COMUM
     * =====================================================================================================================
     */

    /** @test */
    public function can_submit_pre_registro_cpf_by_contabilidade()
    {
        Mail::fake();

        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCpf')->create();

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))->assertOk();
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])->assertOk();

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        Mail::assertQueued(PreRegistroMail::class);

        $this->assertDatabaseHas('pre_registros_cpf', $pr->attributesToArray());

        $this->assertDatabaseHas('anexos', [
            'pre_registro_id' => 1
        ]);

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registro_cpf_if_nacionalidade_different_option_brasileira_by_contabilidade()
    {        
        $externo = $this->signInAsUserExterno('contabil');

        $pr = factory('App\PreRegistroCpf')->create([
            'nacionalidade' => 'CHILENA',
            'naturalidade_cidade' => null,
            'naturalidade_estado' => null,
            'titulo_eleitor' => null,
            'zona' => null,
            'secao' => null,
            'ra_reservista' => null,
        ]);

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $this->assertDatabaseHas('pre_registros_cpf', $pr->attributesToArray());
    }

    /** @test */
    public function can_submit_pre_registros_cpf_when_exists_others_pre_registros_by_contabilidade()
    {
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1970-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1975-10-15',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ])->attributesToArray();

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create());

        $pr = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ])->attributesToArray();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 3]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 3]));

        $this->assertDatabaseHas('pre_registros_cpf', $pr);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2);

        $this->assertEquals(PreRegistro::find(3)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registros_cpf_when_exists_others_pre_registros_with_same_user_and_negado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();

        $pr = factory('App\PreRegistroCpf')->create()->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 3]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 3]));

        $this->assertDatabaseHas('pre_registros_cpf', $pr);

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2);
        $this->assertDatabaseHas('pre_registros', $externo->preRegistros->first()->fresh()->toArray());

        $this->assertEquals(PreRegistro::find(3)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function cannot_submit_pre_registros_cpf_when_exists_others_pre_registros_with_same_user_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('negado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('aprovado')->create([
                'contabil_id' => null,
            ])
        ])->attributesToArray();

        $pr = factory('App\PreRegistroCpf')->raw();
        Anexo::find(3)->delete();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), ['pergunta' => "25 meses"])
        ->assertStatus(500);

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 3]))
        ->assertUnauthorized();

        $this->assertDatabaseMissing('pre_registros_cpf', $pr);

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2);
        $this->assertDatabaseMissing('pre_registros_cpf', ['id' => 3]);
    }

    /** @test */
    public function can_submit_pre_registros_cpf_when_exists_others_pre_registros_with_same_contabil_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1970-03-10'
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1975-10-15',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ])->attributesToArray();

        $pr = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ])->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 3]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 3]));

        $this->assertDatabaseHas('pre_registros_cpf', $pr);

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2);
        $this->assertDatabaseHas('pre_registros', PreRegistro::find(3)->toArray());

        $this->assertEquals(PreRegistro::find(3)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function can_submit_pre_registro_cpf_without_optional_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $prCpf = factory('App\PreRegistroCpf')->create([
            'sexo' => 'F',
            'nome_social' => null,
            'estado_civil' => null,
            'nome_pai' => null,
            'ra_reservista' => null,
        ])->attributesToArray();       

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $this->assertDatabaseHas('pre_registros_cpf', $prCpf);
    }

    /** @test */
    public function cannot_submit_pre_registro_cpf_without_required_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $prCpf = factory('App\PreRegistroCpf')->create([
            'sexo' => null,
            'dt_nascimento' => null,
            'nacionalidade' => null,
            'nome_mae' => null,
            'tipo_identidade' => null,
            'identidade' => null,
            'orgao_emissor' => null,
            'dt_expedicao' => null,
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors([
            'sexo','dt_nascimento', 'nacionalidade','nome_mae','tipo_identidade','identidade','orgao_emissor','dt_expedicao',
        ]);

        $this->assertDatabaseHas('pre_registros_cpf', $prCpf->attributesToArray());
        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_CRIADO);
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'nome_social' => 'Nome'
        ]);
                
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'nome_social' => $this->faker()->text(500)
        ]);  
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'nome_social' => 'Nome Socia1'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_sexo_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'sexo' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('sexo');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_sexo_value_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'sexo' => 'B'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('sexo');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_dt_nascimento_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_incorrect_format_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '2000/12/21'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => 'texto'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_under_18_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => Carbon::today()->subYears(17)->format('Y-m-d')
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_estado_civil_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'estado_civil' => 'Qualquer um'
        ]);
       
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('estado_civil');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_nacionalidade_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'nacionalidade' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nacionalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nacionalidade_with_value_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'nacionalidade' => 'Qualquer'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nacionalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_naturalidade_cidade_when_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'naturalidade_cidade' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('naturalidade_cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_naturalidade_cidade_less_than_4_chars_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'naturalidade_cidade' => 'Qua'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('naturalidade_cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_naturalidade_cidade_more_than_191_chars_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'naturalidade_cidade' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('naturalidade_cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_naturalidade_estado_when_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'naturalidade_estado' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('naturalidade_estado');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_naturalidade_estado_with_value_wrong_when_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'naturalidade_estado' => 'UF'
        ]);
            
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('naturalidade_estado');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_nome_mae_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'nome_mae' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'nome_mae' => 'Nome'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'nome_mae' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'nome_mae' => 'N0me Mãe'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_pai_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'nome_pai' => 'Nome'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_pai_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'nome_pai' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_pai_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'nome_pai' => 'Nom3 pai'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_tipo_identidade_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'tipo_identidade' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('tipo_identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_tipo_identidade_with_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'tipo_identidade' => 'Teste'
        ]);
    
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('tipo_identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_identidade_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'identidade' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_identidade_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'identidade' => '123'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_identidade_more_than_30_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'identidade' => '1234567890123456789012345678901'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_orgao_emissor_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'orgao_emissor' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_orgao_emissor_less_than_3_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'orgao_emissor' => 'SS'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_orgao_emissor_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'orgao_emissor' => $this->faker()->text(500)
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_dt_expedicao_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'dt_expedicao' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_expedicao_incorrect_format_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'dt_expedicao' => '2000/12/21'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_expedicao_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'dt_expedicao' => 'text'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_expedicao_after_today_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'dt_expedicao' => Carbon::today()->addDay()->format('Y-m-d')
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_titulo_eleitor_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'titulo_eleitor' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('titulo_eleitor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_titulo_eleitor_less_than_12_chars_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'titulo_eleitor' => '23569874521'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('titulo_eleitor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_titulo_eleitor_more_than_15_chars_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'titulo_eleitor' => '2356987452123658'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('titulo_eleitor');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_zona_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'zona' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('zona');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_zona_more_than_6_chars_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'zona' => '7536985'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('zona');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_secao_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'secao' => ''
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('secao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_secao_more_than_8_chars_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'secao' => '753698575'
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('secao');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_ra_reservista_if_sexo_m_and_under_45_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => now()->subYears(35)->format('Y-m-d'),
            'ra_reservista' => '',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('ra_reservista');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_ra_reservista_less_than_12_chars_if_sexo_m_and_under_45_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => now()->subYears(35)->format('Y-m-d'),
            'ra_reservista' => '55522211174',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('ra_reservista');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_ra_reservista_more_than_15_chars_if_sexo_m_and_under_45_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => now()->subYears(35)->format('Y-m-d'),
            'ra_reservista' => '5552221117488874',
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('ra_reservista');
    }

    /** @test */
    public function log_is_generated_when_form_pf_is_submitted_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $dados = factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $pr = PreRegistro::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Contabilidade com cnpj '.$externo->cnpj.' realizou a operação para o Usuário Externo com cpf: ' . $pr->userExterno->cpf_cnpj;
        $txt .= ', atualizou o status para ' . $pr::STATUS_ANALISE_INICIAL . ' da solicitação de registro com a id: ' . $pr->id;
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_submit_pre_registro_with_status_different_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistro = factory('App\PreRegistroCpf')->create()->preRegistro;
        
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CRIADO, PreRegistro::STATUS_CORRECAO]))
                in_array($status, [PreRegistro::STATUS_APROVADO, PreRegistro::STATUS_NEGADO]) ? 
                $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])->assertNotFound() : 
                $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))->assertUnauthorized();
        }
    }

    /** @test */
    public function can_submit_pre_registro_with_status_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        Mail::fake();
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistro = factory('App\PreRegistroCpf')->create()->preRegistro;

        $s = [PreRegistro::STATUS_CRIADO => PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_CORRECAO => PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach([PreRegistro::STATUS_CRIADO, PreRegistro::STATUS_CORRECAO] as $status)
        {
            $preRegistro->update(['status' => $status]);
            if($status == PreRegistro::STATUS_CORRECAO)
                $preRegistro->pessoaFisica->update(['nome_mae' => mb_strtoupper($this->faker()->text(50), 'UTF-8')]);

            $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
            ->assertViewIs('site.userExterno.inserir-pre-registro');

            $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
            ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));
            Mail::assertQueued(PreRegistroMail::class);
            $this->assertEquals(PreRegistro::first()->status, $s[$status]);
        }
    }

    /** @test */
    public function log_is_generated_when_status_aguardando_correcao_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistro = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()
        ])->preRegistro;

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Contabilidade com cnpj '.$externo->cnpj.' realizou a operação para o Usuário Externo com cpf: ' . $preRegistro->userExterno->cpf_cnpj;
        $txt .= ', atualizou o status para ' . PreRegistro::STATUS_ANALISE_CORRECAO . ' da solicitação de registro com a id: ' . $preRegistro->id;
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function filled_campos_espelho_when_form_pf_is_submitted_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $preRegistro = factory('App\PreRegistroCpf')->create()->preRegistro;
           
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $t = json_decode($preRegistro->fresh()->campos_espelho, true);
        $t2 = array_merge($preRegistro->arrayValidacaoInputs(), $preRegistro->contabil->arrayValidacaoInputs(), $preRegistro->pessoaFisica->arrayValidacaoInputs(), 
        ['path' => $preRegistro->anexos->count(), "opcional_celular" => $preRegistro->opcional_celular, "opcional_celular_1" => '']);

        $this->assertEquals($t, $t2);
    }

    /** @test */
    public function filled_campos_editados_pre_registros_cpf_when_form_is_submitted_when_status_aguardando_correcao_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
      
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $admin = $this->signIn(PreRegistro::first()->user);

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'justificar',
            'campo' => 'nome_social',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->signInAsUserExterno('contabil', $externo);

        $campos = [
            'nome_social' => null,
            'sexo' => 'F',
            'naturalidade_cidade' => "CIDADE NOVA",
            'naturalidade_estado' => "RJ",
            'nome_mae' => "NOVA MÃE",
            'nome_pai' => "NOVO PAI",
            'identidade' => "00000123678",
            'orgao_emissor' => "PPP",
            'titulo_eleitor' => "098765432109",
            'zona' => "445",
            'secao' => "8765",
            'ra_reservista' => null,
        ];

        foreach($campos as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');
        
        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));
        
        $arrayFinal = array_diff(array_keys(PreRegistro::first()->getCamposEditados()), array_keys($campos));
        $this->assertEquals($arrayFinal, array());
        $arrayFinal = array_diff(array_keys($campos), array_keys(PreRegistro::first()->getCamposEditados()));
        $this->assertEquals($arrayFinal, array());
    }

    /** @test */
    public function view_justifications_pf_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->signInAsUserExterno('contabil', $externo);

        foreach($keys as $campo)
            $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
            ->assertSeeInOrder([
                '<a class="nav-link" data-toggle="pill" href="#parte_dados_gerais">',
                'Dados Gerais&nbsp',
                '<span class="badge badge-danger">',
                '</a>',
            ])
            ->assertSee('value="'. route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo]) .'"');
    }

    /** @test */
    public function view_justifications_text_pf_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        foreach($keys as $campo)
            $this->get(route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo]))
            ->assertJsonFragment(['justificativa' => PreRegistro::first()->getJustificativaPorCampo($campo)]);
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CPF VIA AJAX - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function can_update_justificativa()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());

        $justificativas = array();
        foreach($dados as $campo)
        {
            $texto = $this->faker()->text(500);
            $justificativas[$campo] = $texto;
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $texto
            ])->assertStatus(200);   
            
            $this->assertEquals(PreRegistro::first()->getJustificativaArray(), $justificativas);
            $this->assertEquals(PreRegistro::first()->idusuario, $admin->idusuario);
        }

        $this->assertDatabaseHas('pre_registros', [
            'justificativa' => json_encode($justificativas, JSON_FORCE_OBJECT),
        ]);
    }

    /** @test */
    public function can_update_justificativa_with_status_em_analise_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            if(in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                foreach($dados as $campo)
                    $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                        'acao' => 'justificar',
                        'campo' => $campo,
                        'valor' => $this->faker()->text(500)
                    ])->assertStatus(200);    
        }
    }

    /** @test */
    public function can_edit_justificativas()
    {
        $admin = $this->signInAsAdmin();
        
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);   

        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => ''
            ])->assertStatus(200);    

        $this->assertDatabaseHas('pre_registros', [
            'justificativa' => null,
            'idusuario' => $admin->idusuario
        ]);
    }

    /** @test */
    public function cannot_update_justificativa_more_than_500_chars()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);   

        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
        {
            $texto = $this->faker()->text(900);
            $justificativas[$campo] = $texto;
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $texto
            ])->assertSessionHasErrors('valor');   
        }
            
        $this->assertDatabaseMissing('pre_registros', [
            'justificativa' => json_encode($justificativas, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function cannot_update_justificativa_with_wrong_inputs()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);   

        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo . '_erro',
                'valor' => $this->faker()->text(500)
            ])->assertSessionHasErrors('campo');   
    }

    /** @test */
    public function cannot_update_justificativa_with_wrong_input_acao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);   

        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                'acao' => 'justificar_',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertSessionHasErrors('acao');   
    }

    /** @test */
    public function cannot_update_justificativa_with_status_different_em_analise_or_analise_da_correcao()
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);

        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                foreach($dados as $campo)
                    $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                        'acao' => 'justificar',
                        'campo' => $campo,
                        'valor' => $this->faker()->text(500)
                    ])->assertStatus(401);    
        }
    }

    /** @test */
    public function log_is_generated_when_update_justificativa()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());

        foreach($dados as $campo)
        {
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);

            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
            $txt = $inicio . 'Usuário (usuário 1) fez a ação de "justificar" o campo "' . $campo . '", ';
            $txt .= 'inserindo ou removendo valor *pré-registro* (id: '.$preRegistroCpf->preRegistro->id.')';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function can_remove_all_justificativas()
    {
        $admin = $this->signInAsAdmin();
        
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $dados = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);   

        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'exclusao_massa',
            'campo' => 'exclusao_massa',
            'valor' => $dados
        ])->assertStatus(200);    

        $this->assertDatabaseHas('pre_registros', [
            'justificativa' => null,
            'idusuario' => $admin->idusuario
        ]);
    }

    /** @test */
    public function can_check_anexos()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);
        $tipos = Anexo::first()->getObrigatoriosPreRegistro();

        $arrayAnexos = array();
        foreach($tipos as $tipo)
        {
            $arrayAnexos[$tipo] = "OK";
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'conferir',
                'campo' => 'confere_anexos[]',
                'valor' => $tipo
            ])->assertStatus(200);

            $this->assertEquals(PreRegistro::first()->getConfereAnexosArray(), $arrayAnexos);
            $this->assertEquals(PreRegistro::first()->idusuario, $admin->idusuario);
        }
            
        $this->assertDatabaseHas('pre_registros', [
            'confere_anexos' => json_encode($arrayAnexos, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function log_is_generated_when_check_anexos()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);
        $tipos = Anexo::first()->getObrigatoriosPreRegistro();

        $arrayAnexos = array();
        foreach($tipos as $tipo)
        {
            $arrayAnexos[$tipo] = "OK";
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'conferir',
                'campo' => 'confere_anexos[]',
                'valor' => $tipo
            ])->assertStatus(200);

            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
            $txt = $inicio . 'Usuário (usuário 1) fez a ação de "conferir" o campo "confere_anexos", ';
            $txt .= 'inserindo ou removendo valor *pré-registro* (id: '.$preRegistroCpf->preRegistro->id.')';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function cannot_check_reservista_if_sexo_not_M()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);
        $tipo = 'Cerificado de reservista ou dispensa';
        $arrayAnexos['Cerificado de reservista ou dispensa'] = "OK";

        foreach(generos() as $key => $valor)
        {
            $preRegistroCpf->update(['sexo' => $key]);
            if($key != 'M')
                $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                    'acao' => 'conferir',
                    'campo' => 'confere_anexos[]',
                    'valor' => $tipo
                ])->assertSessionHasErrors('valor'); 
        }

        $this->assertDatabaseMissing('pre_registros', [
            'confere_anexos' => json_encode($arrayAnexos, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function cannot_check_reservista_if_more_than_45_years_old()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);
        $tipo = 'Cerificado de reservista ou dispensa';
        $arrayAnexos['Cerificado de reservista ou dispensa'] = "OK";

        $preRegistroCpf->update(['dt_nascimento' => Carbon::today()->subYears(45)->subDay()->format('Y-m-d')]);
        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => $tipo
        ])->assertSessionHasErrors('valor'); 

        $this->assertDatabaseMissing('pre_registros', [
            'confere_anexos' => json_encode($arrayAnexos, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function cannot_check_anexos_with_wrong_action()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);
        $tipos = Anexo::first()->getObrigatoriosPreRegistro();

        $arrayAnexos = array();
        foreach($tipos as $tipo)
        {
            $arrayAnexos[$tipo] = "OK";
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'conferir_',
                'campo' => 'confere_anexos[]',
                'valor' => $tipo
            ])->assertSessionHasErrors('acao'); 
        }
    }

    /** @test */
    public function cannot_check_anexos_with_wrong_value()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);
        $tipos = Anexo::first()->getObrigatoriosPreRegistro();

        $arrayAnexos = array();
        foreach($tipos as $tipo)
        {
            $arrayAnexos[$tipo] = "OK";
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'conferir',
                'campo' => 'confere_anexos[]',
                'valor' => $tipo . '-'
            ])->assertSessionHasErrors('valor'); 
        }
    }

    /** @test */
    public function cannot_check_anexos_with_wrong_field()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);
        $tipos = Anexo::first()->getObrigatoriosPreRegistro();

        $arrayAnexos = array();
        foreach($tipos as $tipo)
        {
            $arrayAnexos[$tipo] = "OK";
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'conferir',
                'campo' => 'confere_anexos[]_',
                'valor' => $tipo
            ])->assertSessionHasErrors('campo'); 
        }
    }

    /** @test */
    public function cannot_check_anexos_without_anexo()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);
        Anexo::first()->delete();

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => 'CPF'
        ])->assertSessionHasErrors('valor'); 
    }

    /** @test */
    public function cannot_check_anexos_without_pre_registro()
    {
        $admin = $this->signInAsAdmin();

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'conferir',
            'campo' => 'confere_anexos[]',
            'valor' => 'CPF'
        ])->assertNotFound();
    }

    /** @test */
    public function cannot_check_anexos_pre_registro_with_status_different_analise_inicial_or_analise_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                    'acao' => 'conferir',
                    'campo' => 'confere_anexos[]',
                    'valor' => 'CPF'
                ])->assertSessionHasErrors('valor');
        }
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CPF VIA SUBMIT - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function can_update_status_enviar_para_correcao()
    {
        Mail::fake();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
            'acao' => 'justificar',
            'campo' => 'dt_nascimento',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'corrigir'])
        ->assertRedirect(route('preregistro.index'));

        Mail::assertQueued(PreRegistroMail::class);

        $this->get(route('preregistro.index'))
        ->assertSeeText('Pré-registro com a ID: '.$preRegistroCpf->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_CORRECAO.'" com sucesso');

        $this->assertDatabaseHas('pre_registros', [
            'status' => PreRegistro::STATUS_CORRECAO,
            'idusuario' => $admin->idusuario,
            'historico_justificativas' => $preRegistroCpf->fresh()->preRegistro->historico_justificativas
        ]);
    }

    /** @test */
    public function can_update_status_enviar_para_correcao_without_confere_anexos()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
            'acao' => 'justificar',
            'campo' => 'dt_nascimento',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'corrigir'])
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('Pré-registro com a ID: '.$preRegistroCpf->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_CORRECAO.'" com sucesso');

        $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_CORRECAO);
    }

    /** @test */
    public function cannot_update_status_enviar_para_correcao_without_justificativa()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'corrigir'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCpf->pre_registro_id))
        ->assertSeeText('Não possui justificativa(s)');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_CORRECAO);
    }

    /** @test */
    public function cannot_update_status_enviar_para_correcao_only_key_negado()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);
        $preRegistroCpf->preRegistro->update(['justificativa' => json_encode(['negado' => 'teste negação'])]);

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'corrigir'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCpf->pre_registro_id))
        ->assertSeeText('Existe justificativa de negação, informe CTI');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_CORRECAO);
    }

    /** @test */
    public function cannot_update_status_enviar_para_correcao_with_status_different_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
            'acao' => 'justificar',
            'campo' => 'dt_nascimento',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200);

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO, PreRegistro::STATUS_CORRECAO];
        foreach(PreRegistro::getStatus() as $status)
            if(!in_array($status, $canUpdate))
            {
                $preRegistroCpf->preRegistro->update(['status' => $status]);
                $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'corrigir'])
                ->assertSessionHasErrors('status');

                $this->get(route('preregistro.view', $preRegistroCpf->pre_registro_id))
                ->assertSeeText('Não possui o status necessário para ser enviado para correção');

                $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_CORRECAO);
            }
    }

    /** @test */
    public function can_update_status_enviar_para_correcao_with_status_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
            'acao' => 'justificar',
            'campo' => 'dt_nascimento',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200);

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach($canUpdate as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'corrigir'])
            ->assertRedirect(route('preregistro.index'));

            $this->get(route('preregistro.index'))
            ->assertSeeText('Pré-registro com a ID: '.$preRegistroCpf->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_CORRECAO.'" com sucesso');

            $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_CORRECAO);
        }
    }

    /** @test */
    public function log_is_generated_when_update_status_enviar_para_correcao_with_status_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
            'acao' => 'justificar',
            'campo' => 'dt_nascimento',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200);
        
        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach($canUpdate as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'corrigir'])
            ->assertRedirect(route('preregistro.index'));

            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
            $txt = $inicio . 'Usuário (usuário 1) atualizou status para ' . PreRegistro::STATUS_CORRECAO . ' *pré-registro* (id: '.$preRegistroCpf->preRegistro->id.')';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function can_update_status_negado()
    {
        Mail::fake();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);
        $anexo = Anexo::first();

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200); 

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'negar'])
        ->assertRedirect(route('preregistro.index'));

        Mail::assertQueued(PreRegistroMail::class);

        $this->get(route('preregistro.index'))
        ->assertSeeText('Pré-registro com a ID: '.$preRegistroCpf->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_NEGADO.'" com sucesso');

        $this->assertDatabaseHas('pre_registros', [
            'status' => PreRegistro::STATUS_NEGADO,
            'idusuario' => $admin->idusuario
        ]);

        $this->assertDatabaseMissing('anexos', [
            'path' => $anexo->path,
            'pre_registro_id' => $anexo->pre_registro_id
        ]);
    }

    /** @test */
    public function log_is_generated_when_update_status_negado()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200); 

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'negar'])
        ->assertRedirect(route('preregistro.index'));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário (usuário 1) atualizou status para ' . PreRegistro::STATUS_NEGADO;
        $txt .= ' e seus arquivos foram excluídos pelo sistema *pré-registro* (id: '.$preRegistroCpf->preRegistro->id.')';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function can_update_status_negado_without_confere_anexos()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200); 

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'negar'])
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('Pré-registro com a ID: '.$preRegistroCpf->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_NEGADO.'" com sucesso');

        $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_NEGADO);
    }

    /** @test */
    public function cannot_update_status_negado_without_justificativa_negado()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'negar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCpf->pre_registro_id))
        ->assertSeeText('Não possui justificativa(s)');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_NEGADO);
    }

    /** @test */
    public function cannot_update_status_negado_with_others_justificativa_and_without_negado()
    {
        $admin = $this->signInAsAdmin();
        
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'sexo',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200); 

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'negar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCpf->pre_registro_id))
        ->assertSeeText('Não possui justificativa(s)');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_NEGADO);
    }

    /** @test */
    public function can_update_status_negado_with_others_justificativa_and_negado()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);

        foreach(['sexo', 'negado'] as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'negar'])
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('Pré-registro com a ID: '.$preRegistroCpf->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_NEGADO.'" com sucesso');

        $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_NEGADO);
    }

    /** @test */
    public function cannot_update_status_negado_with_status_different_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200); 

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO, PreRegistro::STATUS_NEGADO];
        foreach(PreRegistro::getStatus() as $status)
            if(!in_array($status, $canUpdate))
            {
                $preRegistroCpf->preRegistro->update(['status' => $status]);
                $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'negar'])
                ->assertSessionHasErrors('status');

                $this->get(route('preregistro.view', $preRegistroCpf->pre_registro_id))
                ->assertSeeText('Não possui o status necessário para ser negado');

                $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_NEGADO);
            }
    }

    /** @test */
    public function can_update_status_negado_with_status_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $this->faker()->text(500)
        ])->assertStatus(200); 

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach($canUpdate as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'negar'])
            ->assertRedirect(route('preregistro.index'));
            
            $this->get(route('preregistro.index'))
            ->assertSeeText('Pré-registro com a ID: '.$preRegistroCpf->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_NEGADO.'" com sucesso');

            $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_NEGADO);
        }
    }

    /** @test */
    public function can_update_status_aprovado()
    {
        Mail::fake();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'aprovar'])
        ->assertRedirect(route('preregistro.index'));

        Mail::assertQueued(PreRegistroMail::class);

        $this->get(route('preregistro.index'))
        ->assertSeeText('Pré-registro com a ID: '.$preRegistroCpf->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_APROVADO.'" com sucesso');

        $this->assertDatabaseHas('pre_registros', [
            'status' => PreRegistro::STATUS_APROVADO,
            'idusuario' => $admin->idusuario
        ]);
    }

    /** @test */
    public function log_is_generated_when_update_status_aprovado()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'aprovar'])
        ->assertRedirect(route('preregistro.index'));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $txt = $inicio . 'Usuário (usuário 1) atualizou status para ' . PreRegistro::STATUS_APROVADO;
        $txt .= ' *pré-registro* (id: '.$preRegistroCpf->preRegistro->id.')';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function cannot_update_status_aprovado_without_confere_anexos()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $this->put(route('preregistro.update.status', $preRegistroCpf->preRegistro->id), ['situacao' => 'aprovar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeText('Faltou confirmar a entrega dos anexos');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
    }

    /** @test */
    public function cannot_update_status_aprovado_with_justificativa()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'nome_social',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200); 

        $this->put(route('preregistro.update.status', $preRegistroCpf->preRegistro->id), ['situacao' => 'aprovar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeText('Possui justificativa(s)');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
    }

    /** @test */
    public function cannot_update_status_aprovado_with_status_different_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO, PreRegistro::STATUS_APROVADO];
        foreach(PreRegistro::getStatus() as $status)
            if(!in_array($status, $canUpdate))
            {
                $preRegistroCpf->preRegistro->update(['status' => $status]);
                $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'aprovar'])
                ->assertSessionHasErrors('status');

                $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
                ->assertSeeText('Não possui o status necessário para ser aprovado');

                $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
            }
    }

    /** @test */
    public function can_update_status_aprovado_with_status_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach($canUpdate as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'aprovar'])
            ->assertRedirect(route('preregistro.index'));

            $this->get(route('preregistro.index'))
            ->assertSeeText('Pré-registro com a ID: '.$preRegistroCpf->pre_registro_id.' foi atualizado para "'.PreRegistro::STATUS_APROVADO.'" com sucesso');

            $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_APROVADO);
        }
    }

    /** @test */
    public function cannot_update_status_with_input_situacao_invalid()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'aprova'])
        ->assertSessionHasErrors('situacao');

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeText('Valor do status requisitado inválido');

        $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function cannot_update_status_without_input_situacao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('anexos_ok_pf', 'analise_inicial')->create()
        ]);

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => null])
        ->assertSessionHasErrors('situacao');

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeText('Obrigatório o status requisitado');

        $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CPF - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function view_pre_registro_cpf()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);
        
        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeInOrder([
            '<p id="tipo_cpf">', '<span class="font-weight-bolder">CPF: </span>', formataCpfCnpj($preRegistroCpf->preRegistro->userExterno->cpf_cnpj),
            '<p>', '<span class="font-weight-bolder">Nome Completo: </span>', $preRegistroCpf->preRegistro->userExterno->nome,
            '<p id="nome_social">', ' - Nome Social: </span>', $preRegistroCpf->nome_social,
            '<p id="sexo">', ' - Gênero: </span>', $preRegistroCpf->sexo,
            '<p id="dt_nascimento">', ' - Data de Nascimento: </span>', onlyDate($preRegistroCpf->dt_nascimento),
            '<p id="estado_civil">', ' - Estado Civil: </span>', $preRegistroCpf->estado_civil,
            '<p id="nacionalidade">', ' - Nacionalidade: </span>', $preRegistroCpf->nacionalidade,
            '<p id="naturalidade_cidade">', ' - Naturalidade - Cidade: </span>', $preRegistroCpf->naturalidade_cidade,
            '<p id="naturalidade_estado">', ' - Naturalidade - Estado: </span>', $preRegistroCpf->naturalidade_estado,
            '<p id="nome_mae">', ' - Nome da Mãe: </span>', $preRegistroCpf->nome_mae,
            '<p id="nome_pai">', ' - Nome do Pai: </span>', $preRegistroCpf->nome_pai,
            '<p id="tipo_identidade">', ' - Tipo do documento de identidade: </span>', $preRegistroCpf->tipo_identidade,
            '<p id="identidade">', ' - N° do documento de identidade: </span>', $preRegistroCpf->identidade,
            '<p id="orgao_emissor">', ' - Órgão Emissor: </span>', $preRegistroCpf->orgao_emissor,
            '<p id="dt_expedicao">', ' - Data de Expedição: </span>', onlyDate($preRegistroCpf->dt_expedicao),
            '<p id="titulo_eleitor">', ' - Título de Eleitor: </span>', $preRegistroCpf->titulo_eleitor,
            '<p id="zona">', ' - Zona Eleitoral: </span>', $preRegistroCpf->zona,
            '<p id="secao">', ' - Seção Eleitoral: </span>', $preRegistroCpf->secao,
            '<p id="ra_reservista">', ' - RA Reservista: </span>', $preRegistroCpf->ra_reservista,
        ]);
    }

    /** @test */
    public function view_text_justificado_cpf()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create()
        ]);

        $keys = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $justificativas = $preRegistroCpf->preRegistro->fresh()->getJustificativaArray();

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeText($justificativas['nome_social'])
        ->assertSeeText($justificativas['dt_nascimento'])
        ->assertSeeText($justificativas['sexo'])
        ->assertSeeText($justificativas['estado_civil'])
        ->assertSeeText($justificativas['nacionalidade'])
        ->assertSeeText($justificativas['naturalidade_cidade'])
        ->assertSeeText($justificativas['naturalidade_estado'])
        ->assertSeeText($justificativas['nome_mae'])
        ->assertSeeText($justificativas['nome_pai'])
        ->assertSeeText($justificativas['tipo_identidade'])
        ->assertSeeText($justificativas['identidade'])
        ->assertSeeText($justificativas['orgao_emissor'])
        ->assertSeeText($justificativas['dt_expedicao'])
        ->assertSeeText($justificativas['titulo_eleitor'])
        ->assertSeeText($justificativas['zona'])
        ->assertSeeText($justificativas['secao'])
        ->assertSeeText($justificativas['ra_reservista']);
    }

    /** @test */
    public function view_justifications_text_cpf_by_url()
    {
        $externo = $this->signInAsUserExterno();

        factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['checkPreRegistro' => 'on']), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);
        $data_hora = now()->format('Y-m-d H:i:s');

        foreach($keys as $campo)
            $this->get(route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo, 'data_hora' => urlencode($data_hora)]))
            ->assertJsonFragment([
                'justificativa' => PreRegistro::first()->getJustificativaPorCampoData($campo, $data_hora),
                'data_hora' => formataData($data_hora)
            ]);
    }

    /** @test */
    public function view_historico_justificativas_cpf()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['checkPreRegistro' => 'on']), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaFisica->arrayValidacaoInputs());
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);
        $data_hora = now()->format('Y-m-d H:i:s');

        foreach($keys as $campo)
            $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
            ->assertSee('value="'.route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo, 'data_hora' => urlencode($data_hora)]).'"');
    }

    /** @test */
    public function view_label_campo_alterado_pf()
    {
        $this->filled_campos_editados_pre_registros_cpf_when_form_is_submitted_when_status_aguardando_correcao();

        $admin = $this->signIn(PreRegistro::first()->user);

        $camposEditados = json_decode(PreRegistro::first()->campos_editados, true);

        $this->get(route('preregistro.view', 1))
        ->assertSeeInOrder([
            '<a class="card-link" data-toggle="collapse" href="#parte_dados_gerais">',
            '<div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">',
            '2. Dados Gerais',
            '<span class="badge badge-danger ml-2">Campos alterados</span>',
        ]);
            
        foreach($camposEditados as $key => $value)
            $this->get(route('preregistro.view', 1))->assertSeeInOrder([
                '<p id="'.$key.'">',
                '<span class="badge badge-danger ml-2">Campo alterado</span>',
                '</p>',
            ]);
    }

    /** @test */
    public function view_label_justificado_cpf()
    {
        $this->view_text_justificado_cpf();

        $admin = $this->signIn(PreRegistro::first()->user);

        $justificados = PreRegistro::first()->getJustificativaArray();
            
        foreach($justificados as $key => $value)
            $this->get(route('preregistro.view', 1))->assertSeeInOrder([
                '<p id="'.$key.'">',
                'type="button" ',
                'value="'.$key.'"',
                '<i class="fas fa-edit"></i>',
                '<span class="badge badge-warning just ml-2">Justificado</span>',
                '</p>',
            ]);
    }
}
