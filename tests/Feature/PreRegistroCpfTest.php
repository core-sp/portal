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
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $preRegistro = $externo->load('preRegistro')->preRegistro;

        $this->assertDatabaseHas('pre_registros', [
            'id' => $preRegistro->id,
        ]);

        $this->assertDatabaseHas('pre_registros_cpf', [
            'id' => $preRegistro->pessoaFisica->id,
        ]);
    }

    /** @test */
    public function log_is_generated_when_form_cpf_is_created()
    {
        $externo = $this->signInAsUserExterno();
        
        $this->get(route('externo.inserir.preregistro.view'));     

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

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();
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

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $preRegistroCpf = [
            'nome_social' => $faker->sentence(400),
            'estado_civil' => $faker->sentence(400),
            'naturalidade' => $faker->sentence(400),
            'nacionalidade' => $faker->sentence(400),
            'nome_mae' => $faker->sentence(400),
            'nome_pai' => $faker->sentence(400),
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
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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
    public function view_message_errors_when_submit_with_dados_gerais_without_contabil()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'));

        $dados = [
            'registro_secundario' => null,
            'ramo_atividade' => 'Teste qualquer',
            'segmento' => segmentos()[0],
            'idregional' => factory('App\Regional')->create()->idregional,
            'nome_social' => null,
            'sexo' => 'M',
            'dt_nascimento' => '1988-02-20',
            'estado_civil' => estados_civis()[0],
            'nacionalidade' => nacionalidades()[18],
            'naturalidade' => estados()['SP'],
            'nome_mae' => 'Teste mãe',
            'nome_pai' => '',
            'identidade' => '11.111.111-1',
            'orgao_emissor' => 'SSP',
            'dt_expedicao' => '2022-02-21',   
        ];
        $this->put(route('externo.inserir.preregistro'), $dados)->assertStatus(302);
        $this->get(route('externo.inserir.preregistro.view'))
        ->assertSeeText('Foi encontrado erro em: ')
        ->assertDontSeeText('Contabilidade *')
        ->assertDontSeeText('Dados Gerais *')
        ->assertSeeText('Endereço *')
        ->assertDontSeeText('Contato / RT *')
        ->assertSeeText('Canal de Relacionamento *')
        ->assertSeeText('Anexos *');
    }

    /** @test */
    public function view_message_errors_when_submit_with_endereco_without_contabil()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'));

        $dados = [
            'cep' => '04444-000',
            'bairro' => 'Bairro Teste',
            'logradouro' => 'Rua teste',
            'numero' => 23,
            'complemento' => null,
            'cidade' => 'São Paulo',
            'uf' => 'SP',
        ];
        $this->put(route('externo.inserir.preregistro'), $dados)->assertStatus(302);
        $this->get(route('externo.inserir.preregistro.view'))
        ->assertSeeText('Foi encontrado erro em: ')
        ->assertDontSeeText('Contabilidade *')
        ->assertSeeText('Dados Gerais *')
        ->assertDontSeeText('Endereço *')
        ->assertDontSeeText('Contato / RT *')
        ->assertSeeText('Canal de Relacionamento *')
        ->assertSeeText('Anexos *');
    }

    /** @test */
    public function view_message_errors_when_submit_with_canal_without_contabil()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'));

        $dados = [
            'telefone' => '(11) 99999-5555',
            'tipo_telefone' => tipos_contatos()[1]
        ];
        $this->put(route('externo.inserir.preregistro'), $dados)->assertStatus(302);
        $this->get(route('externo.inserir.preregistro.view'))
        ->assertSeeText('Foi encontrado erro em: ')
        ->assertDontSeeText('Contabilidade *')
        ->assertSeeText('Dados Gerais *')
        ->assertSeeText('Endereço *')
        ->assertDontSeeText('Contato / RT *')
        ->assertDontSeeText('Canal de Relacionamento *')
        ->assertSeeText('Anexos *');
    }

    /** @test */
    public function can_submit_pre_registro_cpf()
    {
        Mail::fake();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => 1,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);

        $contabil = factory('App\Contabil')->raw();
        $temp = array();
        foreach($contabil as $key => $value)
            $temp[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $temp);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();        

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

        Mail::assertQueued(PreRegistroMail::class);

        $pr = $externo->load('preRegistro')->preRegistro;

        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';';
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';';
        $preRegistro['status'] = $pr::STATUS_ANALISE_INICIAL;

        $this->assertDatabaseHas('pre_registros', $preRegistro);
        $this->assertDatabaseHas('pre_registros_cpf', $preRegistroCpf);
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

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => 1,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nacionalidade' => nacionalidades()[5],
            'naturalidade' => null
        ]);

        $contabil = factory('App\Contabil')->raw();
        $temp = array();
        foreach($contabil as $key => $value)
            $temp[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $temp);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();        

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

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
        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => $preRegistroCpf_2->pre_registro_id + 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => $preRegistroCpf_2->preRegistro->contabil_id + 1,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);

        $contabil = factory('App\Contabil')->raw([
            'cnpj' => '46217816000172'
        ]);
        $temp = array();
        foreach($contabil as $key => $value)
            $temp[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $temp);
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();        
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
        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => $preRegistroCpf_2->pre_registro_id + 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => $preRegistroCpf_2->preRegistro->contabil_id,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);

        $contabil = factory('App\Contabil')->raw();
        $temp = array();
        foreach($contabil as $key => $value)
            $temp[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $temp);
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();        
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

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();        

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

        $pr = $externo->load('preRegistro')->preRegistro;

        $preRegistro['tipo_telefone'] = $preRegistro['tipo_telefone'] . ';';
        $preRegistro['telefone'] = $preRegistro['telefone'] . ';';
        $preRegistro['status'] = $pr::STATUS_ANALISE_INICIAL;

        $this->assertDatabaseHas('pre_registros', $preRegistro);
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
            'ramo_atividade' => '',
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
            'identidade' => '',
            'orgao_emissor' => '',
            'dt_expedicao' => '',
            'path' => '',
        ];
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors([
            'ramo_atividade',
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
            'identidade',
            'orgao_emissor',
            'dt_expedicao',
            'path',
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

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nome_social' => $faker->sentence(400)
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_social_with_numbers()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nome_social' => 'N0me Social'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_social');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_sexo()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'sexo' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('sexo');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_sexo_wrong_value()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'sexo' => 'N'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('sexo');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_sexo_more_than_1_char()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'sexo' => 'MM'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('sexo');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_dt_nascimento()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'dt_nascimento' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_without_date_type()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'dt_nascimento' => 'texto'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_dt_nascimento_under_18_years_old()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'dt_nascimento' => Carbon::today()->subYears(17)->format('Y-m-d')
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_estado_civil_wrong_value()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'estado_civil' => 'Qualquer Um'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('estado_civil');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_nacionalidade()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nacionalidade' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nacionalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nacionalidade_with_wrong_value()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nacionalidade' => 'Qualquer Um'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nacionalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_naturalidade_when_nacionalidade_is_option_brasileiro()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'naturalidade' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('naturalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_naturalidade_with_wrong_value_if_nacionalidade_is_option_brasileiro()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'naturalidade' => 'Qualquer Um'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('naturalidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_nome_mae()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nome_mae' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nome_mae' => $faker->sentence(400)
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_nome_mae_with_numbers()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nome_mae' => 'N0me Mãe'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
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

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nome_pai' => $faker->sentence(400)
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
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

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'nome_pai' => 'N0me Pai'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_pai');
    }

    /** @test */
    public function cannot_submit_pre_registro_without_identidade()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'identidade' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('identidade');
    }

    /** @test */
    public function cannot_submit_pre_registro_with_identidade_more_than_20_chars()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'identidade' => '012345678901234567890'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
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

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'orgao_emissor' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
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

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'orgao_emissor' => $faker->sentence(400)
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
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

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'dt_expedicao' => ''
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
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

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'dt_expedicao' => 'texto'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
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

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'dt_expedicao' => Carbon::today()->addDay()->format('Y-m-d')
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
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

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf);
        
        $this->get(route('externo.inserir.preregistro.view'));     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ]);
        
        $this->put(route('externo.inserir.preregistro'), $dados);

        $pr = PreRegistro::first();

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $this->assertStringContainsString('Usuário Externo com cpf: ' . $pr->userExterno->cpf_cnpj, $log);
        $this->assertStringContainsString(', enviou para análise incial a solicitação de registro com a id: ' . $pr->id, $log);
    }
}
