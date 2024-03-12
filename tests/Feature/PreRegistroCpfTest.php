<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\PreRegistro;
use App\UserExterno;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Anexo;

class PreRegistroCpfTest extends TestCase
{
    use RefreshDatabase;

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
        $pr = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => factory('App\PreRegistro')->raw([
                'user_externo_id' => 1
            ])
        ]);

        $this->post(route('externo.inserir.preregistro.ajax', [
            'classe' => 'preRegistro',
            'campo' => 'segmento',
            'valor' => 'Abrasivos'
            ]))->assertStatus(401);
        $this->assertEquals(PreRegistro::count(), 0);

        $this->put(route('externo.verifica.inserir.preregistro', $pr))->assertRedirect(route('externo.preregistro.view'));
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
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'status' => 'Negado'
            ]),
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
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'status' => 'Aprovado'
            ]),
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
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '69214841063'
                ])
            ])
        ]);

        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1975-10-15',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => $preRegistroCpf_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '60923317058'
                ])
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
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Negado'
            ])
        ]);

        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Negado'
            ])
        ]);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => factory('App\PreRegistro')->make([
                'user_externo_id' => 1,
            ]),
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
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Negado'
            ])
        ]);

        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Aprovado'
            ])
        ]);

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertRedirect(route('externo.preregistro.view'));

        $preRegistroCpf = factory('App\PreRegistroCpf')->make([
            'pre_registro_id' => factory('App\PreRegistro')->make([
                'user_externo_id' => 1,
            ]),
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
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = [
            'nome_social' => $faker->text(500),
            'naturalidade_cidade' => $faker->text(500),
            'nome_mae' => $faker->text(500),
            'nome_pai' => $faker->text(500),
            'identidade' => $faker->text(500),
            'orgao_emissor' => $faker->text(500),
            'titulo_eleitor' => $faker->text(500),
            'zona' => $faker->text(500),
            'secao' => $faker->text(500),
            'ra_reservista' => $faker->text(500),
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
    public function view_message_errors_when_submit_pf()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $dados = [
            'idregional' => null,'segmento' => '1','cep' => null,'logradouro' => null,'numero' => null,
            'bairro' => null,'cidade' => null,'uf' => null,'tipo_telefone' => null,'telefone' => null,
            'opcional_celular.*' => ['S'],'tipo_telefone_1' => '1','telefone_1' => '(1)','opcional_celular_1.*' => ['S'],
            'dt_nascimento' => null,'sexo' => null,'estado_civil' => 'e','nacionalidade' => null,'naturalidade_cidade' => 'r',
            'naturalidade_estado' => 'f','nome_mae' => null,'nome_pai' => 'p','tipo_identidade' => null,'identidade' => null,
            'orgao_emissor' => null,'dt_expedicao' => null,'titulo_eleitoral' => null,'zona' => null,'secao' => null, 
            'ra_reservista' => '123','path' => null,'pergunta' => '1'
        ];

        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));

        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);
    }

    /** @test */
    public function can_submit_pre_registro_cpf()
    {
        Mail::fake();
        Storage::fake('local');

        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $pr = factory('App\PreRegistroCpf')->states('request')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();
        Anexo::first()->delete();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertOk();

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        Mail::assertQueued(PreRegistroMail::class);
        
        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cpf', $pr);

        $this->assertDatabaseHas('anexos', [
            'nome_original' => 'random.pdf'
        ]);

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
        Storage::disk('local')->assertExists(PreRegistro::find(1)->anexos->first()->path);
    }

    /** @test */
    public function can_submit_pre_registro_cpf_if_nacionalidade_different_option_brasileira()
    {        
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $pr = factory('App\PreRegistroCpf')->states('request')->make([
            'nacionalidade' => 'Chilena',
            'naturalidade_cidade' => null,
            'naturalidade_estado' => null,
            'titulo_eleitor' => null,
            'zona' => null,
            'secao' => null,
            'ra_reservista' => null,
        ]);
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();

        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cpf', $pr);
    }

    /** @test */
    public function can_submit_pre_registros_cpf_when_exists_others_pre_registros()
    {
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1970-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '69214841063'
                ])
            ])
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1975-10-15',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '60923317058'
                ])
            ])
        ])->attributesToArray();

        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $pr = factory('App\PreRegistroCpf')->states('request')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();

        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cpf', $pr);

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());
    }

    /** @test */
    public function can_submit_pre_registros_cpf_when_exists_others_pre_registros_with_same_user_and_negado()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Negado'
            ])
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Negado'
            ])
        ])->attributesToArray();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();  

        $pr = factory('App\PreRegistroCpf')->states('request')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cpf', $pr);

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());
    }

    /** @test */
    public function cannot_submit_pre_registros_cpf_when_exists_others_pre_registros_with_same_user()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Negado'
            ])
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $externo->id,
                'status' => 'Aprovado'
            ])
        ])->attributesToArray();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertRedirect(route('externo.preregistro.view'));

        $pr = factory('App\PreRegistroCpf')->states('request')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();
        Anexo::first()->delete();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('path');

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
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
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '69214841063'
                ])
            ])
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1975-10-15',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '60923317058'
                ])
            ])
        ])->attributesToArray();

        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();  

        $pr = factory('App\PreRegistroCpf')->states('request')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cpf', $pr);

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());
    }

    /** @test */
    public function can_submit_pre_registro_cpf_without_optional_inputs()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $prCpf = factory('App\PreRegistroCpf')->states('request')->make([
            'nome_social' => null,
            'estado_civil' => null,
            'nome_pai' => null,
            'ra_reservista' => null,
        ]);
        $dados = $prCpf->final;
        $pr = $prCpf->preRegistro->attributesToArray();
        $prCpf = $prCpf->makeHidden(['final'])->attributesToArray();
        $prCpf['dt_nascimento'] = '1970-05-05';
        $dados['dt_nascimento'] = '1970-05-05';

        $pr['contabil_id'] = null;
        $pr['segmento'] = null;
        $pr['opcional_celular'] = ';';
        $pr['telefone'] = '(11) 00000-0000;';
        $pr['tipo_telefone'] = 'CELULAR;';

        $dados['cnpj_contabil'] = null;
        $dados['nome_contabil'] = null;
        $dados['email_contabil'] = null;
        $dados['nome_contato_contabil'] = null;
        $dados['telefone_contabil'] = null;
        $dados['segmento'] = null;
        $dados['opcional_celular'] = [];
        $dados['telefone_1'] = null;
        $dados['tipo_telefone_1'] = null;
        $dados['opcional_celular_1'] = [];    

        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros', $pr);

        foreach($prCpf as $key1 => $value1)
            $prCpf[$key1] = isset($value1) ? mb_strtoupper($value1, 'UTF-8') : $value1;
        $this->assertDatabaseHas('pre_registros_cpf', $prCpf);
    }

    /** @test */
    public function cannot_submit_pre_registro_cpf_without_required_inputs()
    {
        $externo = $this->signInAsUserExterno();
        $dados = [
            'idregional' => '','cep' => '','bairro' => '','logradouro' => '','numero' => '','cidade' => '','uf' => '',
            'tipo_telefone' => '','telefone' => '','sexo' => '','dt_nascimento' => '','nacionalidade' => '','nome_mae' => '',
            'tipo_identidade' => '','identidade' => '','orgao_emissor' => '','dt_expedicao' => '','path' => '', 'pergunta' => ''
        ];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors([
            'idregional','cep','bairro','logradouro','numero','cidade','uf','tipo_telefone','telefone','sexo','dt_nascimento',
            'nacionalidade','nome_mae','tipo_identidade','identidade','orgao_emissor','dt_expedicao','path','pergunta'
        ]);

        $pr = $externo->load('preRegistro')->preRegistro;

        $this->assertDatabaseHas('pre_registros', $pr->toArray());
        $this->assertDatabaseHas('pre_registros_cpf', $pr->pessoaFisica->toArray());
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.pdf'
        ]);
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_social'] = 'Nome';
                
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_social'] = $faker->text(500);     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_with_numbers()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_social'] = 'Nome Socia1';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_sexo()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['sexo'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('sexo');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_sexo_value_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['sexo'] = 'B';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('sexo');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_dt_nascimento()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_nascimento'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_incorrect_format()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_nascimento'] = '2000/12/21';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_without_date_type()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_nascimento'] = 'texto';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_under_18_years_old()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_nascimento'] = Carbon::today()->subYears(17)->format('Y-m-d');     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_estado_civil_wrong_value()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['estado_civil'] = 'Qualquer um';     
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('estado_civil');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_nacionalidade()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nacionalidade'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nacionalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nacionalidade_with_value_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nacionalidade'] = 'Qualquer';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nacionalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_naturalidade_cidade_when_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['naturalidade_cidade'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('naturalidade_cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_naturalidade_cidade_less_than_4_chars_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['naturalidade_cidade'] = 'Qua';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('naturalidade_cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_naturalidade_cidade_more_than_191_chars_if_nacionalidade_brasileira()
    {
        $faker = \Faker\Factory::create();

        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['naturalidade_cidade'] = $faker->text(500);     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('naturalidade_cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_naturalidade_estado_when_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['naturalidade_estado'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('naturalidade_estado');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_naturalidade_estado_with_value_wrong_when_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['naturalidade_estado'] = 'UF';
            
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('naturalidade_estado');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_nome_mae()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_mae'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_mae'] = 'Nome';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_mae'] = $faker->text(500);     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_with_numbers()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_mae'] = 'N0me Mãe';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_pai_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_pai'] = 'Nome';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_pai_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_pai'] = $faker->text(500);     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_pai_with_numbers()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_pai'] = 'Nom3 pai';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_tipo_identidade()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['tipo_identidade'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_tipo_identidade_with_wrong_value()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['tipo_identidade'] = 'Teste';
    
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_identidade()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['identidade'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_identidade_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['identidade'] = '123';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_identidade_more_than_30_chars()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['identidade'] = '1234567890123456789012345678901';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_orgao_emissor()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['orgao_emissor'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('orgao_emissor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_orgao_emissor_less_than_3_chars()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['orgao_emissor'] = 'SS';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('orgao_emissor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_orgao_emissor_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['orgao_emissor'] = $faker->text(500);     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('orgao_emissor');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_dt_expedicao()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_expedicao'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_expedicao_incorrect_format()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_expedicao'] = '2000/12/21';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_expedicao_without_date_type()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_expedicao'] = 'text';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_expedicao_after_today()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_expedicao'] = Carbon::today()->addDay()->format('Y-m-d');     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_titulo_eleitor_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['titulo_eleitor'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('titulo_eleitor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_titulo_eleitor_less_than_12_chars_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['titulo_eleitor'] = '23569874521';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('titulo_eleitor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_titulo_eleitor_more_than_15_chars_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['titulo_eleitor'] = '2356987452123658';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('titulo_eleitor');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_zona_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['zona'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('zona');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_zona_more_than_6_chars_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['zona'] = '7536985';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('zona');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_secao_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['secao'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('secao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_secao_more_than_8_chars_if_nacionalidade_brasileira()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['secao'] = '753698575';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('secao');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_ra_reservista_if_sexo_m_and_under_46_years_old()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        PreRegistro::first()->pessoaFisica->update(['dt_nascimento' => '1995-05-23']);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['ra_reservista'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('ra_reservista');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_ra_reservista_less_than_12_chars_if_sexo_m_and_under_46_years_old()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        PreRegistro::first()->pessoaFisica->update(['dt_nascimento' => '1995-05-23']);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['ra_reservista'] = '55522211174';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('ra_reservista');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_ra_reservista_more_than_15_chars_if_sexo_m_and_under_46_years_old()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        PreRegistro::first()->pessoaFisica->update(['dt_nascimento' => '1995-05-23']);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['ra_reservista'] = '5552221117488874';     
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('ra_reservista');
    }

    /** @test */
    public function log_is_generated_when_form_pf_is_submitted()
    {
        $externo = $this->signInAsUserExterno();
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;

        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertViewIs('site.userExterno.inserir-pre-registro');
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

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CRIADO, PreRegistro::STATUS_CORRECAO]))
                in_array($status, [PreRegistro::STATUS_APROVADO, PreRegistro::STATUS_NEGADO]) ? 
                $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertSessionHasErrors('path') : 
                $this->put(route('externo.inserir.preregistro'))->assertUnauthorized();
        }
    }

    /** @test */
    public function can_submit_pre_registro_with_status_aguardando_correcao_or_sendo_elaborado()
    {
        Mail::fake();
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistroCpf')->create()->preRegistro;

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;

        $s = [PreRegistro::STATUS_CRIADO => PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_CORRECAO => PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach([PreRegistro::STATUS_CRIADO, PreRegistro::STATUS_CORRECAO] as $status)
        {
            $preRegistro->update(['status' => $status]);
            if($status == PreRegistro::STATUS_CORRECAO)
                $dados['nome_mae'] = 'Outro nome da mãe';
            $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertViewIs('site.userExterno.inserir-pre-registro');
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
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()->id
        ])->preRegistro;

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;

        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertViewIs('site.userExterno.inserir-pre-registro');
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

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['path'] = null;
           
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $pr = PreRegistro::first();
        $arrayFinal = array_diff(array_keys(json_decode($pr->campos_espelho, true)), array_keys($dados));
        $this->assertEquals($arrayFinal, array());
    }

    /** @test */
    public function filled_campos_editados_pre_registros_cpf_when_form_is_submitted_when_status_aguardando_correcao()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()->id
        ])->makeHidden(['pre_registro_id', 'created_at', 'updated_at', 'id']);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;

        $dados['nome_social'] = 'Nome Social';
        $dados['dt_nascimento'] = '1988-08-01';
        $dados['sexo'] = 'F';
        $dados['estado_civil'] = estados_civis()[2];
        $dados['nacionalidade'] = 'Chilena';
        $dados['naturalidade_cidade'] = null;
        $dados['naturalidade_estado'] = null;
        $dados['nome_mae'] = 'Nome mãe';
        $dados['nome_pai'] = 'Nome pai';
        $dados['tipo_identidade'] = tipos_identidade()[2];
        $dados['identidade'] = '111122233';
        $dados['orgao_emissor'] = 'DENATRAN';
        $dados['dt_expedicao'] = '2022-01-05';
        $dados['titulo_eleitor'] = '999666888555';
        $dados['zona'] = '1';
        $dados['secao'] = '12';
        $dados['ra_reservista'] = '444555666777';
      
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));
        $pr = PreRegistro::first();
        $dados = Arr::except($dados, ['final', 'created_at', 'updated_at', 'deleted_at', 'pergunta']);

        $arrayFinal = array_diff(array_keys($dados), array_keys(json_decode($pr->campos_espelho, true)));
        $this->assertEquals($arrayFinal, array());
        $arrayFinal = array_diff(array_keys($preRegistroCpf->attributesToArray()), array_keys($pr->getCamposEditados()));
        $this->assertEquals($arrayFinal, array());
    }

    /** 
     * ===============================================================================================================
     * TESTES PRE-REGISTRO-CPF - LOGIN CONTABILIDADE RESPONSÁVEL PELO GERENCIAMENTO PARA O USUARIO EXTERNO COMUM
     * ===============================================================================================================
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
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'status' => 'Negado'
            ]),
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
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'status' => 'Aprovado'
            ]),
        ]);

        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
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
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '69214841063'
                ])
            ])
        ]);

        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1975-10-15',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => $preRegistroCpf_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '60923317058'
                ])
            ])
        ]);

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create(['cnpj' => '67779004000190']));
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
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'status' => 'Negado'
            ])
        ]);

        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $preRegistroCpf_1->preRegistro->userExterno->id,
                'status' => 'Negado'
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
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'status' => 'Negado'
            ])
        ]);

        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => $preRegistroCpf_1->preRegistro->userExterno->id,
                'status' => 'Aprovado'
            ])
        ]);

        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
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
            ])->assertNotFound();

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
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $preRegistroCpf = [
            'nome_social' => $faker->text(500),
            'naturalidade_cidade' => $faker->text(500),
            'nome_mae' => $faker->text(500),
            'nome_pai' => $faker->text(500),
            'identidade' => $faker->text(500),
            'orgao_emissor' => $faker->text(500),
            'titulo_eleitor' => $faker->text(500),
            'zona' => $faker->text(500),
            'secao' => $faker->text(500),
            'ra_reservista' => $faker->text(500),
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

    /** @test */
    public function view_message_errors_when_submit_pf_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        
        $dados = [
            'idregional' => null,'segmento' => '1','cep' => null,'logradouro' => null,'numero' => null,
            'bairro' => null,'cidade' => null,'uf' => null,'tipo_telefone' => null,'telefone' => null,
            'opcional_celular.*' => ['S'],'tipo_telefone_1' => '1','telefone_1' => '(1)','opcional_celular_1.*' => ['S'],
            'dt_nascimento' => null,'sexo' => null,'estado_civil' => 'e','nacionalidade' => null,'naturalidade_cidade' => 'r',
            'naturalidade_estado' => 'f','nome_mae' => null,'nome_pai' => 'p','tipo_identidade' => null,'identidade' => null,
            'orgao_emissor' => null,'dt_expedicao' => null,'titulo_eleitor' => '12','zona' => '1234567','secao' => '123456789',
            'path' => null,'pergunta' => '1'
        ];

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))->assertOk();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertRedirect(route('externo.inserir.preregistro.view', ['preRegistro' => 1]));

        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);
    }

    /** @test */
    public function can_submit_pre_registro_cpf_by_contabilidade()
    {
        Mail::fake();
        Storage::fake('local');

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $pr = factory('App\PreRegistroCpf')->states('request')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();
        Anexo::first()->delete();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')->size(100)]
        ])->assertOk();

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))->assertOk();
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)->assertOk();

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSee('<button type="button" class="btn btn-success" id="submitPreRegistro" value="">Enviar</button>'); 

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        Mail::assertQueued(PreRegistroMail::class);
        
        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cpf', $pr);

        $this->assertDatabaseHas('anexos', [
            'nome_original' => 'random.pdf'
        ]);

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
        Storage::disk('local')->assertExists(PreRegistro::find(1)->anexos->first()->path);
    }

    /** @test */
    public function can_submit_pre_registro_cpf_if_nacionalidade_different_option_brasileira_by_contabilidade()
    {        
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $pr = factory('App\PreRegistroCpf')->states('request')->make([
            'nacionalidade' => 'Chilena',
            'naturalidade_cidade' => null,
            'naturalidade_estado' => null,
            'titulo_eleitor' => null,
            'zona' => null,
            'secao' => null,
            'ra_reservista' => null,
        ]);
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cpf', $pr);
    }

    /** @test */
    public function can_submit_pre_registros_cpf_when_exists_others_pre_registros_by_contabilidade()
    {
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1970-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '69214841063'
                ])
            ])
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1975-10-15',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '60923317058'
                ])
            ])
        ])->attributesToArray();

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create(['cnpj' => '67779004000190']));
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $pr = factory('App\PreRegistroCpf')->states('request')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 3]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 3]));

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cpf', $pr);

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2);
        $this->assertDatabaseHas('pre_registros', $externo->preRegistros->first()->fresh()->toArray());
    }

    /** @test */
    public function can_submit_pre_registros_cpf_when_exists_others_pre_registros_with_same_user_and_negado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'status' => 'Negado'
            ])
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => 1,
                'status' => 'Negado'
            ])
        ])->attributesToArray();

        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $pr = factory('App\PreRegistroCpf')->states('request')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 3]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 3]));

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cpf', $pr);

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2);
        $this->assertDatabaseHas('pre_registros', $externo->preRegistros->first()->fresh()->toArray());
    }

    /** @test */
    public function cannot_submit_pre_registros_cpf_when_exists_others_pre_registros_with_same_user_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistroCpf_1 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'status' => 'Negado'
            ])
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => null,
                'user_externo_id' => 1,
                'status' => 'Aprovado'
            ])
        ])->attributesToArray();

        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));

        $pr = factory('App\PreRegistroCpf')->states('request')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();
        Anexo::first()->delete();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertSessionHasErrors('path');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 3]))
        ->assertUnauthorized();

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
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
            'dt_nascimento' => '1970-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '69214841063'
                ])
            ])
        ])->attributesToArray();
        $preRegistroCpf_2 = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => '1975-10-15',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => 1,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '60923317058'
                ])
            ])
        ])->attributesToArray();

        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $pr = factory('App\PreRegistroCpf')->states('request')->make();
        $dados = $pr->final;
        $pr = $pr->makeHidden(['final'])->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 3]), $dados)
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 3]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 3]));

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros_cpf', $pr);

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_1);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf_2);
        $this->assertDatabaseHas('pre_registros', $externo->preRegistros->first()->fresh()->toArray());
    }

    /** @test */
    public function can_submit_pre_registro_cpf_without_optional_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $prCpf = factory('App\PreRegistroCpf')->states('request')->make([
            'nome_social' => null,
            'estado_civil' => null,
            'nome_pai' => null
        ]);
        $dados = $prCpf->final;
        $pr = $prCpf->preRegistro->attributesToArray();
        $prCpf = $prCpf->makeHidden(['final'])->attributesToArray();

        $pr['segmento'] = null;
        $pr['opcional_celular'] = ';';
        $pr['telefone'] = '(11) 00000-0000;';
        $pr['tipo_telefone'] = 'CELULAR;';

        $dados['segmento'] = null;
        $dados['opcional_celular'] = [];
        $dados['telefone_1'] = null;
        $dados['tipo_telefone_1'] = null;
        $dados['opcional_celular_1'] = [];        

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        foreach($pr as $key => $value)
            $pr[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('pre_registros', $pr);

        foreach($prCpf as $key1 => $value1)
            $prCpf[$key1] = isset($value1) ? mb_strtoupper($value1, 'UTF-8') : $value1;
        $this->assertDatabaseHas('pre_registros_cpf', $prCpf);
    }

    /** @test */
    public function cannot_submit_pre_registro_cpf_without_required_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $dados = [
            'idregional' => '','cep' => '','bairro' => '','logradouro' => '','numero' => '','cidade' => '','uf' => '',
            'tipo_telefone' => '','telefone' => '','sexo' => '','dt_nascimento' => '','nacionalidade' => '','nome_mae' => '',
            'tipo_identidade' => '','identidade' => '','orgao_emissor' => '','dt_expedicao' => '','path' => '', 'pergunta' => ''
        ];
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors([
            'idregional','cep','bairro','logradouro','numero','cidade','uf','tipo_telefone','telefone','sexo','dt_nascimento',
            'nacionalidade','nome_mae','tipo_identidade','identidade','orgao_emissor','dt_expedicao','path','pergunta'
        ]);

        $pr = $externo->preRegistros->first()->fresh();

        $this->assertDatabaseHas('pre_registros', $pr->toArray());
        $this->assertDatabaseHas('pre_registros_cpf', $pr->pessoaFisica->toArray());
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.pdf'
        ]);
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        
        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_social'] = 'Nome';
                
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_social'] = $faker->text(500);     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_social'] = 'Nome Socia1';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_sexo_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['sexo'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('sexo');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_sexo_value_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['sexo'] = 'B';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('sexo');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_dt_nascimento_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_nascimento'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_incorrect_format_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_nascimento'] = '2000/12/21';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_nascimento'] = 'texto';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_under_18_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_nascimento'] = Carbon::today()->subYears(17)->format('Y-m-d');     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_estado_civil_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['estado_civil'] = 'Qualquer um';     
       
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('estado_civil');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_nacionalidade_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nacionalidade'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nacionalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nacionalidade_with_value_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nacionalidade'] = 'Qualquer';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nacionalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_naturalidade_cidade_when_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['naturalidade_cidade'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('naturalidade_cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_naturalidade_cidade_less_than_4_chars_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['naturalidade_cidade'] = 'Qua';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('naturalidade_cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_naturalidade_cidade_more_than_191_chars_if_nacionalidade_brasileira_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['naturalidade_cidade'] = $faker->text(500);     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('naturalidade_cidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_naturalidade_estado_when_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['naturalidade_estado'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('naturalidade_estado');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_naturalidade_estado_with_value_wrong_when_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['naturalidade_estado'] = 'UF';
            
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('naturalidade_estado');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_nome_mae_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_mae'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_mae'] = 'Nome';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_mae'] = $faker->text(500);     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_mae'] = 'N0me Mãe';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_pai_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_pai'] = 'Nome';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_pai_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_pai'] = $faker->text(500);     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_pai_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['nome_pai'] = 'Nom3 pai';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_tipo_identidade_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['tipo_identidade'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('tipo_identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_tipo_identidade_with_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['tipo_identidade'] = 'Teste';
    
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('tipo_identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_identidade_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['identidade'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_identidade_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['identidade'] = '123';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_identidade_more_than_30_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['identidade'] = '1234567890123456789012345678901';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_orgao_emissor_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['orgao_emissor'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('orgao_emissor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_orgao_emissor_less_than_3_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['orgao_emissor'] = 'SS';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('orgao_emissor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_orgao_emissor_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['orgao_emissor'] = $faker->text(500);     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('orgao_emissor');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_dt_expedicao_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_expedicao'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_expedicao_incorrect_format_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_expedicao'] = '2000/12/21';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_expedicao_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_expedicao'] = 'text';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_expedicao_after_today_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['dt_expedicao'] = Carbon::today()->addDay()->format('Y-m-d');     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_titulo_eleitor_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['titulo_eleitor'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('titulo_eleitor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_titulo_eleitor_less_than_12_chars_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['titulo_eleitor'] = '23569874521';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('titulo_eleitor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_titulo_eleitor_more_than_15_chars_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['titulo_eleitor'] = '2356987452123658';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('titulo_eleitor');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_zona_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['zona'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('zona');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_zona_more_than_6_chars_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['zona'] = '7536985';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('zona');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_secao_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['secao'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('secao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_secao_more_than_8_chars_if_nacionalidade_brasileira_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['secao'] = '753698575';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('secao');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_ra_reservista_if_sexo_m_and_under_46_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        PreRegistro::first()->pessoaFisica->update(['dt_nascimento' => '1995-05-23']);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['ra_reservista'] = '';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('ra_reservista');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_ra_reservista_less_than_12_chars_if_sexo_m_and_under_46_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        PreRegistro::first()->pessoaFisica->update(['dt_nascimento' => '1995-05-23']);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['ra_reservista'] = '55522211174';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('ra_reservista');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_ra_reservista_more_than_15_chars_if_sexo_m_and_under_46_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        PreRegistro::first()->pessoaFisica->update(['dt_nascimento' => '1995-05-23']);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['ra_reservista'] = '5552221117488874';     
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('ra_reservista');
    }

    /** @test */
    public function log_is_generated_when_form_pf_is_submitted_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
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

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CRIADO, PreRegistro::STATUS_CORRECAO]))
                in_array($status, [PreRegistro::STATUS_APROVADO, PreRegistro::STATUS_NEGADO]) ? 
                $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)->assertSessionHasErrors('path') : 
                $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))->assertUnauthorized();
        }
    }

    /** @test */
    public function can_submit_pre_registro_with_status_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        Mail::fake();
        $externo = $this->signInAsUserExterno('contabil');
        $preRegistro = factory('App\PreRegistroCpf')->create()->preRegistro;

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;

        $s = [PreRegistro::STATUS_CRIADO => PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_CORRECAO => PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach([PreRegistro::STATUS_CRIADO, PreRegistro::STATUS_CORRECAO] as $status)
        {
            $preRegistro->update(['status' => $status]);
            if($status == PreRegistro::STATUS_CORRECAO)
                $dados['nome_mae'] = 'Outro nome da mãe';

            $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
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
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()->id
        ])->preRegistro;

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
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
        $dados = factory('App\UserExterno')->states('cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados['path'] = null;
           
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $pr = PreRegistro::first();
        $arrayFinal = array_diff(array_keys(json_decode($pr->campos_espelho, true)), array_keys($dados));
        $this->assertEquals($arrayFinal, array());
    }

    /** @test */
    public function filled_campos_editados_pre_registros_cpf_when_form_is_submitted_when_status_aguardando_correcao_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('enviado_correcao')->create()->id
        ])->makeHidden(['pre_registro_id', 'created_at', 'updated_at', 'id']);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;

        $dados['nome_social'] = 'Nome Social';
        $dados['dt_nascimento'] = '1988-08-01';
        $dados['sexo'] = 'F';
        $dados['estado_civil'] = estados_civis()[2];
        $dados['nacionalidade'] = 'Chilena';
        $dados['naturalidade_cidade'] = null;
        $dados['naturalidade_estado'] = null;
        $dados['nome_mae'] = 'Nome mãe';
        $dados['nome_pai'] = 'Nome pai';
        $dados['tipo_identidade'] = tipos_identidade()[2];
        $dados['identidade'] = '111122233';
        $dados['orgao_emissor'] = 'DENATRAN';
        $dados['dt_expedicao'] = '2022-01-05';
        $dados['titulo_eleitor'] = '999888777444';
        $dados['zona'] = '5';
        $dados['secao'] = '88';
        $dados['ra_reservista'] = '222555888777';
      
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));
        $pr = PreRegistro::first();
        $dados = Arr::except($dados, ['final', 'created_at', 'updated_at', 'deleted_at', 'pergunta']);

        $arrayFinal = array_diff(array_keys($dados), array_keys(json_decode($pr->campos_espelho, true)));
        $this->assertEquals($arrayFinal, array());
        $arrayFinal = array_diff(array_keys($preRegistroCpf->attributesToArray()), array_keys($pr->getCamposEditados()));
        $this->assertEquals($arrayFinal, array());
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CPF VIA AJAX - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function can_update_justificativa()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create([
                'telefone' => '(11) 00000-0000;(12) 00000-111',
                'tipo_telefone' => mb_strtoupper(tipos_contatos()[0].';' . tipos_contatos()[0], 'UTF-8'),
                'opcional_celular' => mb_strtoupper(opcoes_celular()[1] . ';' . opcoes_celular()[2], 'UTF-8'),
            ])
        ]);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados = Arr::except($dados, ['final', 'created_at', 'updated_at', 'deleted_at', 'pergunta']);

        $justificativas = array();
        foreach($dados as $campo => $valor)
        {
            $texto = $faker->text(500);
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
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('analise_inicial')->create([
                'telefone' => '(11) 00000-0000;(12) 00000-111',
                'tipo_telefone' => mb_strtoupper(tipos_contatos()[0].';' . tipos_contatos()[0], 'UTF-8'),
                'opcional_celular' => mb_strtoupper(opcoes_celular()[1] . ';' . opcoes_celular()[2], 'UTF-8'),
            ])
        ]);

        $dados = factory('App\PreRegistroCpf')->states('request')->make()->final;
        $dados = Arr::except($dados, ['final', 'created_at', 'updated_at', 'deleted_at', 'pergunta']);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            if(in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                foreach($dados as $campo => $valor)
                    $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                        'acao' => 'justificar',
                        'campo' => $campo,
                        'valor' => $faker->text(500)
                    ])->assertStatus(200);    
        }
    }

    /** @test */
    public function can_edit_justificativas()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $dados = $preRegistroCpf->preRegistro->getJustificativaArray();
        foreach($dados as $campo => $valor)
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
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $dados = $preRegistroCpf->preRegistro->getJustificativaArray();
        foreach($dados as $campo => $valor)
        {
            $texto = $faker->text(1000);
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
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $dados = $preRegistroCpf->preRegistro->getJustificativaArray();
        foreach($dados as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo . '_erro',
                'valor' => $faker->text(500)
            ])->assertSessionHasErrors('campo');
    }

    /** @test */
    public function cannot_update_justificativa_with_wrong_input_acao()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $dados = $preRegistroCpf->preRegistro->getJustificativaArray();
        foreach($dados as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                'acao' => 'justificar_',
                'campo' => $campo,
                'valor' => $faker->text(500)
            ])->assertSessionHasErrors('acao'); 
    }

    /** @test */
    public function cannot_update_justificativa_with_status_different_em_analise_or_analise_da_correcao()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $dados = Arr::only($preRegistroCpf->preRegistro->getJustificativaArray(), ['segmento', 'path', 'nome_mae', 'nome_pai', 'tipo_identidade']);
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                foreach($dados as $campo => $valor)
                    $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                        'acao' => 'justificar',
                        'campo' => $campo,
                        'valor' => $faker->text(500)
                    ])->assertStatus(401);    
        }
    }

    /** @test */
    public function log_is_generated_when_update_justificativa()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $dados = $preRegistroCpf->preRegistro->getJustificativaArray();
        foreach($dados as $campo => $valor)
        {
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $faker->text(500)
            ])->assertOk(); 

            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
            $txt = $inicio . 'Usuário (usuário 1) fez a ação de "justificar" o campo "' . $campo . '", ';
            $txt .= 'inserindo ou removendo valor *pré-registro* (id: '.$preRegistroCpf->preRegistro->id.')';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function can_save_inputs()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $campos = ['registro_secundario' => '000011234'];

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo,
                'valor' => $valor
            ])->assertStatus(200);    

        $this->assertDatabaseHas('pre_registros', $campos);
    }

    /** @test */
    public function log_is_generated_when_save_inputs()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $campos = ['registro_secundario' => '000011234'];

        foreach($campos as $campo => $valor)
        {
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo,
                'valor' => $valor
            ])->assertStatus(200);  

            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
            $txt = $inicio . 'Usuário (usuário 1) fez a ação de "editar" o campo "' . $campo . '", ';
            $txt .= 'inserindo ou removendo valor *pré-registro* (id: '.$preRegistroCpf->preRegistro->id.')';
            $this->assertStringContainsString($txt, $log);
        }  

        $this->assertDatabaseHas('pre_registros', $campos);
    }

    /** @test */
    public function can_clean_inputs_saved_after_update()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $campos = ['registro_secundario' => '000011234'];

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo,
                'valor' => $valor
            ])->assertStatus(200);    

        $this->assertDatabaseHas('pre_registros', $campos);

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo,
                'valor' => ''
            ])->assertStatus(200);    

        $this->assertDatabaseMissing('pre_registros', $campos);
    }

    /** @test */
    public function cannot_save_input_registro_secundario_with_more_than_20_chars()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'editar',
            'campo' => 'registro_secundario',
            'valor' => '000011234541235987532'
        ])->assertSessionHasErrors('valor');    
    }

    /** @test */
    public function cannot_save_inputs_with_wrong_action()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $campos = ['registro_secundario' => '000011234'];

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'editar_',
                'campo' => $campo,
                'valor' => $valor
            ])->assertSessionHasErrors('acao');    
    }

    /** @test */
    public function cannot_save_inputs_with_wrong_field()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $campos = ['registro_secundario' => '000011234'];

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo . '-',
                'valor' => $valor
            ])->assertSessionHasErrors('campo');     
    }

    /** @test */
    public function can_check_anexos()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
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

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
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

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
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

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $tipo = 'Cerificado de reservista ou dispensa';
        $arrayAnexos['Cerificado de reservista ou dispensa'] = "OK";

        $preRegistroCpf->update(['dt_nascimento' => Carbon::today()->subYears(46)->format('Y-m-d')]);
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

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
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

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
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

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
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
        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
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

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();

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

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $arrayAnexos = array();
        foreach($preRegistroCpf->preRegistro->anexos->first()->getObrigatoriosPreRegistro() as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['confere_anexos' => $final]);

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
    public function can_update_historico_justificativas_when_status_enviar_para_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $preRegistroCpf->fresh()->preRegistro->setHistoricoStatus();

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'corrigir'])
        ->assertRedirect(route('preregistro.index'));

        $hist_justificativas = $preRegistroCpf->fresh()->preRegistro->getHistoricoJustificativas();
        $just_1 = $hist_justificativas[0];
        $just_2 = $hist_justificativas[1];
        
        $this->get(route('preregistro.view', $preRegistroCpf->pre_registro_id))
        ->assertSee('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;')
        ->assertSeeInOrder($just_1)
        ->assertSeeInOrder($just_2);

        $this->assertDatabaseHas('pre_registros', [
            'status' => PreRegistro::STATUS_CORRECAO,
            'historico_justificativas' => $preRegistroCpf->fresh()->preRegistro->historico_justificativas,
        ]);
    }

    /** @test */
    public function can_update_status_enviar_para_correcao_without_confere_anexos()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

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

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $preRegistroCpf->preRegistro->update(['justificativa' => null]);

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

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $preRegistroCpf->preRegistro->update(['justificativa' => '{"negado":"teste"}']);

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'corrigir'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCpf->pre_registro_id))
        ->assertSeeText('Não possui justificativa(s)');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_CORRECAO);
    }

    /** @test */
    public function cannot_update_status_enviar_para_correcao_with_status_different_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

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

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

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

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        
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
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        foreach($anexo->first()->getObrigatoriosPreRegistro() as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
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
    public function can_update_historico_justificativas_with_status_negado()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);
        $preRegistroCpf->fresh()->preRegistro->setHistoricoStatus();

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
        ])->assertStatus(200); 

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'negar'])
        ->assertRedirect(route('preregistro.index'));
        
        $hist_justificativas = $preRegistroCpf->fresh()->preRegistro->getHistoricoJustificativas();
        $just_1 = $hist_justificativas[0];
        $just_2 = $hist_justificativas[1];

        $this->get(route('preregistro.view', $preRegistroCpf->pre_registro_id))
        ->assertSee('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;')
        ->assertSeeInOrder($just_1)
        ->assertSeeInOrder($just_2);

        $this->assertDatabaseHas('pre_registros', [
            'status' => PreRegistro::STATUS_NEGADO,
            'idusuario' => $admin->idusuario,
            'historico_justificativas' => $preRegistroCpf->fresh()->preRegistro->historico_justificativas,
        ]);
    }

    /** @test */
    public function log_is_generated_when_update_status_negado()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
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
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
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

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        $tipos = $anexo->first()->getOpcoesPreRegistro();
        foreach($tipos as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

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
        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();

        $this->put(route('preregistro.update.status', $preRegistroCpf->pre_registro_id), ['situacao' => 'negar'])
        ->assertSessionHasErrors('status');

        $this->get(route('preregistro.view', $preRegistroCpf->pre_registro_id))
        ->assertSeeText('Não possui justificativa(s)');

        $this->assertNotEquals(PreRegistro::first()->status, PreRegistro::STATUS_NEGADO);
    }

    /** @test */
    public function can_update_status_negado_with_others_justificativa_and_negado()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
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
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
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
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
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

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        foreach($anexo->first()->getObrigatoriosPreRegistro() as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

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

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        foreach($anexo->first()->getObrigatoriosPreRegistro() as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

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

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);

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

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();

        $arrayAnexos = array();
        $tipos = Anexo::first()->getOpcoesPreRegistro();
        foreach($tipos as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

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

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        $tipos = $anexo->first()->getOpcoesPreRegistro();
        foreach($tipos as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['confere_anexos' => $final]);

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

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();

        $arrayAnexos = array();
        $tipos = $anexo->first()->getOpcoesPreRegistro();
        foreach($tipos as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['confere_anexos' => $final]);

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

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);

        $arrayAnexos = array();
        foreach($anexo->first()->getObrigatoriosPreRegistro() as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['confere_anexos' => $final]);

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

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);

        $arrayAnexos = array();
        foreach($anexo->first()->getObrigatoriosPreRegistro() as $tipo)
            $arrayAnexos[$tipo] = "OK";

        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['confere_anexos' => $final]);

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

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);
        
        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeText($preRegistroCpf->nome_social)
        ->assertSeeText(onlyDate($preRegistroCpf->dt_nascimento))
        ->assertSeeText($preRegistroCpf->sexo)
        ->assertSeeText($preRegistroCpf->estado_civil)
        ->assertSeeText($preRegistroCpf->nacionalidade)
        ->assertSeeText($preRegistroCpf->naturalidade_cidade)
        ->assertSeeText($preRegistroCpf->naturalidade_estado)
        ->assertSeeText($preRegistroCpf->nome_mae)
        ->assertSeeText($preRegistroCpf->nome_pai)
        ->assertSeeText($preRegistroCpf->tipo_identidade)
        ->assertSeeText($preRegistroCpf->identidade)
        ->assertSeeText($preRegistroCpf->orgao_emissor)
        ->assertSeeText(onlyDate($preRegistroCpf->dt_expedicao))
        ->assertSeeText($preRegistroCpf->titulo_eleitor)
        ->assertSeeText($preRegistroCpf->zona)
        ->assertSeeText($preRegistroCpf->secao)
        ->assertSeeText($preRegistroCpf->ra_reservista);
    }

    /** @test */
    public function view_text_justificado_cpf()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $justificativas = $preRegistroCpf->preRegistro->getJustificativaArray();

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
    public function view_label_campo_alterado_pf()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('campos_editados')->create();
        $preRegistroCpf->preRegistro->update([
            'opcional_celular' => 'SMS;TELEGRAM',
            'telefone' => '(11) 00000-0000;(11) 00000-0000',
            'tipo_telefone' => mb_strtoupper(tipos_contatos()[0] . ';' . tipos_contatos()[0], 'UTF-8'),
        ]);
        $camposEditados = json_decode($preRegistroCpf->preRegistro->campos_editados, true);

        foreach($camposEditados as $key => $value)
        {
            $preRegistroCpf->preRegistro->update([
                'campos_editados' => json_encode([$key => null], JSON_FORCE_OBJECT)
            ]);
            $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
            ->assertSee('<span class="badge badge-danger ml-2">Campos alterados</span>')
            ->assertSee('<span class="badge badge-danger ml-2">Campo alterado</span>');
        }
    }
}
