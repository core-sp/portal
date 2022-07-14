<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\PreRegistro;
use Carbon\Carbon;

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
    public function log_is_generated_when_form_cpf_is_created()
    {
        $externo = $this->signInAsUserExterno();
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));     

        $pr = PreRegistro::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString('Usuário Externo com cpf: ' . $pr->userExterno->cpf_cnpj, $log);
        $this->assertStringContainsString(', iniciou o processo de solicitação de registro com a id: ' . $pr->id, $log);
    }

    /** @test */
    public function view_msg_update()
    {
        $externo = $this->signInAsUserExterno();
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
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

        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        unset($preRegistroCpf['pre_registro_id']);
        
        foreach($preRegistroCpf as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf);
    }

    /** @test */
    public function can_update_table_pre_registros_cpf_by_ajax_with_upperCase()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id,
        ]);

        unset($preRegistroCpf['pre_registro_id']);
        
        foreach($preRegistroCpf as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        foreach($preRegistroCpf as $key => $value)
            if(isset($value))
                $preRegistroCpf[$key] = mb_strtoupper($value, 'UTF-8');

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf);
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
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        unset($preRegistroCpf['pre_registro_id']);
        
        foreach($preRegistroCpf as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $pr_1 = $preRegistroCpf_1->toArray();
        unset($pr_1['pre_registro']);
        $pr_2 = $preRegistroCpf_2->toArray();
        unset($pr_2['pre_registro']);

        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf);
        $this->assertDatabaseHas('pre_registros_cpf', $pr_1);
        $this->assertDatabaseHas('pre_registros_cpf', $pr_2);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        unset($preRegistroCpf['pre_registro_id']);
        
        foreach($preRegistroCpf as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        unset($preRegistroCpf['pre_registro_id']);
        
        foreach($preRegistroCpf as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        unset($preRegistroCpf['pre_registro_id']);
        
        foreach($preRegistroCpf as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridicaErro',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        unset($preRegistroCpf['pre_registro_id']);
        
        foreach($preRegistroCpf as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaFisica',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_with_input_type_text_more_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCpf = [
            'nome_social' => $faker->sentence(400),
            'estado_civil' => $faker->sentence(400),
            'naturalidade' => $faker->sentence(400),
            'nacionalidade' => $faker->sentence(400),
            'nome_mae' => $faker->sentence(400),
            'nome_pai' => $faker->sentence(400),
            'tipo_identidade' => $faker->sentence(400),
            'identidade' => $faker->sentence(400),
            'orgao_emissor' => $faker->sentence(400),
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

        $preRegistro = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => $externo->id,
            ]),
        ]);

        $preRegistroCpf = $preRegistro->toArray();
        $pular = ['id', 'pre_registro_id', 'updated_at', 'created_at', 'pre_registro'];
        
        foreach($preRegistroCpf as $key => $value)
        {
            if(!in_array($key, $pular))
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'pessoaFisica',
                    'campo' => $key,
                    'valor' => ''
                ])->assertStatus(200);
        }

        unset($preRegistroCpf['pre_registro']);
        
        $this->assertDatabaseMissing('pre_registros_cpf', $preRegistroCpf);
    }

    // Status do pré-registro

    /** @test */
    public function cannot_update_table_pre_registros_cpf_by_ajax_with_status_different_aguardando_correcao_or_null()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => $externo->id,
            ]),
        ]);

        $preRegistroCpf = $preRegistro->toArray();
        $pular = ['id', 'pre_registro_id', 'updated_at', 'created_at', 'pre_registro'];

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            if($status != PreRegistro::STATUS_CORRECAO)
                foreach($preRegistroCpf as $key => $value)
                {
                    if(!in_array($key, $pular))
                        $this->post(route('externo.inserir.preregistro.ajax'), [
                            'classe' => 'pessoaFisica',
                            'campo' => $key,
                            'valor' => ''
                        ])->assertStatus(401);
                }
        }
    }

    /** @test */
    public function can_update_table_pre_registros_cpf_by_ajax_with_status_aguardando_correcao_or_null()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => $externo->id,
            ]),
        ]);

        $preRegistroCpf = $preRegistro->toArray();
        $pular = ['id', 'pre_registro_id', 'updated_at', 'created_at', 'pre_registro'];

        foreach([PreRegistro::STATUS_CORRECAO, null] as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            foreach($preRegistroCpf as $key => $value)
            {
                if(!in_array($key, $pular))
                    $this->post(route('externo.inserir.preregistro.ajax'), [
                        'classe' => 'pessoaFisica',
                        'campo' => $key,
                        'valor' => ''
                    ])->assertStatus(200);
            }
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
            'idregional' => null,
            'segmento' => null,
            'cep' => null,
            'logradouro' => null,
            'numero' => null,
            'bairro' => null,
            'cidade' => null,
            'uf' => null,
            'telefone' => null,
            'tipo_telefone' => null,
            'dt_nascimento' => null,
            'sexo' => null,
            'nacionalidade' => null,
            'nome_mae' => null,
            'tipo_identidade' => null,
            'identidade' => null,
            'orgao_emissor' => null,
            'dt_expedicao' => null,
            'path' => null,
            'pergunta' => null
        ];

        $this->put(route('externo.inserir.preregistro'), $dados)->assertStatus(302);

        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);

        $this->assertEquals(count($keys), count($dados));
    }

    /** @test */
    public function can_submit_pre_registro_cpf()
    {
        Mail::fake();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => 1,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);

        $contabil = factory('App\Contabil')->state('low')->raw();
        $temp = array();
        foreach($contabil as $key => $value)
            $temp[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $temp);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();        

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertOk();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSee('<button type="button" class="btn btn-success" id="submitPreRegistro" value="">Enviar</button>'); 

        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));

        Mail::assertQueued(PreRegistroMail::class);

        $pr = $externo->load('preRegistro')->preRegistro;
        $preRegistro['opcional_celular'] = ';';
        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';';
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';';
        unset($preRegistro['pergunta']);

        foreach($preRegistro as $key => $value)
            $preRegistro[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $preRegistro['status'] = $pr::STATUS_ANALISE_INICIAL;
        unset($preRegistro['historico_contabil']);
        $this->assertDatabaseHas('pre_registros', $preRegistro);

        foreach($preRegistroCpf as $key => $value)
            $preRegistroCpf[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf);

        foreach($contabil as $key => $value)
            if($key != 'email')
                $contabil[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('contabeis', $contabil);

        $this->assertDatabaseHas('anexos', [
            'nome_original' => 'random.pdf'
        ]);

        Storage::disk('local')->assertExists($pr->anexos->first()->path);
    }

    /** @test */
    public function can_submit_pre_registro_cpf_if_nacionalidade_different_option_brasileiro()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nacionalidade' => nacionalidades()[5],
            'naturalidade' => null
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();        

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

        foreach($preRegistroCpf as $key => $value)
            $preRegistroCpf[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf);
    }

    /** @test */
    public function can_submit_pre_registros_cpf_when_exists_others_pre_registros()
    {
        Storage::fake('local');
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
        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => $preRegistroCpf_2->pre_registro_id + 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => $preRegistroCpf_2->preRegistro->contabil_id + 1,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();  

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));
        
        $pr_1 = $preRegistroCpf_1->toArray();
        unset($pr_1['pre_registro']);
        $pr_2 = $preRegistroCpf_2->toArray();
        unset($pr_2['pre_registro']);

        foreach($preRegistroCpf as $key => $value)
            $preRegistroCpf[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf);

        $this->assertDatabaseHas('pre_registros_cpf', $pr_1);
        $this->assertDatabaseHas('pre_registros_cpf', $pr_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());
    }

    /** @test */
    public function can_submit_pre_registros_cpf_when_exists_others_pre_registros_with_same_contabil()
    {
        Storage::fake('local');
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
        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => $preRegistroCpf_2->pre_registro_id + 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => $preRegistroCpf_2->preRegistro->contabil_id,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);

        $contabil = factory('App\Contabil')->raw();
        $temp = array();
        foreach($contabil as $key => $value)
            $temp[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $temp);
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();   

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));
        
        $pr_1 = $preRegistroCpf_1->toArray();
        unset($pr_1['pre_registro']);
        $pr_2 = $preRegistroCpf_2->toArray();
        unset($pr_2['pre_registro']);

        foreach($preRegistroCpf as $key => $value)
            $preRegistroCpf[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf);

        $this->assertDatabaseHas('pre_registros_cpf', $pr_1);
        $this->assertDatabaseHas('pre_registros_cpf', $pr_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());
    }

    /** @test */
    public function can_submit_pre_registro_cpf_without_optional_inputs()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'estado_civil' => null
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();        

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

        $pr = $externo->load('preRegistro')->preRegistro;

        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';';
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';';
        $preRegistro['opcional_celular'] = ';';
        unset($preRegistro['pergunta']);

        foreach($preRegistro as $key => $value)
            $preRegistro[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $preRegistro['status'] = PreRegistro::STATUS_ANALISE_INICIAL;
        unset($preRegistro['historico_contabil']);
        $this->assertDatabaseHas('pre_registros', $preRegistro);

        foreach($preRegistroCpf as $key => $value)
            $preRegistroCpf[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf);

        $this->assertDatabaseHas('anexos', [
            'nome_original' => 'random.pdf'
        ]);

        Storage::disk('local')->assertExists($pr->anexos->first()->path);
    }

    /** @test */
    public function cannot_submit_pre_registro_cpf_without_required_inputs()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $dados = [
            'segmento' => '',
            'idregional' => '',
            'cep' => '',
            'bairro' => '',
            'logradouro' => '',
            'numero' => '',
            'cidade' => '',
            'uf' => '',
            'tipo_telefone' => '',
            'telefone' => '',
            'sexo' => '',
            'dt_nascimento' => '',
            'nacionalidade' => '',
            'nome_mae' => '',
            'tipo_identidade' => '',
            'identidade' => '',
            'orgao_emissor' => '',
            'dt_expedicao' => '',
            'path' => '',
            'pergunta' => '',
        ];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors([
            'segmento',
            'idregional',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'cidade',
            'uf',
            'tipo_telefone',
            'telefone',
            'sexo',
            'dt_nascimento',
            'nacionalidade',
            'nome_mae',
            'tipo_identidade',
            'identidade',
            'orgao_emissor',
            'dt_expedicao',
            'path',
            'pergunta',
        ]);

        $pr = $externo->load('preRegistro')->preRegistro;

        $this->assertDatabaseHas('pre_registros', $pr->toArray());
        $this->assertDatabaseHas('pre_registros_cpf', $pr->pessoaFisica->toArray());
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.pdf'
        ]);
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nome_social' => $faker->sentence(400)
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_with_numbers()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nome_social' => 'N0me Social'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_sexo()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'sexo' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('sexo');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_sexo_wrong_value()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'sexo' => 'N'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('sexo');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_sexo_more_than_1_char()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'sexo' => 'MM'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('sexo');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_dt_nascimento()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'dt_nascimento' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_without_date_type()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'dt_nascimento' => 'texto'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_under_18_years_old()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'dt_nascimento' => Carbon::today()->subYears(17)->format('Y-m-d')
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_estado_civil_wrong_value()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'estado_civil' => 'Qualquer Um'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('estado_civil');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_nacionalidade()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nacionalidade' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nacionalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nacionalidade_with_wrong_value()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nacionalidade' => 'Qualquer Um'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nacionalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_naturalidade_when_nacionalidade_is_option_brasileiro()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'naturalidade' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('naturalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_naturalidade_with_wrong_value_if_nacionalidade_is_option_brasileiro()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'naturalidade' => 'Qualquer Um'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('naturalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_nome_mae()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nome_mae' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nome_mae' => $faker->sentence(400)
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_with_numbers()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nome_mae' => 'N0me Mãe'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_pai_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nome_pai' => $faker->sentence(400)
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_pai_with_numbers()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nome_pai' => 'N0me Pai'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_tipo_identidade()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'tipo_identidade' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_tipo_identidade_with_wrong_value()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'tipo_identidade' => 'Teste'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_identidade()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'identidade' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_identidade_more_than_30_chars()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'identidade' => '0123456789012345678901023-8pl9644'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_orgao_emissor()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'orgao_emissor' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('orgao_emissor');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_orgao_emissor_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'orgao_emissor' => $faker->sentence(400)
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('orgao_emissor');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_dt_expedicao()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'dt_expedicao' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_expedicao_without_date_type()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'dt_expedicao' => 'texto'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_expedicao_after_today()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'dt_expedicao' => Carbon::today()->addDay()->format('Y-m-d')
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_expedicao');
    }

    /** @test */
    public function log_is_generated_when_form_cpf_is_submitted()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ]);
        
        $this->put(route('externo.inserir.preregistro'), $dados);

        $pr = PreRegistro::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString('Usuário Externo com cpf: ' . $pr->userExterno->cpf_cnpj, $log);
        $this->assertStringContainsString(', atualizou o status para ' . $pr::STATUS_ANALISE_INICIAL . ' da solicitação de registro com a id: ' . $pr->id, $log);
    }

    /** @test */
    public function cannot_submit_pre_registro_with_status_different_aguardando_correcao_or_null()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistro')->state('low')->create([
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'opcional_celular' => null,
        ]);
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => 1
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->create([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $dados = array_merge($preRegistro->toArray(), $preRegistroCpf->toArray(), ['pergunta' => 'teste da pergunta']);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->update(['status' => $status]);
            if($status != PreRegistro::STATUS_CORRECAO)
                $this->put(route('externo.inserir.preregistro'), $dados)->assertStatus(401);
        }
    }

    /** @test */
    public function can_submit_pre_registro_with_status_aguardando_correcao_or_null()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistro = factory('App\PreRegistro')->state('low')->create([
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'opcional_celular' => null,
        ]);
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => 1
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->create([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $dados = array_merge($preRegistro->toArray(), $preRegistroCpf->toArray(), ['pergunta' => 'teste da pergunta']);

        foreach([PreRegistro::STATUS_CORRECAO, null] as $status)
        {
            $preRegistro->update(['status' => $status]);
            $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));
            if(isset($status))
                $this->assertEquals(PreRegistro::first()->status, PreRegistro::STATUS_ANALISE_CORRECAO);
        }
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
        $preRegistro = factory('App\PreRegistro')->state('analise_inicial')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistro->id
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => $preRegistro->id
        ]);
        $tempContabil = array();
        foreach($preRegistro->contabil->toArray() as $key => $temp)
            if(!in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
                $tempContabil[$key . '_contabil'] = $temp;

        $dados = array_merge($preRegistro->toArray(), $preRegistroCpf->toArray(), $tempContabil, ['path' => 'nnn']);
        $pular = ['id', 'created_at', 'updated_at', 'deleted_at', 'pre_registro', 'pre_registro_id', 'contabil', 'contabil_id', 'user_externo_id',
        'idusuario', 'status', 'historico_contabil', 'registro_secundario', 'justificativa', 'confere_anexos'];

        $justificativas = array();
        foreach($dados as $campo => $valor)
            if(!in_array($campo, $pular))
            {
                $texto = $faker->text(500);
                $justificativas[$campo] = $texto;
                $this->post(route('preregistro.update.ajax', $preRegistro->id), [
                    'acao' => 'justificar',
                    'campo' => $campo,
                    'valor' => $texto
                ])->assertStatus(200);    
            }

        $this->assertDatabaseHas('pre_registros', [
                'justificativa' => json_encode($justificativas, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function can_update_justificativa_with_status_em_analise_or_analise_da_correcao()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();
        $preRegistro = factory('App\PreRegistro')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistro->id
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => $preRegistro->id
        ]);
        $tempContabil = array();
        foreach($preRegistro->contabil->toArray() as $key => $temp)
            if(!in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
                $tempContabil[$key . '_contabil'] = $temp;

        $dados = array_merge($preRegistro->toArray(), $preRegistroCpf->toArray(), $tempContabil, ['path' => 'nnn']);
        $pular = ['id', 'created_at', 'updated_at', 'deleted_at', 'pre_registro', 'pre_registro_id', 'contabil', 'contabil_id', 'user_externo_id',
        'idusuario', 'status', 'historico_contabil', 'registro_secundario', 'justificativa', 'confere_anexos'];

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->update(['status' => $status]);
            if(in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                foreach($dados as $campo => $valor)
                    if(!in_array($campo, $pular))
                        $this->post(route('preregistro.update.ajax', $preRegistro->id), [
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
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();

        $dados = $preRegistroCpf->preRegistro->getJustificativaArray();
        foreach($dados as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => ''
            ])->assertStatus(200);    

        $this->assertDatabaseHas('pre_registros', [
                'justificativa' => null
        ]);
    }

    /** @test */
    public function cannot_update_justificativa_more_than_500_chars()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();
        $preRegistro = factory('App\PreRegistro')->state('analise_inicial')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistro->id
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => $preRegistro->id
        ]);
        $tempContabil = array();
        foreach($preRegistro->contabil->toArray() as $key => $temp)
            if(!in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
                $tempContabil[$key . '_contabil'] = $temp;

        $dados = array_merge($preRegistro->toArray(), $preRegistroCpf->toArray(), $tempContabil, ['path' => 'nnn']);
        $pular = ['id', 'created_at', 'updated_at', 'deleted_at', 'pre_registro', 'pre_registro_id', 'contabil', 'contabil_id', 'user_externo_id',
        'idusuario', 'status', 'historico_contabil', 'registro_secundario', 'justificativa', 'confere_anexos'];

        $justificativas = array();
        foreach($dados as $campo => $valor)
            if(!in_array($campo, $pular))
            {
                $texto = $faker->text(800);
                $justificativas[$campo] = $texto;
                $this->post(route('preregistro.update.ajax', $preRegistro->id), [
                    'acao' => 'justificar',
                    'campo' => $campo,
                    'valor' => $texto
                ])->assertStatus(302);    
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
        $preRegistro = factory('App\PreRegistro')->state('analise_inicial')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistro->id
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => $preRegistro->id
        ]);
        $tempContabil = array();
        $dados = array_merge($preRegistro->toArray(), $preRegistroCpf->toArray(), $tempContabil);

        foreach($dados as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo . '_erro',
                'valor' => $faker->text(500)
            ])->assertStatus(302);    
    }

    /** @test */
    public function cannot_update_justificativa_with_wrong_input_acao()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();
        $preRegistro = factory('App\PreRegistro')->state('analise_inicial')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistro->id
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => $preRegistro->id
        ]);
        $tempContabil = array();
        foreach($preRegistro->contabil->toArray() as $key => $temp)
            if(!in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
                $tempContabil[$key . '_contabil'] = $temp;

        $dados = array_merge($preRegistro->toArray(), $preRegistroCpf->toArray(), $tempContabil, ['path' => 'nnn']);
        $pular = ['id', 'created_at', 'updated_at', 'deleted_at', 'pre_registro', 'pre_registro_id', 'contabil', 'contabil_id', 'user_externo_id',
        'idusuario', 'status', 'historico_contabil', 'registro_secundario', 'justificativa', 'confere_anexos'];

        foreach($dados as $campo => $valor)
            if(!in_array($campo, $pular))
                $this->post(route('preregistro.update.ajax', $preRegistro->id), [
                    'acao' => 'justificar_',
                    'campo' => $campo,
                    'valor' => $faker->text(500)
                ])->assertStatus(302);    
    }

    /** @test */
    public function cannot_update_justificativa_with_status_different_em_analise_or_analise_da_correcao()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();
        $preRegistro = factory('App\PreRegistro')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistro->id
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->create([
            'pre_registro_id' => $preRegistro->id
        ]);
        $tempContabil = array();
        foreach($preRegistro->contabil->toArray() as $key => $temp)
            if(!in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
                $tempContabil[$key . '_contabil'] = $temp;

        $dados = array_merge($preRegistro->toArray(), $preRegistroCpf->toArray(), $tempContabil, ['path' => 'nnn']);
        $pular = ['id', 'created_at', 'updated_at', 'deleted_at', 'pre_registro', 'pre_registro_id', 'contabil', 'contabil_id', 'user_externo_id',
        'idusuario', 'status', 'historico_contabil', 'registro_secundario', 'justificativa', 'confere_anexos'];

        $allStatus = array_merge(PreRegistro::getStatus(), [null]);
        foreach($allStatus as $status)
        {
            $preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                foreach($dados as $campo => $valor)
                    if(!in_array($campo, $pular))
                        $this->post(route('preregistro.update.ajax', $preRegistro->id), [
                            'acao' => 'justificar',
                            'campo' => $campo,
                            'valor' => $faker->text(500)
                        ])->assertStatus(401);    
        }
    }

    /** @test */
    public function can_save_inputs()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
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
    public function can_clean_inputs_saved_after_update()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
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
    public function cannot_save_inputs_with_wrong_action()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $campos = ['registro_secundario' => '000011234'];

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'editar_',
                'campo' => $campo,
                'valor' => $valor
            ])->assertStatus(302);    
    }

    /** @test */
    public function cannot_save_inputs_with_wrong_field()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $campos = ['registro_secundario' => '000011234'];

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo . '-',
                'valor' => $valor
            ])->assertStatus(302);    
    }

    /** @test */
    public function can_check_anexos()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $tipos = $anexo->first()->getObrigatoriosPreRegistro();
        $arrayAnexos = array();
        foreach($tipos as $tipo)
        {
            $arrayAnexos[$tipo] = "OK";
            $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
                'acao' => 'conferir',
                'campo' => 'confere_anexos[]',
                'valor' => $tipo
            ])->assertStatus(200);    
        }
            
        $this->assertDatabaseHas('pre_registros', [
            'confere_anexos' => json_encode($arrayAnexos, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function cannot_check_reservista_if_sexo_not_M()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
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
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
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
    public function can_check_anexos_with_wrong_action()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $tipos = $anexo->first()->getObrigatoriosPreRegistro();
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
    public function can_check_anexos_with_wrong_value()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $tipos = $anexo->first()->getObrigatoriosPreRegistro();
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
    public function can_check_anexos_with_wrong_field()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $tipos = $anexo->first()->getObrigatoriosPreRegistro();
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

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CPF VIA SUBMIT - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function can_update_status_enviar_para_correcao()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $arrayAnexos = array();
        foreach($anexo->first()->getObrigatoriosPreRegistro() as $tipo)
            $arrayAnexos[$tipo] = "OK";
        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['confere_anexos' => $final]);

        $this->put(route('preregistro.update.enviar.correcao', $preRegistroCpf->pre_registro_id))
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('enviado para correção')
        ->assertSeeText('sucesso');
    }

    /** @test */
    public function can_update_status_enviar_para_correcao_without_confere_anexos()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);

        $this->put(route('preregistro.update.enviar.correcao', $preRegistroCpf->pre_registro_id))
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('enviado para correção')
        ->assertSeeText('sucesso');
    }

    /** @test */
    public function cannot_update_status_enviar_para_correcao_without_justificativa()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $preRegistroCpf->preRegistro->update(['justificativa' => null]);

        $this->put(route('preregistro.update.enviar.correcao', $preRegistroCpf->pre_registro_id))
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('não possui justificativa(s)');
    }

    /** @test */
    public function cannot_update_status_enviar_para_correcao_only_key_negado()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $preRegistroCpf->preRegistro->update(['justificativa' => '{"negado":"teste"}']);

        $this->put(route('preregistro.update.enviar.correcao', $preRegistroCpf->pre_registro_id))
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('não possui justificativa(s)');
    }

    /** @test */
    public function cannot_update_status_enviar_para_correcao_with_status_different_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO];
        $all_status = array_merge(PreRegistro::getStatus(), [null]);
        foreach($all_status as $status)
            if(!in_array($status, $canUpdate))
            {
                $preRegistroCpf->preRegistro->update(['status' => $status]);
                $this->put(route('preregistro.update.enviar.correcao', $preRegistroCpf->pre_registro_id))
                ->assertRedirect(route('preregistro.index'));
                $this->get(route('preregistro.index'))
                ->assertSeeText('não possui o status necessário para ser enviado para correção');
            }
    }

    /** @test */
    public function can_update_status_enviar_para_correcao_with_status_analise_inicial_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach($canUpdate as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            $this->put(route('preregistro.update.enviar.correcao', $preRegistroCpf->pre_registro_id))
            ->assertRedirect(route('preregistro.index'));
            $this->get(route('preregistro.index'))
            ->assertSeeText('enviado para correção')
            ->assertSeeText('sucesso');
        }
    }

    /** @test */
    public function can_update_status_negado()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
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

        $this->put(route('preregistro.update.negado', $preRegistroCpf->pre_registro_id))
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('Negado')
        ->assertSeeText('sucesso');
    }

    /** @test */
    public function can_update_status_negado_without_confere_anexos()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
        ])->assertStatus(200); 

        $this->put(route('preregistro.update.negado', $preRegistroCpf->pre_registro_id))
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('Negado')
        ->assertSeeText('sucesso');
    }

    /** @test */
    public function cannot_update_status_negado_without_justificativa_negado()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $arrayAnexos = array();
        $tipos = $anexo->first()->getOpcoesPreRegistro();
        foreach($tipos as $tipo)
            $arrayAnexos[$tipo] = "OK";
        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

        $this->put(route('preregistro.update.negado', $preRegistroCpf->preRegistro->id))
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('Negado')
        ->assertSeeText('não possui justificativa(s)');
    }

    /** @test */
    public function cannot_update_status_negado_with_others_justificativa_and_without_negado()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);

        $this->put(route('preregistro.update.negado', $preRegistroCpf->preRegistro->id))
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('Negado')
        ->assertSeeText('não possui justificativa(s)');
    }

    /** @test */
    public function can_update_status_negado_with_others_justificativa_and_negado()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
        ])->assertStatus(200); 

        $this->put(route('preregistro.update.negado', $preRegistroCpf->preRegistro->id))
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('Negado')
        ->assertSeeText('sucesso');
    }

    /** @test */
    public function cannot_update_status_negado_with_status_different_analise_inicial_or_analise_da_correcao()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
        ])->assertStatus(200); 

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO];
        $all_status = array_merge(PreRegistro::getStatus(), [null]);
        foreach($all_status as $status)
            if(!in_array($status, $canUpdate))
            {
                $preRegistroCpf->preRegistro->update(['status' => $status]);
                $this->put(route('preregistro.update.negado', $preRegistroCpf->pre_registro_id))
                ->assertRedirect(route('preregistro.index'));
                $this->get(route('preregistro.index'))
                ->assertSee('<i class="icon fa fa-ban"></i>');
            }
    }

    /** @test */
    public function can_update_status_negado_with_status_analise_inicial_or_analise_da_correcao()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);

        $this->post(route('preregistro.update.ajax', $preRegistroCpf->pre_registro_id), [
            'acao' => 'justificar',
            'campo' => 'negado',
            'valor' => $faker->text(500)
        ])->assertStatus(200); 

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO];
        foreach($canUpdate as $status)
        {
            $preRegistroCpf->preRegistro->update(['status' => $status]);
            $this->put(route('preregistro.update.negado', $preRegistroCpf->pre_registro_id))
            ->assertRedirect(route('preregistro.index'));
            $this->get(route('preregistro.index'))
            ->assertSeeText('negado')
            ->assertSeeText('sucesso');
        }
    }

    /** @test */
    public function can_update_status_aprovado()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $arrayAnexos = array();
        foreach($anexo->first()->getObrigatoriosPreRegistro() as $tipo)
            $arrayAnexos[$tipo] = "OK";
        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

        $this->put(route('preregistro.update.aprovado', $preRegistroCpf->pre_registro_id))
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('aprovado')
        ->assertSeeText('sucesso');
    }

    /** @test */
    public function cannot_update_status_aprovado_without_confere_anexos()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);

        $this->put(route('preregistro.update.aprovado', $preRegistroCpf->pre_registro_id))
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('faltou anexos');
    }

    /** @test */
    public function cannot_update_status_aprovado_with_justificativa()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('justificado')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $arrayAnexos = array();
        $tipos = $anexo->first()->getOpcoesPreRegistro();
        foreach($tipos as $tipo)
            $arrayAnexos[$tipo] = "OK";
        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL, 'confere_anexos' => $final]);

        $this->put(route('preregistro.update.aprovado', $preRegistroCpf->preRegistro->id))
        ->assertRedirect(route('preregistro.index'));

        $this->get(route('preregistro.index'))
        ->assertSeeText('possui justificativa(s)');
    }

    /** @test */
    public function cannot_update_status_aprovado_with_status_different_analise_inicial_or_analise_da_correcao()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
        $arrayAnexos = array();
        $tipos = $anexo->first()->getOpcoesPreRegistro();
        foreach($tipos as $tipo)
            $arrayAnexos[$tipo] = "OK";
        $final = json_encode($arrayAnexos, JSON_FORCE_OBJECT);
        $preRegistroCpf->preRegistro->update(['confere_anexos' => $final]);

        $canUpdate = [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO];
        $all_status = array_merge(PreRegistro::getStatus(), [null]);
        foreach($all_status as $status)
            if(!in_array($status, $canUpdate))
            {
                $preRegistroCpf->preRegistro->update(['status' => $status]);
                $this->put(route('preregistro.update.aprovado', $preRegistroCpf->pre_registro_id))
                ->assertRedirect(route('preregistro.index'));
                $this->get(route('preregistro.index'))
                ->assertSee('<i class="icon fa fa-ban"></i>');
            }
    }

    /** @test */
    public function can_update_status_aprovado_with_status_analise_inicial_or_analise_da_correcao()
    {
        $faker = \Faker\Factory::create();
        $admin = $this->signInAsAdmin();
        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $anexo = factory('App\Anexo')->state('pre_registro')->create([
            'pre_registro_id' => $preRegistroCpf->pre_registro_id
        ]);
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
            $this->put(route('preregistro.update.aprovado', $preRegistroCpf->pre_registro_id))
            ->assertRedirect(route('preregistro.index'));
            $this->get(route('preregistro.index'))
            ->assertSeeText('aprovado')
            ->assertSeeText('sucesso');
        }
    }
}
