<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\PreRegistro;
use App\Contabil;
use Illuminate\Support\Arr;

class ContabilTest extends TestCase
{
    use RefreshDatabase;

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
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => '78087976000130'
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
        $this->assertNotEquals($atual, PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO CONTABIL VIA AJAX - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function can_update_table_contabeis_by_ajax()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => $key.'_contabil',
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('contabeis', $contabil);

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function can_update_table_contabeis_by_ajax_with_upperCase()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $contabil = factory('App\Contabil')->states('low')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => $key.'_contabil',
                'valor' => $value
            ])->assertStatus(200);
        
        foreach($contabil as $key => $value)
            if(isset($value) && ($key != 'email'))
                $contabil[$key] = mb_strtoupper($value, 'UTF-8');

        $this->assertDatabaseHas('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function can_update_table_contabeis_by_ajax_when_exists_others_pre_registros()
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
        $contabil = factory('App\Contabil')->raw([
            'cnpj' => '46217816000172'
        ]);
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => $key.'_contabil',
                'valor' => $value
            ])->assertStatus(200);

        $this->assertDatabaseHas('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', $preRegistroCpf_1->preRegistro->toArray());
        $this->assertDatabaseHas('pre_registros', $preRegistroCpf_2->preRegistro->toArray());
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => $externo->load('preRegistro')->preRegistro->contabil_id
        ]);
    }

    /** @test */
    public function can_update_table_contabeis_by_ajax_when_exists_others_pre_registros_with_same_contabil()
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
        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => $key.'_contabil',
                'valor' => $value
            ])->assertStatus(200);

        $this->assertDatabaseHas('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', $preRegistroCpf_1->preRegistro->attributesToArray());
        $this->assertDatabaseHas('pre_registros', $preRegistroCpf_2->preRegistro->attributesToArray());
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => $externo->load('preRegistro')->preRegistro->contabil_id
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => $key.'_contabil',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil_erro',
                'campo' => $key.'_contabil',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_with_input_type_text_more_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => factory('App\Contabil')->raw()['cnpj']
        ]);

        $contabil = [
            'nome' => $faker->sentence(400),
            'email' => $faker->sentence(400),
            'nome_contato' => $faker->sentence(400),
            'telefone' => $faker->sentence(400),
        ];

        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => $key . '_contabil',
                'valor' => $value
            ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_with_cnpj_wrong()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => factory('App\Contabil')->raw()['cnpj'] . '4'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('contabeis', [
            'cnpj' => factory('App\Contabil')->raw()['cnpj'] . '4'
        ]);
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_without_relationship()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            if($key != 'cnpj')
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'contabil',
                    'campo' => $key.'_contabil',
                    'valor' => $value
                ])->assertOk();
        
        $this->assertDatabaseMissing('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_when_remove_relationship()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => $key.'_contabil',
                'valor' => $value
            ])->assertOk();
        
        $this->assertDatabaseHas('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil->id
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => ''
        ])->assertOk();

        $this->assertDatabaseHas('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => null
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'nome_contabil',
            'valor' => 'Novo Teste'
        ])->assertOk();

        $this->assertDatabaseHas('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => null
        ]);
    }

    /** @test */
    public function get_contabil_by_ajax_when_exists()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $contabil = factory('App\Contabil')->create();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => $contabil->cnpj
        ])->assertJsonFragment($contabil->toArray());
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_when_clean_inputs()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $contabil = factory('App\Contabil')->create();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'cnpj_contabil',
            'valor' => $contabil->cnpj
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => $contabil->id
        ]);

        $dados = $contabil->makeHidden(['id', 'created_at', 'updated_at', 'deleted_at'])->toArray();
        foreach($dados as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => $key . '_contabil',
                'valor' => ''
            ])->assertOk();

        $this->assertDatabaseHas('contabeis', $contabil->toArray());
        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno();

        $contabil = factory('App\Contabil')->create();
        $preRegistro = factory('App\PreRegistro')->create([
            'user_externo_id' => $externo->id,
            'contabil_id' => $contabil->id
        ]);

        $contabilAjax = $contabil->makeHidden(['id', 'created_at', 'updated_at', 'deleted_at'])->toArray();        
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                foreach($contabilAjax as $key => $value)
                    $this->post(route('externo.inserir.preregistro.ajax'), [
                        'classe' => 'contabil',
                        'campo' => $key . '_contabil',
                        'valor' => ''
                    ])->assertStatus(401);
        }
    }

    /** @test */
    public function can_update_table_contabeis_by_ajax_with_status_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno();

        $contabil = factory('App\Contabil')->create();
        $preRegistro = factory('App\PreRegistro')->create([
            'contabil_id' => $contabil->id
        ]);

        $contabilAjax = $contabil->makeHidden(['id', 'created_at', 'updated_at'])->toArray();        
        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->update(['status' => $status]);
            foreach($contabilAjax as $key => $value)
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'contabil',
                    'campo' => $key . '_contabil',
                    'valor' => ''
                ])->assertStatus(200);
        }
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO CONTABIL VIA SUBMIT - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function view_message_errors_when_submit_with_cnpj()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistroCpf = factory('App\PreRegistroCpf')->states('low')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('low')->create([
                'opcional_celular' => null
            ]),
        ]);

        $dados = [
            'cnpj_contabil' => '78087976000130',
            'nome_contabil' => null,
            'email_contabil' => null,
            'nome_contato_contabil' => null,
            'telefone_contabil' => null,
            'path' => null,
        ];

        $final = array_merge($dados, $preRegistroCpf->preRegistro->toArray(), $preRegistroCpf->toArray());
        $this->put(route('externo.verifica.inserir.preregistro'), $final)->assertStatus(302);

        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);
    }
    
    /** @test */
    public function can_submit_pre_registro_with_cnpj_contabil_exists()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $cont = $preRegistroCpf->contabil;
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => [UploadedFile::fake()->create('random.pdf')]
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertOk();

        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));

        foreach($cont as $key => $value)
            $cont[$key] = $key != 'email' ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('contabeis', $cont);
        $this->assertEquals(Contabil::count(), 1);
    }

    /** @test */
    public function cannot_submit_pre_registro_with_cnpj_contabil_wrong()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['cnpj_contabil'] = '01234567891023';

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cnpj_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_nome_contabil()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['nome_contabil'] = '';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contabil_less_than_5_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['nome_contabil'] = 'Nome';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contabil_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['nome_contabil'] = $faker->sentence(400);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_email_contabil()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['email_contabil'] = '';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('email_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_email_contabil_less_than_10_chars()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['email_contabil'] = 'tes@.com';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('email_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_email_contabil_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['email_contabil'] = $faker->sentence(400);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('email_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_email_wrong_value()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['email_contabil'] = 'teste@.com';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('email_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_nome_contato_contabil()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['nome_contato_contabil'] = '';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contato_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contato_contabil_less_than_5_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['nome_contato_contabil'] = 'Nome';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contato_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contato_contabil_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['nome_contato_contabil'] = $faker->sentence(400);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contato_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contato_contabil_with_numbers()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['nome_contato_contabil'] = 'N0me C0ntato';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
       
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contato_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_telefone_contabil()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['telefone_contabil'] = '';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_telefone_contabil_less_than_14_chars()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['telefone_contabil'] = '(11) 9888-862';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_telefone_contabil_more_than_15_chars()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('request')->make();
        $final = $preRegistroCpf->final;
        $prCpf = Arr::except($preRegistroCpf->toArray(), [
            'final','preRegistro','contabil'
        ]);
        $dados = array_merge($prCpf, $final);
        $pr = Arr::except($preRegistroCpf->preRegistro, [
            'idusuario','status','justificativa','confere_anexos','historico_contabil','historico_status','campos_espelho','campos_editados'
        ]);
        $dados['telefone_contabil'] = '(11) 98889-86265';
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        $anexo = factory('App\Anexo')->states('pre_registro')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone_contabil');
    }

    /** 
     * =======================================================================================================
     * TESTES PRÉ-REGISTRO CONTÁBIL - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function view_pre_registro_contabil()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->create();
        $preRegistroCpf->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);
        $contabil = $preRegistroCpf->preRegistro->contabil;
        
        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeText(formataCpfCnpj($contabil->cnpj))
        ->assertSeeText($contabil->nome)
        ->assertSeeText($contabil->nome_contato)
        ->assertSeeText($contabil->email)
        ->assertSeeText($contabil->telefone);
    }

    /** @test */
    public function view_text_justificado_contabil()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCpf = factory('App\PreRegistroCpf')->states('justificado')->create();
        $justificativas = $preRegistroCpf->preRegistro->getJustificativaArray();

        $this->get(route('preregistro.view', $preRegistroCpf->preRegistro->id))
        ->assertSeeText($justificativas['cnpj_contabil'])
        ->assertSeeText($justificativas['nome_contabil'])
        ->assertSeeText($justificativas['nome_contato_contabil'])
        ->assertSeeText($justificativas['email_contabil'])
        ->assertSeeText($justificativas['telefone_contabil']);
    }
}
