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
            'estado_civil' => null,
            'naturalidade' => null,
            'nacionalidade' => null,
            'nome_mae' => null,
            'tipo_identidade' => null,
            'identidade' => null,
            'orgao_emissor' => null,
            'dt_expedicao' => null,
            'path' => null
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
        $this->assertDatabaseHas('pre_registros', $preRegistro);

        foreach($preRegistroCpf as $key => $value)
            $preRegistroCpf[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf);

        foreach($contabil as $key => $value)
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

    // /** @test */
    // public function cannot_submit_pre_registro_with_nome_mae_with_numbers()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
    //         'pre_registro_id' => $preRegistro['id'],
    //         'nome_mae' => 'N0me Mãe'
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('nome_mae');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_nome_pai_more_than_191_chars()
    // {
    //     $faker = \Faker\Factory::create();
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
    //         'pre_registro_id' => $preRegistro['id'],
    //         'nome_pai' => $faker->sentence(400)
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('nome_pai');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_nome_pai_with_numbers()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
    //         'pre_registro_id' => $preRegistro['id'],
    //         'nome_pai' => 'N0me Pai'
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('nome_pai');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_without_identidade()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
    //         'pre_registro_id' => $preRegistro['id'],
    //         'identidade' => ''
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('identidade');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_identidade_more_than_20_chars()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
    //         'pre_registro_id' => $preRegistro['id'],
    //         'identidade' => '012345678901234567890'
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('identidade');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_without_orgao_emissor()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
    //         'pre_registro_id' => $preRegistro['id'],
    //         'orgao_emissor' => ''
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('orgao_emissor');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_orgao_emissor_more_than_191_chars()
    // {
    //     $faker = \Faker\Factory::create();
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
    //         'pre_registro_id' => $preRegistro['id'],
    //         'orgao_emissor' => $faker->sentence(400)
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('orgao_emissor');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_without_dt_expedicao()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
    //         'pre_registro_id' => $preRegistro['id'],
    //         'dt_expedicao' => ''
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('dt_expedicao');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_dt_expedicao_without_date_type()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
    //         'pre_registro_id' => $preRegistro['id'],
    //         'dt_expedicao' => 'texto'
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('dt_expedicao');
    // }

    // /** @test */
    // public function cannot_submit_pre_registro_with_dt_expedicao_after_today()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
    //         'pre_registro_id' => $preRegistro['id'],
    //         'dt_expedicao' => Carbon::today()->addDay()->format('Y-m-d')
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ])->assertOk();
        
    //     $this->put(route('externo.inserir.preregistro'), $dados)
    //     ->assertSessionHasErrors('dt_expedicao');
    // }

    // /** @test */
    // public function log_is_generated_when_form_cpf_is_submitted()
    // {
    //     Storage::fake('local');
    //     $externo = $this->signInAsUserExterno();

    //     $preRegistro = factory('App\PreRegistro')->raw([
    //         'id' => 1,
    //         'user_externo_id' => $externo->id,
    //         'contabil_id' => null,
    //         'idusuario' => null,
    //     ]);
    //     $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
    //         'pre_registro_id' => $preRegistro['id']
    //     ]);

    //     $dados = array_merge($preRegistro, $preRegistroCpf);
        
    //     $this->get(route('externo.inserir.preregistro.view'));     
        
    //     $this->post(route('externo.inserir.preregistro.ajax'), [
    //         'classe' => 'anexos',
    //         'campo' => 'path',
    //         'valor' => UploadedFile::fake()->create('random.pdf')
    //     ]);
        
    //     $this->put(route('externo.inserir.preregistro'), $dados);

    //     $pr = PreRegistro::first();

    //     $log = tailCustom(storage_path($this->pathLogExterno()));
    //     $this->assertStringContainsString('Usuário Externo com cpf: ' . $pr->userExterno->cpf_cnpj, $log);
    //     $this->assertStringContainsString(', enviou para análise incial a solicitação de registro com a id: ' . $pr->id, $log);
    // }
}
