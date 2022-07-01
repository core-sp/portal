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

class ContabilTest extends TestCase
{
    use RefreshDatabase;

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

        $contabil = factory('App\Contabil')->state('low')->raw();
        
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
        
        $pr_1 = $preRegistroCpf_1->preRegistro->toArray();
        $pr_2 = $preRegistroCpf_2->preRegistro->toArray();

        $this->assertDatabaseHas('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', $pr_1);
        $this->assertDatabaseHas('pre_registros', $pr_2);
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
        
        $pr_1 = $preRegistroCpf_1->preRegistro->toArray();
        $pr_2 = $preRegistroCpf_2->preRegistro->toArray();
        unset($pr_1['contabil']);
        unset($pr_2['contabil']);

        $this->assertDatabaseHas('contabeis', $contabil);
        $this->assertDatabaseHas('pre_registros', $pr_1);
        $this->assertDatabaseHas('pre_registros', $pr_2);
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
                'campo' => $key,
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
        {
            if($key != 'cnpj')
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'contabil',
                    'campo' => $key.'_contabil',
                    'valor' => $value
                ])->assertOk();
        }
        
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

        $dados = $contabil->toArray();
        unset($dados['created_at']);
        unset($dados['updated_at']);
        unset($dados['deleted_at']);
        unset($dados['id']);

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

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO CONTABIL VIA SUBMIT - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function view_message_errors_when_submit_with_cnpj()
    {
        $externo = $this->signInAsUserExterno();
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->create([
            'pre_registro_id' => factory('App\PreRegistro')->state('low')->create([
                'user_externo_id' => $externo->id,
                'opcional_celular' => null
            ]),
        ]);
        $preRegistro = $preRegistroCpf->preRegistro->toArray();

        $dados = [
            'cnpj_contabil' => '78087976000130',
            'nome_contabil' => null,
            'email_contabil' => null,
            'nome_contato_contabil' => null,
            'telefone_contabil' => null,
            'path' => null,
            'pergunta' => 'teste'
        ];

        $final = array_merge($dados, $preRegistro, $preRegistroCpf->toArray());
        $this->put(route('externo.verifica.inserir.preregistro'), $final)->assertStatus(302);

        $errors = session('errors');
        $keys = array();
        foreach($errors->messages() as $key => $value)
            array_push($keys, '<button class="btn btn-sm btn-link erroPreRegistro" value="' . $key . '">');

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Foram encontrados ' . count($errors->messages()) . ' erros:')
        ->assertSeeInOrder($keys);

        $this->assertEquals(count($keys), count($dados) - 2);
    }
    
    /** @test */
    public function can_submit_pre_registro_with_cnpj_contabil_exists()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno();
        
        $contabil = factory('App\Contabil')->create();

        $preRegistro = factory('App\PreRegistro')->state('low')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null,
            'pergunta' => 'teste da pergunta',
            'opcional_celular' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);

        foreach($contabil->toArray() as $key => $value)
            $dados[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $dados);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)->assertOk();

        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('contabeis', $contabil->toArray());
        $this->assertEquals(Contabil::count(), 1);
    }

    /** @test */
    public function cannot_submit_pre_registro_with_cnpj_contabil_wrong()
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
            'pre_registro_id' => $preRegistro['id']
        ]);
        $contabil = factory('App\Contabil')->raw([
            'cnpj' => '01234567891023',
        ]);

        foreach($contabil as $key => $value)
            $contabil[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $contabil);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cnpj_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_nome_contabil()
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
            'pre_registro_id' => $preRegistro['id']
        ]);
        $contabil = factory('App\Contabil')->raw([
            'nome' => '',
        ]);

        foreach($contabil as $key => $value)
            $contabil[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $contabil);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contabil_more_than_191_chars()
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
            'opcional_celular' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);

        $contabil = factory('App\Contabil')->raw([
            'nome' => $faker->sentence(400),
        ]);

        foreach($contabil as $key => $value)
            $contabil[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $contabil);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_email_contabil()
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
            'pre_registro_id' => $preRegistro['id']
        ]);
        $contabil = factory('App\Contabil')->raw([
            'email' => '',
        ]);

        foreach($contabil as $key => $value)
            $contabil[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $contabil);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('email_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_email_contabil_more_than_191_chars()
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
            'opcional_celular' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $contabil = factory('App\Contabil')->raw([
            'email' => $faker->sentence(400),
        ]);

        foreach($contabil as $key => $value)
            $contabil[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $contabil);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('email_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_email_wrong_value()
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
            'pre_registro_id' => $preRegistro['id']
        ]);
        $contabil = factory('App\Contabil')->raw([
            'email' => 'qualquercoisa.com',
        ]);

        foreach($contabil as $key => $value)
            $contabil[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $contabil);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('email_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_nome_contato_contabil()
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
            'pre_registro_id' => $preRegistro['id']
        ]);
        $contabil = factory('App\Contabil')->raw([
            'nome_contato' => '',
        ]);

        foreach($contabil as $key => $value)
            $contabil[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $contabil);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contato_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contato_contabil_more_than_191_chars()
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
            'opcional_celular' => null
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->state('low')->raw([
            'pre_registro_id' => $preRegistro['id']
        ]);
        $contabil = factory('App\Contabil')->raw([
            'nome_contato' => $faker->sentence(400),
        ]);

        foreach($contabil as $key => $value)
            $contabil[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $contabil);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contato_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_nome_contato_contabil_with_numbers()
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
            'pre_registro_id' => $preRegistro['id']
        ]);
        $contabil = factory('App\Contabil')->raw([
            'nome_contato' => 'Teste do n0me com números',
        ]);

        foreach($contabil as $key => $value)
            $contabil[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $contabil);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_contato_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_without_telefone_contabil()
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
            'pre_registro_id' => $preRegistro['id']
        ]);
        $contabil = factory('App\Contabil')->raw([
            'telefone' => '',
        ]);

        foreach($contabil as $key => $value)
            $contabil[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $contabil);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone_contabil');
    }

    /** @test */
    public function cannot_submit_pre_registro_if_has_cnpj_contabil_and_with_telefone_contabil_more_than_20_chars_and_value_wrong()
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
            'pre_registro_id' => $preRegistro['id']
        ]);
        $contabil = factory('App\Contabil')->raw([
            'telefone' => '(112) 988886-2233'
        ]);

        foreach($contabil as $key => $value)
            $contabil[$key . '_contabil'] = $value;

        $dados = array_merge($preRegistro, $preRegistroCpf, $contabil);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('telefone_contabil');
    }
}
