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
use App\ResponsavelTecnico;

class ResponsavelTecnicoTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CNPJ VIA AJAX - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw();
        $campos = ['registro'];
        
        foreach($rt as $key => $value)
        {
            $temp = in_array($key, $campos);
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $temp !== false ? $key : $key.'_rt',
                'valor' => $value
            ])->assertStatus(200);
        }
        
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw();
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw();
        $campos = ['registro'];
        
        foreach($rt as $key => $value)
        {
            $temp = in_array($key, $campos);
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => $temp !== false ? $key : $key.'_rt',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        }
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw();
        $campos = ['registro'];
        
        foreach($rt as $key => $value)
        {
            $temp = in_array($key, $campos);
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnicoErro',
                'campo' => $temp !== false ? $key : $key.'_rt',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        }
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw();
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_input_type_text_more_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $rt = [
            'registro' => $faker->sentence(400),
            'nome' => $faker->sentence(400),
            'nome_social' => $faker->sentence(400),
            'logradouro' => $faker->sentence(400),
            'complemento' => $faker->sentence(400),
            'bairro' => $faker->sentence(400),
            'cidade' => $faker->sentence(400),
            'nome_mae' => $faker->sentence(400),
            'nome_pai' => $faker->sentence(400),
            'identidade' => $faker->sentence(400),
            'orgao_emissor' => $faker->sentence(400),
        ];
        
        $campos = ['registro'];
        
        foreach($rt as $key => $value)
        {
            $temp = in_array($key, $campos);
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $temp !== false ? $key : $key.'_rt',
                'valor' => $value
            ])->assertSessionHasErrors('valor');
        }
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_cpf_wrong()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf'] . '5'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', [
            'cpf' => Carbon::today()->subYears(17)->format('Y-m-d')
        ]);

        $this->assertDatabaseMissing('pre_registros_cnpj', [
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->raw()['cpf'] . '5'
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_under_18_years_old()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'dt_nascimento_rt',
            'valor' => Carbon::today()->subYears(17)->format('Y-m-d')
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', [
            'dt_nascimento' => Carbon::today()->subYears(17)->format('Y-m-d')
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_dt_expedicao_after_today()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'dt_expedicao_rt',
            'valor' => Carbon::today()->addDay()->format('Y-m-d')
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', [
            'dt_expedicao' => Carbon::today()->addDay()->format('Y-m-d')
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_without_date_type()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $datas = [
            'dt_nascimento' => null, 
            'dt_expedicao' => null
        ];

        foreach($datas as $key => $value) 
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key . '_rt',
                'valor' => 'texto'
            ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', $datas);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_without_relationship()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw();
        
        foreach($rt as $key => $value)
        {
            if($key != 'cpf')
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'pessoaJuridica.responsavelTecnico',
                    'campo' => $key == 'registro' ? $key : $key . '_rt',
                    'valor' => $value
                ])->assertOk();
        }
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_when_remove_relationship()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw();
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key == 'registro' ? $key : $key . '_rt',
                'valor' => $value
            ])->assertOk();
        
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavelTecnico->id
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => ''
        ])->assertOk();

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function get_responsavel_tecnico_by_ajax_when_exists_in_database()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->create();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => $rt->cpf
        ])->assertJsonFragment($rt->toArray());
    }

    /** @test */
    public function get_responsavel_tecnico_by_ajax_when_exists_in_gerenti()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $rt = [
            'registro' => '0000000001', 
            'nome' => 'RC Teste 1', 
            'identidade' => '11.111.111-1',
            'orgao_emissor' => 'SSP-SP',
            'dt_expedicao' => '2012-03-05',
            'nome_pai' => 'PAI 1',
            'nome_mae' => 'MAE 1',
            'sexo' => 'M',
            'dt_nascimento' => '1962-09-30',
        ];

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => '86294373085'
        ])->assertJsonFragment($rt);

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO-CNPJ VIA SUBMIT - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function can_submit_pre_registro_cnpj_if_rt_exists_in_database()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $rt = factory('App\ResponsavelTecnico')->create();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $tempRT = array();
        foreach($rt->toArray() as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt->toArray());
        $this->assertEquals(ResponsavelTecnico::count(), 1);
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_if_rt_exists_in_gerenti()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $rt = factory('App\ResponsavelTecnico')->raw([
            'cpf' => '86294373085'
        ]);

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertEquals(ResponsavelTecnico::count(), 1);
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_cpf_rt()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'cpf' => null
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cpf_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_cpf_rt_with_wrong_value()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'cpf' => '012.012.456-88'
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cpf_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_nome_rt()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'nome' => null
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'nome' => $faker->sentence(400)
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_rt_with_numbers()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'nome' => 'Nome do RT com núm3ero5'
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_social_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'nome_social' => $faker->sentence(400)
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_social_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_social_rt_with_numbers()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'nome_social' => 'Nome do RT com núm3ero5'
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_social_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_registro_more_than_20_chars()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'registro' => '012345678998745632107'
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('registro');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_sexo_rt()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'sexo' => ''
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('sexo_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_sexo_rt_wrong_value()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'sexo' => 'N'
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('sexo_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_dt_nascimento_rt()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'dt_nascimento' => ''
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_nascimento_rt_without_date_type()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'dt_nascimento' => 'teste'
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_nascimento_rt_under_18_years_old()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'dt_nascimento' => Carbon::today()->subYears(17)->format('Y-m-d')
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_identidade_rt()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'identidade' => ''
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('identidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_identidade_rt_more_than_20_chars()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'identidade' => '987654321098765432101'
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('identidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_orgao_emissor_rt()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'orgao_emissor' => null
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('orgao_emissor_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_orgao_emissor_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'orgao_emissor' => $faker->sentence(400)
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('orgao_emissor_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_dt_expedicao_rt()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'dt_expedicao' => ''
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_expedicao_rt_without_date_type()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'dt_expedicao' => 'teste'
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_expedicao_rt_after_today()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'dt_expedicao' => Carbon::today()->addDay()->format('Y-m-d')
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_cep_rt()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'cep' => ''
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cep_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_cep_rt_more_than_9_chars()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'cep' => '01234-7890'
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cep_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_bairro_rt()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'bairro' => ''
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('bairro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_bairro_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'bairro' => $faker->sentence(400)
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('bairro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_logradouro_rt()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'logradouro' => ''
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('logradouro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_logradouro_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'logradouro' => $faker->sentence(400)
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('logradouro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_numero_rt()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'numero' => ''
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('numero_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_numero_rt_more_than_10_chars()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'numero' => '012345678a9'
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('numero_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_complemento_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'complemento' => $faker->sentence(400)
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('complemento_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_cidade_rt()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'cidade' => ''
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_cidade_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'cidade' => $faker->sentence(400)
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_uf_rt()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'uf' => ''
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('uf_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_uf_rt_with_wrong_value()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'uf' => 'SSP'
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('uf_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_nome_mae_rt()
    {
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'nome_mae' => null
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_mae_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'nome_mae' => $faker->sentence(400)
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_mae_rt_with_numbers()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'nome_mae' => 'Nome do RT com núm3ero5'
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_pai_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'nome_pai' => $faker->sentence(400)
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_pai_rt_with_numbers()
    {
        $faker = \Faker\Factory::create();
        Storage::fake('local');
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => $externo->id,
            'contabil_id' => null,
            'idusuario' => null
        ]);
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'responsavel_tecnico_id' => null,
        ]);
        $rt = factory('App\ResponsavelTecnico')->raw([
            'nome_pai' => 'Nome do RT com núm3ero5'
        ]);
        $tempRT = array();
        foreach($rt as $key => $value)
            $key == 'registro' ? $tempRT[$key] = $value : $tempRT[$key . '_rt'] = $value;
        
        $tempCnpj = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'];
        foreach($preRegistroCnpj as $key => $value)
            in_array($key, $tempCnpj) ? $tempCnpj[$key . '_empresa'] = $value : $tempCnpj[$key] = $value;

        $dados = array_merge($preRegistro, $tempCnpj, $tempRT);
        
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();     
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'anexos',
            'campo' => 'path',
            'valor' => UploadedFile::fake()->create('random.pdf')
        ])->assertOk();
        
        $this->put(route('externo.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_pai_rt');
    }
}
