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

class PreRegistroCnpjTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_new_pre_registro_pj()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.preregistro.view'))->assertOk();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistro = $externo->load('preRegistro')->preRegistro;

        $this->assertDatabaseHas('pre_registros', [
            'id' => $preRegistro->id,
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'id' => $preRegistro->pessoaJuridica->id,
        ]);
    }

    /** @test */
    public function cannot_new_pre_registro_pj_without_check()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.preregistro.view'))->assertOk();
        $this->get(route('externo.inserir.preregistro.view'))
        ->assertRedirect(route('externo.preregistro.view'));
    }

    /** @test */
    public function log_is_generated_when_form_cnpj_is_created()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));     

        $pr = PreRegistro::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString('Usuário Externo com cnpj: ' . $pr->userExterno->cpf_cnpj, $log);
        $this->assertStringContainsString(', iniciou o processo de solicitação de registro com a id: ' . $pr->id, $log);
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CNPJ VIA AJAX - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        $endereco = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        unset($preRegistroCnpj['pre_registro_id']);
        unset($preRegistroCnpj['responsavel_tecnico_id']);
        
        foreach($preRegistroCnpj as $key => $value)
        {
            $temp = in_array($key, $endereco);
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $temp !== false ? $key.'_empresa' : $key,
                'valor' => $value
            ])->assertStatus(200);
        }
        
        $preRegistroCnpj['pre_registro_id'] = $externo->load('preRegistro')->preRegistro->id;
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_with_upperCase()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        $endereco = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        unset($preRegistroCnpj['pre_registro_id']);
        unset($preRegistroCnpj['responsavel_tecnico_id']);
        
        foreach($preRegistroCnpj as $key => $value)
        {
            $temp = in_array($key, $endereco);
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $temp !== false ? $key.'_empresa' : $key,
                'valor' => $value
            ])->assertStatus(200);
        }
        
        foreach($preRegistroCnpj as $key => $value)
            if(isset($value))
                $preRegistroCnpj[$key] = mb_strtoupper($value, 'UTF-8');

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_exists_others_pre_registros()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '67779004000190'
                ])
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '56821972000100'
                ])
            ])
        ]);

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id,
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'cpf' => '60923317058'
            ]),
        ]);
        
        $endereco = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        unset($preRegistroCnpj['pre_registro_id']);
        unset($preRegistroCnpj['responsavel_tecnico_id']);
        
        foreach($preRegistroCnpj as $key => $value)
        {
            $temp = in_array($key, $endereco);
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $temp !== false ? $key.'_empresa' : $key,
                'valor' => $value
            ])->assertStatus(200);
        }
        
        $preRegistroCnpj['pre_registro_id'] = $externo->load('preRegistro')->preRegistro->id;

        $pr_1 = $preRegistroCnpj_1->toArray();
        unset($pr_1['pre_registro']);
        $pr_2 = $preRegistroCnpj_2->toArray();
        unset($pr_2['pre_registro']);

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj);
        $this->assertDatabaseHas('pre_registros_cnpj', $pr_1);
        $this->assertDatabaseHas('pre_registros_cnpj', $pr_2);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        unset($preRegistroCnpj['pre_registro_id']);
        unset($preRegistroCnpj['responsavel_tecnico_id']);
        
        foreach($preRegistroCnpj as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $preRegistroCnpj);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        $endereco = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];

        unset($preRegistroCnpj['pre_registro_id']);
        unset($preRegistroCnpj['responsavel_tecnico_id']);
        
        foreach($preRegistroCnpj as $key => $value)
        {
            $temp = in_array($key, $endereco);
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => $temp !== false ? $key.'_empresa' : $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        }
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $preRegistroCnpj);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        $endereco = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        unset($preRegistroCnpj['pre_registro_id']);
        unset($preRegistroCnpj['responsavel_tecnico_id']);
        
        foreach($preRegistroCnpj as $key => $value)
        {
            $temp = in_array($key, $endereco);
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridicaErro',
                'campo' => $temp !== false ? $key.'_empresa' : $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        }
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $preRegistroCnpj);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);

        unset($preRegistroCnpj['pre_registro_id']);
        unset($preRegistroCnpj['responsavel_tecnico_id']);
        
        foreach($preRegistroCnpj as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $preRegistroCnpj);
    }

    /** @test */
    public function cannot_update_table_pre_registros_cnpj_by_ajax_with_input_type_text_more_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $preRegistroCnpj = [
            'razao_social' => $faker->sentence(400),
            'tipo_empresa' => $faker->sentence(400),
            'inscricao_municipal' => $faker->sentence(400),
            'inscricao_estadual' => $faker->sentence(400),
            'capital_social' => $faker->sentence(400),
            'logradouro' => $faker->sentence(400),
            'complemento' => $faker->sentence(400),
            'bairro' => $faker->sentence(400),
            'cidade' => $faker->sentence(400),
        ];

        $endereco = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        
        foreach($preRegistroCnpj as $key => $value)
        {
            $temp = in_array($key, $endereco);
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica',
                'campo' => $temp !== false ? $key.'_empresa' : $key,
                'valor' => $value
            ])->assertSessionHasErrors('valor');
        }
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $preRegistroCnpj);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cnpj_by_ajax_with_dt_inicio_atividade_after_today()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica',
            'campo' => 'dt_inicio_atividade',
            'valor' => Carbon::today()->addDay()->format('Y-m-d')
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'dt_inicio_atividade' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registro_cnpj_by_ajax_without_date_type()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica',
            'campo' => 'dt_inicio_atividade',
            'valor' => 'texto'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'dt_inicio_atividade' => null
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax_when_clean_inputs()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => $externo->id,
            ]),
        ]);

        $preRegistroCnpj = $preRegistro->toArray();
        $pular = ['id', 'pre_registro_id', 'updated_at', 'created_at', 'responsavel_tecnico_id', 'pre_registro'];
        
        foreach($preRegistroCnpj as $key => $value)
        {
            if(!in_array($key, $pular))
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'pessoaJuridica',
                    'campo' => $key,
                    'valor' => ''
                ])->assertStatus(200);
        }

        unset($preRegistroCnpj['pre_registro']);
        
        $this->assertDatabaseMissing('pre_registros_cnpj', $preRegistroCnpj);
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CNPJ VIA SUBMIT - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function view_message_errors_when_submit_pf()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

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
            'razao_social' => null,
            'tipo_empresa' => null,
            'dt_inicio_atividade' => null,
            'inscricao_municipal' => null,
            'inscricao_estadual' => null,
            'capital_social' => null,
            'cep_empresa' => null,
            'logradouro_empresa' => null,
            'numero_empresa' => null,
            'bairro_empresa' => null,
            'cidade_empresa' => null,
            'uf_empresa' => null,
            'cpf_rt' => null,
            'nome_rt' => null,
            'sexo_rt' => null,
            'dt_nascimento_rt' => null,
            'cep_rt' => null,
            'logradouro_rt' => null,
            'numero_rt' => null,
            'bairro_rt' => null,
            'cidade_rt' => null,
            'uf_rt' => null,
            'nome_mae_rt' => null,
            'tipo_identidade_rt' => null,
            'identidade_rt' => null,
            'orgao_emissor_rt' => null,
            'dt_expedicao_rt' => null,
            'path' => null,
            'pergunta' => null,
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
    public function view_message_errors_when_submit_when_checkEndEmpresa_on()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

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
            'razao_social' => null,
            'tipo_empresa' => null,
            'dt_inicio_atividade' => null,
            'inscricao_municipal' => null,
            'inscricao_estadual' => null,
            'capital_social' => null,
            'checkEndEmpresa' => 'on',
            'cpf_rt' => null,
            'nome_rt' => null,
            'sexo_rt' => null,
            'dt_nascimento_rt' => null,
            'cep_rt' => null,
            'logradouro_rt' => null,
            'numero_rt' => null,
            'bairro_rt' => null,
            'cidade_rt' => null,
            'uf_rt' => null,
            'nome_mae_rt' => null,
            'tipo_identidade_rt' => null,
            'identidade_rt' => null,
            'orgao_emissor_rt' => null,
            'dt_expedicao_rt' => null,
            'path' => null,
            'pergunta' => null,
        ];

        $this->put(route('externo.inserir.preregistro'), $dados)->assertStatus(302);

        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);

        $this->assertEquals(count($keys), count($dados) - 1);
    }

    /** @test */
    public function can_submit_pre_registro_cnpj()
    {
        Mail::fake();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => 1,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => 1
        ]);

        $contabil = factory('App\Contabil')->state('low')->raw();
        $temp = array();
        foreach($contabil as $key => $value)
            $temp[$key . '_contabil'] = $value;

        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $temp, $tempRT);
        
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

        foreach($preRegistroCnpj as $key => $value)
            $preRegistroCnpj[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj);

        foreach($contabil as $key => $value)
            if($key != 'email')
                $contabil[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('contabeis', $contabil);

        foreach($rt as $key => $value)
            $rt[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);

        $this->assertDatabaseHas('anexos', [
            'nome_original' => 'random.pdf'
        ]);

        Storage::disk('local')->assertExists($pr->anexos->first()->path);
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_with_checkEndEmpresa_on()
    {
        Mail::fake();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => 1,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => 1,
            'checkEndEmpresa' => 'on'
        ]);

        $contabil = factory('App\Contabil')->state('low')->raw();
        $temp = array();
        foreach($contabil as $key => $value)
            $temp[$key . '_contabil'] = $value;

        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = null : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $temp, $tempRT);
        
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

        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            if(in_array($key, $tempCnpj))
                $preRegistroCnpj[$key] = $preRegistro[$key];

        $pr = $externo->load('preRegistro')->preRegistro;
        $preRegistro['opcional_celular'] = ';';
        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';';
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';';
        unset($preRegistro['pergunta']);
        unset($preRegistroCnpj['checkEndEmpresa']);

        foreach($preRegistro as $key => $value)
            $preRegistro[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $preRegistro['status'] = $pr::STATUS_ANALISE_INICIAL;
        $this->assertDatabaseHas('pre_registros', $preRegistro);

        foreach($preRegistroCnpj as $key => $value)
            $preRegistroCnpj[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj);

        foreach($contabil as $key => $value)
            if($key != 'email')
                $contabil[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('contabeis', $contabil);

        foreach($rt as $key => $value)
            $rt[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);

        $this->assertDatabaseHas('anexos', [
            'nome_original' => 'random.pdf'
        ]);

        Storage::disk('local')->assertExists($pr->anexos->first()->path);
    }

    /** @test */
    public function can_submit_pre_registros_cnpj_by_ajax_when_exists_others_pre_registros()
    {
        Storage::fake('local');
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '67779004000190'
                ])
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '56821972000100'
                ])
            ])
        ]);

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => $preRegistroCnpj_2->pre_registro_id + 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => $preRegistroCnpj_2->preRegistro->contabil_id + 1,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id + 1,
        ]);

        $rt = factory('App\ResponsavelTecnico')->state('low')->raw([
            'cpf' => '60923317058'
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();        

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

        $pr_1 = $preRegistroCnpj_1->toArray();
        unset($pr_1['pre_registro']);
        $pr_2 = $preRegistroCnpj_2->toArray();
        unset($pr_2['pre_registro']);

        foreach($preRegistroCnpj as $key => $value)
            $preRegistroCnpj[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj);
        $this->assertDatabaseHas('pre_registros_cnpj', $pr_1);
        $this->assertDatabaseHas('pre_registros_cnpj', $pr_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());
    }

    /** @test */
    public function can_submit_pre_registros_cnpj_by_ajax_when_exists_others_pre_registros_with_same_contabil_and_rt()
    {
        Storage::fake('local');
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '67779004000190'
                ])
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '56821972000100'
                ])
            ])
        ]);

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => $preRegistroCnpj_2->pre_registro_id + 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => $preRegistroCnpj_2->preRegistro->contabil_id,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
        ]);

        $contabil = factory('App\Contabil')->state('low')->raw();
        $temp = array();
        foreach($contabil as $key => $value)
            $temp[$key . '_contabil'] = $value;

        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $temp, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();        

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));
        $preRegistroCnpj['pre_registro_id'] = $externo->load('preRegistro')->preRegistro->id;

        $pr_1 = $preRegistroCnpj_1->toArray();
        unset($pr_1['pre_registro']);
        $pr_2 = $preRegistroCnpj_2->toArray();
        unset($pr_2['pre_registro']);

        foreach($preRegistroCnpj as $key => $value)
            $preRegistroCnpj[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;

        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj);
        $this->assertDatabaseHas('pre_registros_cnpj', $pr_1);
        $this->assertDatabaseHas('pre_registros_cnpj', $pr_2);
        $this->assertDatabaseHas('pre_registros', $externo->load('preRegistro')->preRegistro->toArray());
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_without_optional_inputs()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => 1
        ]);

        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();        

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));
        
        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';';
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';';
        $preRegistro['opcional_celular'] = ';';
        unset($preRegistro['pergunta']);
        
        $pr = $externo->load('preRegistro')->preRegistro;

        foreach($preRegistro as $key => $value)
            $preRegistro[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $preRegistro['status'] = $pr::STATUS_ANALISE_INICIAL;
        $this->assertDatabaseHas('pre_registros', $preRegistro);

        foreach($preRegistroCnpj as $key => $value)
            $preRegistroCnpj[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj);

        foreach($rt as $key => $value)
            $rt[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : null;
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);

        $this->assertDatabaseHas('anexos', [
            'nome_original' => 'random.pdf'
        ]);

        Storage::disk('local')->assertExists($pr->anexos->first()->path);
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_required_inputs()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $dados = [
            'path' => '',
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
            'razao_social' => '',
            'capital_social' => '',
            'tipo_empresa' => '',
            'dt_inicio_atividade' => '',
            'inscricao_municipal' => '',
            'inscricao_estadual' => '',
            'cep_empresa' => '',
            'bairro_empresa' => '',
            'logradouro_empresa' => '',
            'numero_empresa' => '',
            'cidade_empresa' => '',
            'uf_empresa' => '',
            'nome_rt' => '',
            'sexo_rt' => '',
            'dt_nascimento_rt' => '',
            'cpf_rt' => '',
            'tipo_identidade_rt' => '',
            'identidade_rt' => '',
            'orgao_emissor_rt' => '',
            'dt_expedicao_rt' => '',
            'cep_rt' => '',
            'bairro_rt' => '',
            'logradouro_rt' => '',
            'numero_rt' => '',
            'cidade_rt' => '',
            'uf_rt' => '',
            'nome_mae_rt' => '',
        ];
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors([
            'path',
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
            'razao_social',
            'capital_social',
            'tipo_empresa',
            'dt_inicio_atividade',
            'inscricao_municipal',
            'inscricao_estadual',
            'cep_empresa',
            'bairro_empresa',
            'logradouro_empresa',
            'numero_empresa',
            'cidade_empresa',
            'uf_empresa',
            'nome_rt',
            'sexo_rt',
            'dt_nascimento_rt',
            'cpf_rt',
            'tipo_identidade_rt',
            'identidade_rt',
            'orgao_emissor_rt',
            'dt_expedicao_rt',
            'cep_rt',
            'bairro_rt',
            'logradouro_rt',
            'numero_rt',
            'cidade_rt',
            'uf_rt',
            'nome_mae_rt',
        ]);

        $pr = $externo->load('preRegistro')->preRegistro;

        $this->assertDatabaseHas('pre_registros', $pr->toArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $pr->pessoaJuridica->toArray());
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.pdf'
        ]);
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_razao_social()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'razao_social' => ''
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_razao_social_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'razao_social' => $faker->sentence(400)
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_razao_social_with_numbers()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'razao_social' => 'Razão S0cia1'
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('razao_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_capital_social()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'capital_social' => ''
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('capital_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_capital_social_more_than_16_chars()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'capital_social' => '12345678912345,00'
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('capital_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_capital_social_wrong_value()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'capital_social' => '12-34567891234500'
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('capital_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nire_more_than_20_chars()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'nire' => 'abc0123654789qwert012'
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nire');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_tipo_empresa()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'tipo_empresa' => ''
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_tipo_empresa_wrong_value()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'tipo_empresa' => 'Qualquer tipo'
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_dt_inicio_atividade()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'dt_inicio_atividade' => ''
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_inicio_atividade_without_date_type()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'dt_inicio_atividade' => 'texto'
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_inicio_atividade_after_today()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'dt_inicio_atividade' => Carbon::today()->addDay()->format('Y-m-d')
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_inicio_atividade');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_inscricao_municipal()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'inscricao_municipal' => ''
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('inscricao_municipal');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_inscricao_municipal_more_than_30_chars()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'inscricao_municipal' => '0123456789012345678901234567890'
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('inscricao_municipal');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_inscricao_estadual()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'inscricao_estadual' => ''
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('inscricao_estadual');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_inscricao_estadual_more_than_30_chars()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'inscricao_estadual' => '0123456789012345678901234567890'
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('inscricao_estadual');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_cep_empresa()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'cep' => ''
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cep_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cep_empresa_more_than_9_chars()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'cep' => '01234-5678'
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cep_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_bairro_empresa()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'bairro' => ''
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('bairro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_bairro_empresa_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'bairro' => $faker->sentence(400)
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('bairro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_logradouro_empresa()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'logradouro' => ''
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('logradouro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_logradouro_empresa_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'logradouro' => $faker->sentence(400)
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('logradouro_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_numero_empresa()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'numero' => ''
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('numero_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_numero_empresa_more_than_10_chars()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'numero' => '01qw2345678'
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('numero_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_complemento_empresa_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'complemento' => $faker->sentence(400)
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('complemento_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_cidade_empresa()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'cidade' => ''
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cidade_empresa_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'cidade' => $faker->sentence(400)
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_cidade_empresa_with_numbers()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'cidade' => 'Cidade com num3eros'
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_without_uf_empresa()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'uf' => ''
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('uf_empresa');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_if_has_checkEndEmpresa_off_and_with_uf_empresa_with_wrong_value()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
            'uf' => 'TT'
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('uf_empresa');
    }

    /** @test */
    public function log_is_generated_when_form_cnpj_is_submitted()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null,
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->state('low')->raw();
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']));     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ]);
        
        $this->put(route('externo.inserir.preregistro'), $dados);

        $pr = PreRegistro::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString('Usuário Externo com cnpj: ' . $pr->userExterno->cpf_cnpj, $log);
        $this->assertStringContainsString(', enviou para análise incial a solicitação de registro com a id: ' . $pr->id, $log);
    }
}
