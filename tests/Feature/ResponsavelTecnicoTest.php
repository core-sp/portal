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
use Illuminate\Support\Arr;

class ResponsavelTecnicoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function view_msg_update()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));

        PreRegistro::first()->update(['updated_at' => PreRegistro::first()->updated_at->subHour()]);
        $atual = PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s');

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => '288.198.540-82'
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
        $this->assertNotEquals($atual, PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO RESPONSAVEL TECNICO VIA AJAX - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);

        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_with_upperCase()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->states('low')->raw();
        unset($rt['registro']);

        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertStatus(200);
        
        foreach($rt as $key => $value)
            $rt[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_when_exists_others_pre_registros()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '67779004000190'
                ])
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '56821972000100'
                ])
            ])
        ]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw([
            'cpf' => '60923317058'
        ]);
        unset($rt['registro']);
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertStatus(200);

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => $externo->load('preRegistro')->preRegistro->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_when_exists_others_pre_registros_with_same_rt()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '67779004000190'
                ])
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '56821972000100'
                ])
            ])
        ]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertStatus(200);

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => $externo->load('preRegistro')->preRegistro->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
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
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnicoErro',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);
        
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
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $rt = [
            'nome' => $faker->text(500),
            'nome_social' => $faker->text(500),
            'logradouro' => $faker->text(500),
            'complemento' => $faker->text(500),
            'bairro' => $faker->text(500),
            'cidade' => $faker->text(500),
            'nome_mae' => $faker->text(500),
            'nome_pai' => $faker->text(500),
            'identidade' => $faker->text(500),
            'orgao_emissor' => $faker->text(500),
        ];
                
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertSessionHasErrors('valor');
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_cpf_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

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
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

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
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

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
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

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
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_sexo_rt_value_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'sexo_rt',
            'valor' => 'P'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', ['sexo' => 'P']);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_tipo_identidade_rt_value_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'tipo_identidade_rt',
            'valor' => 'Teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', ['tipo_identidade_rt' => 'Teste']);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_uf_rt_value_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'uf_rt',
            'valor' => 'TT'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', ['uf_rt' => 'TT']);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_without_relationship()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);

        foreach($rt as $key => $value)
            if($key != 'cpf')
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'pessoaJuridica.responsavelTecnico',
                    'campo' => $key . '_rt',
                    'valor' => $value
                ])->assertOk();
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_when_remove_relationship()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key . '_rt',
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

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'nome_rt',
            'valor' => 'Novo Teste'
        ])->assertOk();

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function get_responsavel_tecnico_by_ajax_when_exists_in_database()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

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
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

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

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_when_clean_inputs()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->create()->makeHidden(['registro', 'created_at', 'updated_at', 'id']);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => $rt->cpf
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => $rt->id
        ]);

        foreach($rt->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key . '_rt',
                'valor' => ''
            ])->assertOk();
        
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt->toArray());
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    // Status do pré-registro

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $rt = factory('App\ResponsavelTecnico')->create();
        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => $rt->id
        ]);

        $rtAjax = $rt->makeHidden(['id', 'created_at', 'updated_at', 'deleted_at', 'registro'])->toArray();
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                foreach($rtAjax as $key => $value)
                    $this->post(route('externo.inserir.preregistro.ajax'), [
                        'classe' => 'pessoaJuridica.responsavelTecnico',
                        'campo' => $key . '_rt',
                        'valor' => ''
                    ])->assertStatus(401);
        }
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_with_status_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $rt = factory('App\ResponsavelTecnico')->create();
        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => $rt->id
        ]);

        $rtAjax = $rt->makeHidden(['id', 'created_at', 'updated_at', 'deleted_at', 'registro'])->toArray();
        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            foreach($rtAjax as $key => $value)
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'pessoaJuridica.responsavelTecnico',
                    'campo' => $key . '_rt',
                    'valor' => ''
                ])->assertStatus(200);
        }
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO RESPONSAVEL TECNICO VIA SUBMIT - CLIENT
     * =======================================================================================================
     */

    /** @test */
    public function view_message_errors_when_submit_with_cnpj()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $dados = [
            'nome_rt' => null, 'nome_social_rt' => 'R', 'sexo_rt' => null, 'dt_nascimento_rt' => null, 'cep_rt' => null, 'logradouro_rt' => null,
            'numero_rt' => null, 'complemento_rt' => 'f', 'bairro_rt' => null, 'cidade_rt' => null, 'uf_rt' => null, 'nome_mae_rt' => null, 'nome_pai_rt' => 'g',
            'tipo_identidade_rt' => null, 'identidade_rt' => null, 'orgao_emissor_rt' => null, 'dt_expedicao_rt' => null, 'path' => null
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
    public function can_submit_pre_registro_cnpj_if_rt_exists_in_database()
    {
        $rt = factory('App\ResponsavelTecnico')->states('low')->create()->toArray();

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $pr = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make();
        $dados = $pr->final;
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('responsaveis_tecnicos', [
            'nome_mae' => $rt['nome_mae'],
            'identidade' => $rt['identidade'],
            'logradouro' => $rt['logradouro'],
            'numero' => $rt['numero'],
            'cpf' => $rt['cpf'],
            'nome' => $rt['nome']
        ]);
        $this->assertEquals(ResponsavelTecnico::count(), 1);
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_if_rt_exists_in_gerenti()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5
        $rt = factory('App\ResponsavelTecnico')->states('low')->raw([
            'cpf' => '86294373085',
        ]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $pr = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make([
            'responsavel_tecnico_id' => null,
        ]);
        $dados = $pr->final;
        foreach($rt as $chave => $val)
            !in_array($chave, ['registro']) ? $dados[$chave.'_rt'] = $val : null;
        
        $this->put(route('externo.inserir.preregistro'), $dados)->assertRedirect(route('externo.preregistro.view'));

        foreach($rt as $key => $value)
            $rt[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('responsaveis_tecnicos', [
            'id' => 1,
            'nome_mae' => $rt['nome_mae'],
            'identidade' => $rt['identidade'],
            'logradouro' => $rt['logradouro'],
            'numero' => $rt['numero'],
            'cpf' => $rt['cpf'],
            'nome' => $rt['nome'],
            'registro' => '0000000001'
        ]);
        
        $this->assertEquals(ResponsavelTecnico::count(), 1);
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_cpf_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['cpf_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cpf_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_cpf_rt_with_wrong_value()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['cpf_rt'] = '12345678922';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cpf_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_nome_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_rt_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_rt'] = 'Nome';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_rt_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_rt'] = 'N0me com númer0';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_social_rt_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_social_rt'] = 'Nome';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_social_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_social_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_social_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_social_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_social_rt_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_social_rt'] = 'Nom3 com numeros';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_social_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_sexo_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['sexo_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('sexo_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_sexo_rt_wrong_value()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['sexo_rt'] = 'E';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('sexo_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_dt_nascimento_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_nascimento_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_nascimento_rt_incorrect_format()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_nascimento_rt'] = '2000/01/01';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_nascimento_rt_without_date_type()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_nascimento_rt'] = 'texto';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_nascimento_rt_under_18_years_old()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_nascimento_rt'] = Carbon::today()->subYears(17)->format('Y-m-d');
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_tipo_identidade_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['tipo_identidade_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_identidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_tipo_identidade_rt_with_wrong_value()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['tipo_identidade_rt'] = 'Doc';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('tipo_identidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_identidade_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['identidade_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('identidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_identidade_rt_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['identidade_rt'] = '12A';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('identidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_identidade_rt_more_than_30_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['identidade_rt'] = '123456789012345678901234567890123';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('identidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_orgao_emissor_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['orgao_emissor_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('orgao_emissor_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_orgao_emissor_rt_less_than_3_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['orgao_emissor_rt'] = 'sd';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('orgao_emissor_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_orgao_emissor_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['orgao_emissor_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('orgao_emissor_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_dt_expedicao_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_expedicao_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_expedicao_rt_without_date_type()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_expedicao_rt'] = 'texto';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_expedicao_rt_incorrect_format()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_expedicao_rt'] = '2000/01/25';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_expedicao_rt_after_today()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_expedicao_rt'] = Carbon::today()->addDay()->format('Y-m-d');
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_cep_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['cep_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cep_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_cep_rt_more_than_9_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['cep_rt'] = '012345698';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cep_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_cep_rt_incorrect_format()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['cep_rt'] = '012-12365';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cep_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_bairro_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['bairro_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('bairro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_bairro_rt_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['bairro_rt'] = 'Bai';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('bairro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_bairro_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['bairro_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('bairro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_logradouro_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['logradouro_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('logradouro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_logradouro_rt_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['logradouro_rt'] = 'Log';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('logradouro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_logradouro_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['logradouro_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('logradouro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_numero_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['numero_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('numero_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_numero_rt_more_than_10_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['numero_rt'] = '123456789lp';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('numero_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_complemento_rt_more_than_50_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['complemento_rt'] = $faker->text(200);
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('complemento_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_cidade_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['cidade_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_cidade_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['cidade_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('cidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_uf_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['uf_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('uf_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_uf_rt_with_wrong_value()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['uf_rt'] = 'UF';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('uf_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_nome_mae_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_mae_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_mae_rt_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_mae_rt'] = 'Mãen';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_mae_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_mae_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_mae_rt_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_mae_rt'] = 'M4mãe';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_pai_rt_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_pai_rt'] = 'paiz';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_pai_rt_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_pai_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_pai_rt_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();    

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_pai_rt'] = 'pa1 teste';
        
        $this->put(route('externo.verifica.inserir.preregistro'), $dados)
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** 
     * ==========================================================================================================================
     * TESTES PRE-REGISTRO RESPONSAVEL TECNICO - LOGIN CONTABILIDADE RESPONSÁVEL PELO GERENCIAMENTO PARA O USUARIO EXTERNO COMUM
     * ==========================================================================================================================
     */

    /** @test */
    public function view_msg_update_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));

        PreRegistro::first()->update(['updated_at' => PreRegistro::first()->updated_at->subHour()]);
        $atual = PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s');

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => '288.198.540-82'
        ])->assertStatus(200);
        
        $this->get(route('externo.inserir.preregistro.view', ['preRegistro' => 1]))
        ->assertSeeText('Atualizado em: ')
        ->assertSeeText(PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
        $this->assertNotEquals($atual, PreRegistro::first()->updated_at->format('d\/m\/Y, \à\s H:i:s'));
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);

        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_with_upperCase_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = factory('App\ResponsavelTecnico')->states('low')->raw();
        unset($rt['registro']);

        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertStatus(200);
        
        foreach($rt as $key => $value)
            $rt[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_when_exists_others_pre_registros_by_contabilidade()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '67779004000190'
                ])
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '56821972000100'
                ])
            ])
        ]);

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create(['cnpj' => '67779004000190']));
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;

        $rt = factory('App\ResponsavelTecnico')->raw([
            'cpf' => '60923317058'
        ]);
        unset($rt['registro']);
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => $id]), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertStatus(200);

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => $externo->preRegistros->first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_when_exists_others_pre_registros_with_same_rt_by_contabilidade()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '67779004000190'
                ])
            ])
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create([
                    'cpf_cnpj' => '56821972000100'
                ])
            ])
        ]);

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create(['cnpj' => '67779004000190']));
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        $id = $externo->preRegistros->first()->id;

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => $id]), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertStatus(200);

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => $externo->preRegistros->first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_wrong_input_name_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = factory('App\ResponsavelTecnico')->raw();
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
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
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_without_classe_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => '',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_wrong_classe_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.responsavelTecnicoErro',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_without_campo_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
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
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_input_type_text_more_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = [
            'nome' => $faker->text(500),
            'nome_social' => $faker->text(500),
            'logradouro' => $faker->text(500),
            'complemento' => $faker->text(500),
            'bairro' => $faker->text(500),
            'cidade' => $faker->text(500),
            'nome_mae' => $faker->text(500),
            'nome_pai' => $faker->text(500),
            'identidade' => $faker->text(500),
            'orgao_emissor' => $faker->text(500),
        ];
                
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertSessionHasErrors('valor');
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_cpf_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
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
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_under_18_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
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
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_dt_expedicao_after_today_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
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
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $datas = [
            'dt_nascimento' => null, 
            'dt_expedicao' => null
        ];

        foreach($datas as $key => $value) 
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
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
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_sexo_rt_value_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'sexo_rt',
            'valor' => 'P'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', ['sexo' => 'P']);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_tipo_identidade_rt_value_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'tipo_identidade_rt',
            'valor' => 'Teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', ['tipo_identidade_rt' => 'Teste']);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_uf_rt_value_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'uf_rt',
            'valor' => 'TT'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', ['uf_rt' => 'TT']);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_without_relationship_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);

        foreach($rt as $key => $value)
            if($key != 'cpf')
                $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                    'classe' => 'pessoaJuridica.responsavelTecnico',
                    'campo' => $key . '_rt',
                    'valor' => $value
                ])->assertOk();
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_when_remove_relationship_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key . '_rt',
                'valor' => $value
            ])->assertOk();
        
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavelTecnico->id
        ]);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => ''
        ])->assertOk();

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'nome_rt',
            'valor' => 'Novo Teste'
        ])->assertOk();

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function get_responsavel_tecnico_by_ajax_when_exists_in_database_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = factory('App\ResponsavelTecnico')->create();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => $rt->cpf
        ])->assertJsonFragment($rt->toArray());
    }

    /** @test */
    public function get_responsavel_tecnico_by_ajax_when_exists_in_gerenti_by_contabilidade()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

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

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => '86294373085'
        ])->assertJsonFragment($rt);

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_when_clean_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = factory('App\ResponsavelTecnico')->create()->makeHidden(['registro', 'created_at', 'updated_at', 'id']);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => $rt->cpf
        ])->assertOk();

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => $rt->id
        ]);

        foreach($rt->toArray() as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key . '_rt',
                'valor' => ''
            ])->assertOk();
        
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt->toArray());
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    // Status do pré-registro

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $rt = factory('App\ResponsavelTecnico')->create();
        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => $rt->id
        ]);

        $rtAjax = $rt->makeHidden(['id', 'created_at', 'updated_at', 'deleted_at', 'registro'])->toArray();
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                foreach($rtAjax as $key => $value)
                    $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                        'classe' => 'pessoaJuridica.responsavelTecnico',
                        'campo' => $key . '_rt',
                        'valor' => ''
                    ])->assertStatus(401);
        }
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_with_status_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $rt = factory('App\ResponsavelTecnico')->create();
        $preRegistro = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => $rt->id
        ]);

        $rtAjax = $rt->makeHidden(['id', 'created_at', 'updated_at', 'deleted_at', 'registro'])->toArray();
        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            foreach($rtAjax as $key => $value)
                $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                    'classe' => 'pessoaJuridica.responsavelTecnico',
                    'campo' => $key . '_rt',
                    'valor' => ''
                ])->assertStatus(200);
        }
    }

    /** @test */
    public function view_message_errors_when_submit_with_cnpj_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = [
            'nome_rt' => null, 'nome_social_rt' => 'R', 'sexo_rt' => null, 'dt_nascimento_rt' => null, 'cep_rt' => null, 'logradouro_rt' => null,
            'numero_rt' => null, 'complemento_rt' => 'f', 'bairro_rt' => null, 'cidade_rt' => null, 'uf_rt' => null, 'nome_mae_rt' => null, 'nome_pai_rt' => 'g',
            'tipo_identidade_rt' => null, 'identidade_rt' => null, 'orgao_emissor_rt' => null, 'dt_expedicao_rt' => null, 'path' => null
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
    public function can_submit_pre_registro_cnpj_if_rt_exists_in_database_by_contabilidade()
    {
        $rt = factory('App\ResponsavelTecnico')->states('low')->create()->toArray();

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $pr = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make();
        $dados = $pr->final;
        
        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $this->assertDatabaseHas('responsaveis_tecnicos', [
            'nome_mae' => $rt['nome_mae'],
            'identidade' => $rt['identidade'],
            'logradouro' => $rt['logradouro'],
            'numero' => $rt['numero'],
            'cpf' => $rt['cpf'],
            'nome' => $rt['nome']
        ]);
        $this->assertEquals(ResponsavelTecnico::count(), 1);
    }

    /** @test */
    public function can_submit_pre_registro_cnpj_if_rt_exists_in_gerenti_by_contabilidade()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5
        $rt = factory('App\ResponsavelTecnico')->states('low')->raw([
            'cpf' => '86294373085',
        ]);

        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $pr = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make([
            'responsavel_tecnico_id' => null,
        ]);
        $dados = $pr->final;
        foreach($rt as $chave => $val)
            !in_array($chave, ['registro']) ? $dados[$chave.'_rt'] = $val : null;
        
        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        foreach($rt as $key => $value)
            $rt[$key] = isset($value) ? mb_strtoupper($value, 'UTF-8') : $value;
        $this->assertDatabaseHas('responsaveis_tecnicos', [
            'id' => 1,
            'nome_mae' => $rt['nome_mae'],
            'identidade' => $rt['identidade'],
            'logradouro' => $rt['logradouro'],
            'numero' => $rt['numero'],
            'cpf' => $rt['cpf'],
            'nome' => $rt['nome'],
            'registro' => '0000000001'
        ]);
        
        $this->assertEquals(ResponsavelTecnico::count(), 1);
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_cpf_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['cpf_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('cpf_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_cpf_rt_with_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['cpf_rt'] = '12345678922';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('cpf_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_nome_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_rt_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_rt'] = 'Nome';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_rt_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_rt_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_rt'] = 'N0me com númer0';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_social_rt_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_social_rt'] = 'Nome';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_social_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_social_rt_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_social_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_social_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_social_rt_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_social_rt'] = 'Nom3 com numeros';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_social_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_sexo_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['sexo_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('sexo_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_sexo_rt_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['sexo_rt'] = 'E';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('sexo_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_dt_nascimento_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_nascimento_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_nascimento_rt_incorrect_format_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_nascimento_rt'] = '2000/01/01';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_nascimento_rt_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_nascimento_rt'] = 'texto';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_nascimento_rt_under_18_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_nascimento_rt'] = Carbon::today()->subYears(17)->format('Y-m-d');
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_tipo_identidade_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['tipo_identidade_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('tipo_identidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_tipo_identidade_rt_with_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['tipo_identidade_rt'] = 'Doc';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('tipo_identidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_identidade_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['identidade_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('identidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_identidade_rt_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['identidade_rt'] = '12A';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('identidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_identidade_rt_more_than_30_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['identidade_rt'] = '123456789012345678901234567890123';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('identidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_orgao_emissor_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['orgao_emissor_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('orgao_emissor_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_orgao_emissor_rt_less_than_3_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['orgao_emissor_rt'] = 'sd';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('orgao_emissor_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_orgao_emissor_rt_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['orgao_emissor_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('orgao_emissor_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_dt_expedicao_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_expedicao_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_expedicao_rt_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_expedicao_rt'] = 'texto';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_expedicao_rt_incorrect_format_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_expedicao_rt'] = '2000/01/25';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_dt_expedicao_rt_after_today_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['dt_expedicao_rt'] = Carbon::today()->addDay()->format('Y-m-d');
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_cep_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['cep_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('cep_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_cep_rt_more_than_9_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['cep_rt'] = '012345698';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('cep_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_cep_rt_incorrect_format_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['cep_rt'] = '012-12365';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('cep_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_bairro_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['bairro_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('bairro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_bairro_rt_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['bairro_rt'] = 'Bai';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('bairro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_bairro_rt_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['bairro_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('bairro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_logradouro_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['logradouro_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('logradouro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_logradouro_rt_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['logradouro_rt'] = 'Log';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('logradouro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_logradouro_rt_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['logradouro_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('logradouro_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_numero_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['numero_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('numero_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_numero_rt_more_than_10_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['numero_rt'] = '123456789lp';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('numero_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_complemento_rt_more_than_50_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['complemento_rt'] = $faker->text(200);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('complemento_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_cidade_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['cidade_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('cidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_cidade_rt_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['cidade_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('cidade_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_uf_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['uf_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('uf_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_uf_rt_with_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['uf_rt'] = 'UF';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('uf_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_without_nome_mae_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_mae_rt'] = '';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_mae_rt_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_mae_rt'] = 'Mãen';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_mae_rt_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_mae_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_mae_rt_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_mae_rt'] = 'M4mãe';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_pai_rt_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_pai_rt'] = 'paiz';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_pai_rt_more_than_191_chars_by_contabilidade()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_pai_rt'] = $faker->text(500);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** @test */
    public function cannot_submit_pre_registro_cnpj_with_nome_pai_rt_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados); 

        $dados = factory('App\PreRegistroCnpj')->states('request_mesmo_endereco')->make()->final;
        $dados['nome_pai_rt'] = 'pa1 teste';
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), $dados)
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** 
     * =======================================================================================================
     * TESTES PRE-REGISTRO RESPONSAVEL TECNICO - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function view_pre_registro_rt()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();
        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_INICIAL]);
        $rt = $preRegistroCnpj->responsavelTecnico;
        
        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText(formataRegistro($rt->registro))
        ->assertSeeText(formataCpfCnpj($rt->cpf))
        ->assertSeeText($rt->nome)
        ->assertSeeText($rt->nome_social)
        ->assertSeeText(onlyDate($rt->dt_nascimento))
        ->assertSeeText($rt->sexo)
        ->assertSeeText($rt->nome_mae)
        ->assertSeeText($rt->nome_pai)
        ->assertSeeText($rt->tipo_identidade)
        ->assertSeeText($rt->identidade)
        ->assertSeeText($rt->orgao_emissor)
        ->assertSeeText(onlyDate($rt->dt_expedicao))
        ->assertSeeText($rt->cep)
        ->assertSeeText($rt->logradouro)
        ->assertSeeText($rt->numero)
        ->assertSeeText($rt->complemento)
        ->assertSeeText($rt->bairro)
        ->assertSeeText($rt->cidade)
        ->assertSeeText($rt->uf);
    }

    /** @test */
    public function view_text_justificado_rt()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->states('justificado')->create();
        $justificativas = $preRegistroCnpj->preRegistro->getJustificativaArray();

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText($justificativas['registro'])
        ->assertSeeText($justificativas['cpf_rt'])
        ->assertSeeText($justificativas['nome_rt'])
        ->assertSeeText($justificativas['nome_social_rt'])
        ->assertSeeText($justificativas['dt_nascimento_rt'])
        ->assertSeeText($justificativas['sexo_rt'])
        ->assertSeeText($justificativas['nome_mae_rt'])
        ->assertSeeText($justificativas['nome_pai_rt'])
        ->assertSeeText($justificativas['tipo_identidade_rt'])
        ->assertSeeText($justificativas['identidade_rt'])
        ->assertSeeText($justificativas['orgao_emissor_rt'])
        ->assertSeeText($justificativas['dt_expedicao_rt'])
        ->assertSeeText($justificativas['cep_rt'])
        ->assertSeeText($justificativas['logradouro_rt'])
        ->assertSeeText($justificativas['numero_rt'])
        ->assertSeeText($justificativas['complemento_rt'])
        ->assertSeeText($justificativas['bairro_rt'])
        ->assertSeeText($justificativas['cidade_rt'])
        ->assertSeeText($justificativas['uf_rt']);
    }
}
