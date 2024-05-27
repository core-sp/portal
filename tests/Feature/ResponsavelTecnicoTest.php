<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\PreRegistro;
use Carbon\Carbon;
use App\ResponsavelTecnico;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Testing\WithFaker;

class ResponsavelTecnicoTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    const CPF_GERENTI = '86294373085';

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
            'valor' => formataCpfCnpj(factory('App\ResponsavelTecnico')->raw()['cpf'])
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
            'responsavel_tecnico_id' => 1
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
            'responsavel_tecnico_id' => 1
        ]);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_when_exists_others_pre_registros()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create()
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
            'responsavel_tecnico_id' => 2
        ]);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_when_exists_others_pre_registros_with_same_rt()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ]);

        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $rt = $preRegistroCnpj_1->responsavelTecnico->attributesToArray();

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        
        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => $rt['cpf']
        ])->assertStatus(200);

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();
        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
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
            'responsavel_tecnico_id' => null
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
            'responsavel_tecnico_id' => null
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
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_input_type_text_more_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $rt = [
            'nome' => $this->faker()->text(500),
            'nome_social' => $this->faker()->text(500),
            'logradouro' => $this->faker()->text(500),
            'complemento' => $this->faker()->text(500),
            'bairro' => $this->faker()->text(500),
            'cidade' => $this->faker()->text(500),
            'nome_mae' => $this->faker()->text(500),
            'nome_pai' => $this->faker()->text(500),
            'identidade' => $this->faker()->text(500),
            'orgao_emissor' => $this->faker()->text(500),
            'titulo_eleitor' => $this->faker()->text(500),
            'zona' => $this->faker()->text(500),
            'secao' => $this->faker()->text(500),
            'ra_reservista' => $this->faker()->text(500),
        ];
                
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertSessionHasErrors('valor');
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
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

        $this->assertEquals(ResponsavelTecnico::count(), 0);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_cep_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cep_rt',
            'valor' => '123-456789'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', [
            'cep' => '123-456789'
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_under_18_years_old()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'dt_nascimento_rt',
            'valor' => Carbon::today()->subYears(17)->format('Y-m-d')
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', [
            'dt_nascimento' => Carbon::today()->subYears(17)->format('Y-m-d')
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_dt_expedicao_after_today()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'dt_expedicao_rt',
            'valor' => Carbon::today()->addDay()->format('Y-m-d')
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', [
            'dt_expedicao' => Carbon::today()->addDay()->format('Y-m-d')
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_without_date_type()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

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

        $this->assertDatabaseHas('responsaveis_tecnicos', $datas);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_sexo_rt_value_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'sexo_rt',
            'valor' => 'P'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', ['sexo' => 'P']);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_tipo_identidade_rt_value_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'tipo_identidade_rt',
            'valor' => 'Teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', ['tipo_identidade_rt' => 'Teste']);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_uf_rt_value_wrong()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'uf_rt',
            'valor' => 'TT'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', ['uf_rt' => 'TT']);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_without_relationship()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);

        foreach($rt as $key => $value){
            if($key != 'cpf')
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'pessoaJuridica.responsavelTecnico',
                    'campo' => $key . '_rt',
                    'valor' => $value
                ])->assertStatus(500);
        }
        
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
            'responsavel_tecnico_id' => 1
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
        ])->assertStatus(500);

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseMissing('responsaveis_tecnicos', ['nome' => 'Novo Teste']);
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
            'nome' => 'RC TESTE 1', 
            'identidade' => '111111111',
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
            'valor' => self::CPF_GERENTI
        ])->assertJsonFragment($rt);

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_when_exists_in_gerenti_and_empty_input_registro_in_database()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $rt = factory('App\ResponsavelTecnico')->create([
            'cpf' => self::CPF_GERENTI
        ]);

        $this->assertDatabaseHas('responsaveis_tecnicos', ['registro' => null, 'cpf' => self::CPF_GERENTI]);

        $rt = [
            'registro' => '0000000001', 
            'nome' => 'RC TESTE 1', 
            'identidade' => '111111111',
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
            'valor' => self::CPF_GERENTI
        ])->assertJsonFragment($rt);

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_when_non_exists_in_gerenti_and_fill_input_registro_in_database()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 2
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        $this->get(route('externo.inserir.preregistro.view', ['checkPreRegistro' => 'on']))->assertOk();

        $rt = [
            'registro' => '0000000001', 
            'nome' => 'RC TESTE 1', 
            'identidade' => '111111111',
            'orgao_emissor' => 'SSP-SP',
            'dt_expedicao' => '2012-03-05',
            'nome_pai' => 'PAI 1',
            'nome_mae' => 'MAE 1',
            'sexo' => 'M',
            'dt_nascimento' => '1962-09-30',
        ];

        $rt = factory('App\ResponsavelTecnico')->create([
            'cpf' => self::CPF_GERENTI,
            'registro' => $rt['registro'],
        ]);

        $this->assertDatabaseHas('responsaveis_tecnicos', ['registro' => $rt['registro'], 'cpf' => self::CPF_GERENTI]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => self::CPF_GERENTI
        ])->assertJsonFragment(['registro' => null, 'cpf' => self::CPF_GERENTI]);

        $this->assertDatabaseHas('responsaveis_tecnicos', ['registro' => null, 'cpf' => self::CPF_GERENTI]);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_when_clean_inputs()
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
            'responsavel_tecnico_id' => 1
        ]);

        foreach($rt->arrayValidacaoInputs() as $key => $value)
            $key != 'cpf_rt' ? $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key,
                'valor' => ''
            ])->assertOk() : null;

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt->fresh()->toArray());
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => $rt->id
        ]);
    }

    // Status do pré-registro

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $preRegistro = factory('App\PreRegistroCnpj')->create();

        $rtAjax = $preRegistro->responsavelTecnico->arrayValidacaoInputs();
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                foreach($rtAjax as $key => $value)
                    $this->post(route('externo.inserir.preregistro.ajax'), [
                        'classe' => 'pessoaJuridica.responsavelTecnico',
                        'campo' => $key,
                        'valor' => ''
                    ])->assertStatus(401);
        }
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_with_status_aguardando_correcao_or_sendo_elaborado()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $preRegistro = factory('App\PreRegistroCnpj')->create();

        $rtAjax = Arr::except($preRegistro->responsavelTecnico->arrayValidacaoInputs(), ['cpf_rt']);
        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            foreach($rtAjax as $key => $value)
                $this->post(route('externo.inserir.preregistro.ajax'), [
                    'classe' => 'pessoaJuridica.responsavelTecnico',
                    'campo' => $key,
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
    public function can_submit_rt_if_rt_exists_in_database()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $pr = factory('App\PreRegistroCnpj')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertOk();

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $rt = $pr->responsavelTecnico->toArray();

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
    public function can_submit_rt_if_rt_exists_in_gerenti()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => null
        ]);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => self::CPF_GERENTI
        ])->assertJsonFragment([
            'cpf' => self::CPF_GERENTI,
            'registro' => '0000000001'
        ]);

        $rt = Arr::except(factory('App\ResponsavelTecnico')->raw(), ['cpf', 'registro']);
        ResponsavelTecnico::first()->update($rt);

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $this->assertDatabaseHas('responsaveis_tecnicos', [
            'id' => 1,
            'cpf' => self::CPF_GERENTI,
            'registro' => '0000000001'
        ]);
        
        $this->assertEquals(ResponsavelTecnico::count(), 1);
    }

    /** @test */
    public function can_submit_rt_without_optional_inputs()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_social' => null,
                'complemento' => null,
                'nome_pai' => null,
                'ra_reservista' => null,
            ])
        ])->responsavelTecnico->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));
        
        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function cannot_submit_rt_without_required_inputs()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome' => null,
                'sexo' => null,
                'dt_nascimento' => null,
                'cep' => null,
                'logradouro' => null,
                'numero' => null,
                'bairro' => null,
                'cidade' => null,
                'uf' => null,
                'nome_mae' => null,
                'tipo_identidade' => null,
                'identidade' => null,
                'orgao_emissor' => null,
                'dt_expedicao' => null,
                'titulo_eleitor' => null,
                'zona' => null,
                'secao' => null,
            ])
        ])->responsavelTecnico->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => '25 meses'])
        ->assertSessionHasErrors([
            'nome_rt', 'sexo_rt', 'dt_nascimento_rt', 'cep_rt', 'logradouro_rt', 'numero_rt', 'bairro_rt', 'cidade_rt', 'uf_rt', 'nome_mae_rt', 'tipo_identidade_rt',
            'identidade_rt', 'orgao_emissor_rt', 'dt_expedicao_rt', 'titulo_eleitor_rt', 'zona_rt', 'secao_rt'
        ]);

        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_CRIADO);
    }

    /** @test */
    public function cannot_submit_rt_without_cpf_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $pr = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => null
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_cpf_rt_with_wrong_value()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'cpf' => '12345678922'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_nome_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_rt_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome' => 'Nome'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_rt_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome' => $this->faker()->text(500)
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_rt_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome' => 'N0me com númer0'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_social_rt_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_social' => 'Nome'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_social_rt_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_social' => $this->faker()->text(500)
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_social_rt_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_social' => 'Nom3 com numeros'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_sexo_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'sexo' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('sexo_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_sexo_rt_wrong_value()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'sexo' => 'E'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('sexo_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_dt_nascimento_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_nascimento' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_dt_nascimento_rt_incorrect_format()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_nascimento' => '2000/01/01'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_dt_nascimento_rt_without_date_type()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_nascimento' => 'texto'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_dt_nascimento_rt_under_18_years_old()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
       
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_nascimento' => Carbon::today()->subYears(17)->format('Y-m-d')
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_tipo_identidade_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'tipo_identidade' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('tipo_identidade_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_tipo_identidade_rt_with_wrong_value()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'tipo_identidade' => 'Doc'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('tipo_identidade_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_identidade_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'identidade' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_identidade_rt_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'identidade' => '12A'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_identidade_rt_more_than_30_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'identidade' => '123456789012345678901234567890123'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_orgao_emissor_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'orgao_emissor' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_orgao_emissor_rt_less_than_3_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'orgao_emissor' => 'sd'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_orgao_emissor_rt_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'orgao_emissor' => $this->faker()->text(500)
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_dt_expedicao_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_expedicao' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_dt_expedicao_rt_without_date_type()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_expedicao' => 'texto'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_dt_expedicao_rt_incorrect_format()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_expedicao' => '2000/01/25'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_dt_expedicao_rt_after_today()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_expedicao' => Carbon::today()->addDay()->format('Y-m-d')
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_titulo_eleitor()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'titulo_eleitor' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('titulo_eleitor_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_titulo_eleitor_less_than_12_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'titulo_eleitor' => '23569874521'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('titulo_eleitor_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_titulo_eleitor_more_than_15_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'titulo_eleitor' => '2356987452123658'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('titulo_eleitor_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_zona()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'zona' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('zona_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_zona_more_than_6_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'zona' => '7536985'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('zona_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_secao()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'secao' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('secao_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_secao_more_than_8_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'secao' => '753698575'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('secao_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_ra_reservista_if_sexo_m_and_under_45_years_old()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_nascimento' => '1995-05-23',
                'sexo' => 'M',
                'ra_reservista' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('ra_reservista_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_ra_reservista_less_than_12_chars_if_sexo_m_and_under_45_years_old()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_nascimento' => '1995-05-23',
                'sexo' => 'M',
                'ra_reservista' => '55522211174'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('ra_reservista_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_ra_reservista_more_than_15_chars_if_sexo_m_and_under_45_years_old()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_nascimento' => '1995-05-23',
                'sexo' => 'M',
                'ra_reservista' => '5552221117488874'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('ra_reservista_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_cep_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'cep' => '',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_cep_rt_more_than_9_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'cep' => '012345698',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_cep_rt_incorrect_format()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'cep' => '012-12365',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_bairro_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'bairro' => '',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_bairro_rt_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'bairro' => 'Bai',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_bairro_rt_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'bairro' => $this->faker()->text(500),
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_logradouro_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'logradouro' => null,
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_logradouro_rt_less_than_4_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'logradouro' => 'Log',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_logradouro_rt_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'logradouro' => $this->faker()->text(500),
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_numero_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'numero' => '',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('numero_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_numero_rt_more_than_10_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'numero' => '123456789lp',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('numero_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_complemento_rt_more_than_50_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'complemento' => $this->faker()->text(200),
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('complemento_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_cidade_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'cidade' => '',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cidade_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_cidade_rt_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'cidade' => $this->faker()->text(500),
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cidade_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_uf_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'uf' => '',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('uf_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_uf_rt_with_wrong_value()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'uf' => 'UF',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('uf_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_nome_mae_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_mae' => '',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_mae_rt_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_mae' => 'Mãen',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_mae_rt_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_mae' => $this->faker()->text(500),
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_mae_rt_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_mae' => 'M4mãe',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_pai_rt_less_than_5_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_pai' => 'paiz',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_pai_rt_more_than_191_chars()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_pai' => $this->faker()->text(500),
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_pai_rt_with_numbers()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_pai' => 'pa1 teste',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** @test */
    public function filled_campos_editados_rt_when_form_is_submitted_when_status_aguardando_correcao()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $PreRegistroCnpj = factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])->assertViewIs('site.userExterno.inserir-pre-registro');
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'justificar',
            'campo' => 'razao_social',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->signInAsUserExterno('user_externo', $externo);

        $campos = [
            'cpf_rt' => factory('App\ResponsavelTecnico')->raw()['cpf'],
            'nome_rt' => str_replace("'", "", mb_strtoupper($this->faker()->name, 'UTF-8')),
            'nome_social_rt' => null,
            'sexo_rt' => 'O',
            'dt_nascimento_rt' => '1970-04-20',
            'tipo_identidade_rt' => mb_strtoupper(tipos_identidade()[2], 'UTF-8'),
            'identidade_rt' => '2211113X',
            'orgao_emissor_rt' => 'SSP - MG',
            'dt_expedicao_rt' => '2022-05-20',
            'cep_rt' => '03021-040',
            'bairro_rt' => 'TESTE BAIRRO RT NOVO',
            'logradouro_rt' => 'RUA TESTE DO RT NOVO',
            'numero_rt' => '155',
            'complemento_rt' => 'FUNDOS',
            'cidade_rt' => 'BELO HORIZONTE',
            'uf_rt' => 'MG',
            'nome_mae_rt' => str_replace("'", "", mb_strtoupper($this->faker()->name, 'UTF-8')),
            'nome_pai_rt' => null,
            'titulo_eleitor_rt' => '875698541263',
            'zona_rt' => '321',
            'secao_rt' => '54321',
            'ra_reservista_rt' => '789547896352',
        ];

        foreach($campos as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
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
    public function view_justifications_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs());
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
                '<a class="nav-link" data-toggle="pill" href="#parte_contato_rt">',
                'Contato / RT&nbsp',
                '<span class="badge badge-danger">',
                '</a>',
            ])
            ->assertSee('value="'. route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo]) .'"');
    }

    /** @test */
    public function view_justifications_text_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro'), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs());
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
     * ===================================================================================================================================
     * TESTES PRE-REGISTRO RESPONSAVEL TECNICO VIA AJAX - LOGIN CONTABILIDADE RESPONSÁVEL PELO GERENCIAMENTO PARA O USUARIO EXTERNO COMUM
     * ===================================================================================================================================
     */

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
            'responsavel_tecnico_id' => 1
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
            'responsavel_tecnico_id' => 1
        ]);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_when_exists_others_pre_registros_by_contabilidade()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ]);

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create());
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 3]), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertStatus(200);

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 2
        ]);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_when_exists_others_pre_registros_with_same_rt_by_contabilidade()
    {
        $preRegistroCnpj_1 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2000-03-10',
        ]);

        $preRegistroCnpj_2 = factory('App\PreRegistroCnpj')->create([
            'dt_inicio_atividade' => '2010-10-15',
            'responsavel_tecnico_id' => $preRegistroCnpj_1->responsavel_tecnico_id,
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create([
                'contabil_id' => $preRegistroCnpj_1->preRegistro->contabil_id,
                'user_externo_id' => factory('App\UserExterno')->create()
            ])
        ]);

        $externo = $this->signInAsUserExterno('contabil', factory('App\Contabil')->create());
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);
        
        $rt = $preRegistroCnpj_1->responsavelTecnico->attributesToArray();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 3]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => $rt['cpf']
        ])->assertStatus(200);

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_1->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', $preRegistroCnpj_2->attributesToArray());
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_wrong_input_name_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = factory('App\ResponsavelTecnico')->raw();
        unset($rt['registro']);
        
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
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
            'responsavel_tecnico_id' => null
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
            'responsavel_tecnico_id' => null
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
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_input_type_text_more_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = [
            'nome' => $this->faker()->text(500),
            'nome_social' => $this->faker()->text(500),
            'logradouro' => $this->faker()->text(500),
            'complemento' => $this->faker()->text(500),
            'bairro' => $this->faker()->text(500),
            'cidade' => $this->faker()->text(500),
            'nome_mae' => $this->faker()->text(500),
            'nome_pai' => $this->faker()->text(500),
            'identidade' => $this->faker()->text(500),
            'orgao_emissor' => $this->faker()->text(500),
            'titulo_eleitor' => $this->faker()->text(500),
            'zona' => $this->faker()->text(500),
            'secao' => $this->faker()->text(500),
            'ra_reservista' => $this->faker()->text(500),
        ];
                
        foreach($rt as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key.'_rt',
                'valor' => $value
            ])->assertSessionHasErrors('valor');
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', $rt);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
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

        $this->assertEquals(ResponsavelTecnico::count(), 0);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => null
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_cep_wrong_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cep_rt',
            'valor' => '123-456789'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', [
            'cep' => '123-456789'
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
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
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'dt_nascimento_rt',
            'valor' => Carbon::today()->subYears(17)->format('Y-m-d')
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', [
            'dt_nascimento' => Carbon::today()->subYears(17)->format('Y-m-d')
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
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
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'dt_expedicao_rt',
            'valor' => Carbon::today()->addDay()->format('Y-m-d')
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', [
            'dt_expedicao' => Carbon::today()->addDay()->format('Y-m-d')
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
        ]);
    }

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

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

        $this->assertDatabaseHas('responsaveis_tecnicos', $datas);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
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
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'sexo_rt',
            'valor' => 'P'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', ['sexo' => 'P']);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
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
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'tipo_identidade_rt',
            'valor' => 'Teste'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', ['tipo_identidade' => 'Teste']);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
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
            'campo' => 'cpf_rt',
            'valor' => factory('App\ResponsavelTecnico')->raw()['cpf']
        ])->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'uf_rt',
            'valor' => 'TT'
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseMissing('responsaveis_tecnicos', ['uf' => 'TT']);
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => 1
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
                ])->assertStatus(500);
        
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
            'responsavel_tecnico_id' => 1
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
        ])->assertStatus(500);

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
            'nome' => 'RC TESTE 1', 
            'identidade' => '111111111',
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
            'valor' => self::CPF_GERENTI
        ])->assertJsonFragment($rt);

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_when_exists_in_gerenti_and_empty_input_registro_in_database_by_contabilidade()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = factory('App\ResponsavelTecnico')->create([
            'cpf' => self::CPF_GERENTI
        ]);

        $this->assertDatabaseHas('responsaveis_tecnicos', ['registro' => null, 'cpf' => self::CPF_GERENTI]);

        $rt = [
            'registro' => '0000000001', 
            'nome' => 'RC TESTE 1', 
            'identidade' => '111111111',
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
            'valor' => self::CPF_GERENTI
        ])->assertJsonFragment($rt);

        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_when_non_exists_in_gerenti_and_fill_input_registro_in_database_by_contabilidade()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 2
        $externo = $this->signInAsUserExterno('contabil');
        $dados = factory('App\UserExterno')->states('pj', 'cadastro_by_contabil')->make()->toArray();
        $this->post(route('externo.contabil.inserir.preregistro'), $dados);

        $rt = [
            'registro' => '0000000001', 
            'nome' => 'RC TESTE 1', 
            'identidade' => '111111111',
            'orgao_emissor' => 'SSP-SP',
            'dt_expedicao' => '2012-03-05',
            'nome_pai' => 'PAI 1',
            'nome_mae' => 'MAE 1',
            'sexo' => 'M',
            'dt_nascimento' => '1962-09-30',
        ];

        $rt = factory('App\ResponsavelTecnico')->create([
            'cpf' => self::CPF_GERENTI,
            'registro' => $rt['registro'],
        ]);

        $this->assertDatabaseHas('responsaveis_tecnicos', ['registro' => $rt['registro'], 'cpf' => self::CPF_GERENTI]);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => self::CPF_GERENTI
        ])->assertJsonFragment(['registro' => null, 'cpf' => self::CPF_GERENTI]);

        $this->assertDatabaseHas('responsaveis_tecnicos', ['registro' => null, 'cpf' => self::CPF_GERENTI]);
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_when_clean_inputs_by_contabilidade()
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
            'responsavel_tecnico_id' => 1
        ]);

        foreach($rt->toArray() as $key => $value)
            $key != 'cpf' ? $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
                'campo' => $key . '_rt',
                'valor' => ''
            ])->assertOk() : null;
        
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt->fresh()->toArray());
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => $rt->id
        ]);
    }

    // Status do pré-registro

    /** @test */
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_with_status_different_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $preRegistro = factory('App\PreRegistroCnpj')->create();

        $rtAjax = $preRegistro->responsavelTecnico->arrayValidacaoInputs();
        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO]))
                foreach($rtAjax as $key => $value)
                    $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                        'classe' => 'pessoaJuridica.responsavelTecnico',
                        'campo' => $key,
                        'valor' => ''
                    ])->assertStatus(401);
        }
    }

    /** @test */
    public function can_update_table_responsaveis_tecnicos_by_ajax_with_status_aguardando_correcao_or_sendo_elaborado_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $preRegistro = factory('App\PreRegistroCnpj')->create();

        $rtAjax = Arr::except($preRegistro->responsavelTecnico->arrayValidacaoInputs(), ['cpf_rt']);
        foreach([PreRegistro::STATUS_CORRECAO, PreRegistro::STATUS_CRIADO] as $status)
        {
            $preRegistro->preRegistro->update(['status' => $status]);
            foreach($rtAjax as $key => $value)
                $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                    'classe' => 'pessoaJuridica.responsavelTecnico',
                    'campo' => $key,
                    'valor' => ''
                ])->assertStatus(200);
        }
    }

    /** 
     * =====================================================================================================================================
     * TESTES PRE-REGISTRO RESPONSÁVEL TÉCNICO VIA SUBMIT - LOGIN CONTABILIDADE RESPONSÁVEL PELO GERENCIAMENTO PARA O USUARIO EXTERNO COMUM
     * =====================================================================================================================================
     */

    /** @test */
    public function can_submit_rt_if_rt_exists_in_database_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $rt = $pr->responsavelTecnico->toArray();

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
    public function can_submit_rt_if_rt_exists_in_gerenti_by_contabilidade()
    {
        // Caso dê erro, analisar o GerentiMock para editar em gerentiBusca(), em pessoa física, no campo ASS_TP_ASSOC para 5
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => null
        ]);

        $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
            'classe' => 'pessoaJuridica.responsavelTecnico',
            'campo' => 'cpf_rt',
            'valor' => self::CPF_GERENTI
        ])->assertJsonFragment([
            'cpf' => self::CPF_GERENTI,
            'registro' => '0000000001'
        ]);

        $rt = Arr::except(factory('App\ResponsavelTecnico')->raw(), ['cpf', 'registro']);
        ResponsavelTecnico::first()->update($rt);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $this->assertDatabaseHas('responsaveis_tecnicos', [
            'id' => 1,
            'cpf' => self::CPF_GERENTI,
            'registro' => '0000000001'
        ]);
        
        $this->assertEquals(ResponsavelTecnico::count(), 1);
    }

    /** @test */
    public function can_submit_rt_without_optional_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_social' => null,
                'complemento' => null,
                'nome_pai' => null,
                'ra_reservista' => null,
            ])
        ])->responsavelTecnico->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));
        
        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_ANALISE_INICIAL);
    }

    /** @test */
    public function cannot_submit_rt_without_required_inputs_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome' => null,
                'sexo' => null,
                'dt_nascimento' => null,
                'cep' => null,
                'logradouro' => null,
                'numero' => null,
                'bairro' => null,
                'cidade' => null,
                'uf' => null,
                'nome_mae' => null,
                'tipo_identidade' => null,
                'identidade' => null,
                'orgao_emissor' => null,
                'dt_expedicao' => null,
                'titulo_eleitor' => null,
                'zona' => null,
                'secao' => null,
            ])
        ])->responsavelTecnico->attributesToArray();
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors([
            'nome_rt', 'sexo_rt', 'dt_nascimento_rt', 'cep_rt', 'logradouro_rt', 'numero_rt', 'bairro_rt', 'cidade_rt', 'uf_rt', 'nome_mae_rt', 'tipo_identidade_rt',
            'identidade_rt', 'orgao_emissor_rt', 'dt_expedicao_rt', 'titulo_eleitor_rt', 'zona_rt', 'secao_rt'
        ]);

        $this->assertDatabaseHas('pre_registros', PreRegistro::first()->attributesToArray());
        $this->assertDatabaseHas('responsaveis_tecnicos', $rt);

        $this->assertEquals(PreRegistro::find(1)->status, PreRegistro::STATUS_CRIADO);
    }

    /** @test */
    public function cannot_submit_rt_without_cpf_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $pr = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => null
        ]);

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_cpf_rt_with_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'cpf' => '12345678922'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cpf_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_nome_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_rt_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome' => 'Nome'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_rt_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome' => $this->faker()->text(500)
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_rt_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome' => 'N0me com númer0'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_social_rt_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_social' => 'Nome'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_social_rt_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_social' => $this->faker()->text(500)
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_social_rt_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_social' => 'Nom3 com numeros'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_social_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_sexo_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'sexo' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('sexo_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_sexo_rt_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'sexo' => 'E'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('sexo_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_dt_nascimento_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_nascimento' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_dt_nascimento_rt_incorrect_format_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_nascimento' => '2000/01/01'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_dt_nascimento_rt_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_nascimento' => 'texto'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_dt_nascimento_rt_under_18_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_nascimento' => Carbon::today()->subYears(17)->format('Y-m-d')
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_nascimento_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_tipo_identidade_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'tipo_identidade' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('tipo_identidade_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_tipo_identidade_rt_with_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'tipo_identidade' => 'Doc'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('tipo_identidade_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_identidade_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'identidade' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_identidade_rt_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'identidade' => '12A'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_identidade_rt_more_than_30_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'identidade' => '123456789012345678901234567890123'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('identidade_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_orgao_emissor_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'orgao_emissor' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_orgao_emissor_rt_less_than_3_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'orgao_emissor' => 'sd'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_orgao_emissor_rt_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'orgao_emissor' => $this->faker()->text(500)
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('orgao_emissor_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_dt_expedicao_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_expedicao' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_dt_expedicao_rt_without_date_type_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_expedicao' => 'texto'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_dt_expedicao_rt_incorrect_format_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_expedicao' => '2000/01/25'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_dt_expedicao_rt_after_today_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_expedicao' => Carbon::today()->addDay()->format('Y-m-d')
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('dt_expedicao_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_titulo_eleitor_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'titulo_eleitor' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('titulo_eleitor_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_titulo_eleitor_less_than_12_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'titulo_eleitor' => '23569874521'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('titulo_eleitor_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_titulo_eleitor_more_than_15_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'titulo_eleitor' => '2356987452123658'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('titulo_eleitor_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_zona_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'zona' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('zona_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_zona_more_than_6_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'zona' => '7536985'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('zona_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_secao_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'secao' => ''
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('secao_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_secao_more_than_8_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'secao' => '753698575'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('secao_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_ra_reservista_if_sexo_m_and_under_45_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_nascimento' => '1995-05-23',
                'sexo' => 'M',
                'ra_reservista' => ''
            ])
        ]);

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('ra_reservista_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_ra_reservista_less_than_12_chars_if_sexo_m_and_under_45_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_nascimento' => '1995-05-23',
                'sexo' => 'M',
                'ra_reservista' => '55522211174'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('ra_reservista_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_ra_reservista_more_than_15_chars_if_sexo_m_and_under_45_years_old_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'dt_nascimento' => '1995-05-23',
                'sexo' => 'M',
                'ra_reservista' => '5552221117488874'
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('ra_reservista_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_cep_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'cep' => '',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_cep_rt_more_than_9_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'cep' => '012345698',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_cep_rt_incorrect_format_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'cep' => '012-12365',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cep_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_bairro_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'bairro' => '',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_bairro_rt_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'bairro' => 'Bai',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_bairro_rt_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'bairro' => $this->faker()->text(500),
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('bairro_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_logradouro_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'logradouro' => null,
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_logradouro_rt_less_than_4_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'logradouro' => 'Log',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_logradouro_rt_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'logradouro' => $this->faker()->text(500),
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('logradouro_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_numero_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'numero' => '',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('numero_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_numero_rt_more_than_10_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'numero' => '123456789lp',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('numero_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_complemento_rt_more_than_50_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'complemento' => $this->faker()->text(200),
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('complemento_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_cidade_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'cidade' => '',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cidade_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_cidade_rt_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'cidade' => $this->faker()->text(500),
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('cidade_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_uf_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'uf' => '',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('uf_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_uf_rt_with_wrong_value_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'uf' => 'UF',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('uf_rt');
    }

    /** @test */
    public function cannot_submit_rt_without_nome_mae_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_mae' => '',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_mae_rt_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_mae' => 'Mãen',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_mae_rt_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_mae' => $this->faker()->text(500),
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_mae_rt_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_mae' => 'M4mãe',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_mae_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_pai_rt_less_than_5_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_pai' => 'paiz',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_pai_rt_more_than_191_chars_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_pai' => $this->faker()->text(500),
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** @test */
    public function cannot_submit_rt_with_nome_pai_rt_with_numbers_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');
        
        $rt = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => factory('App\ResponsavelTecnico')->create([
                'nome_pai' => 'pa1 teste',
            ])
        ]);
        
        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertSessionHasErrors('nome_pai_rt');
    }

    /** @test */
    public function filled_campos_editados_rt_when_form_is_submitted_when_status_aguardando_correcao_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        $PreRegistroCnpj = factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $admin = $this->signIn(PreRegistro::first()->user);

        $this->post(route('preregistro.update.ajax', 1), [
            'acao' => 'justificar',
            'campo' => 'razao_social',
            'valor' => $this->faker()->text(100)
        ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);

        $this->signInAsUserExterno('contabil', $externo);

        $campos = [
            'cpf_rt' => factory('App\ResponsavelTecnico')->raw()['cpf'],
            'nome_rt' => str_replace("'", "", mb_strtoupper($this->faker()->name, 'UTF-8')),
            'nome_social_rt' => null,
            'sexo_rt' => 'O',
            'dt_nascimento_rt' => '1970-04-20',
            'tipo_identidade_rt' => mb_strtoupper(tipos_identidade()[2], 'UTF-8'),
            'identidade_rt' => '2211113X',
            'orgao_emissor_rt' => 'SSP - MG',
            'dt_expedicao_rt' => '2022-05-20',
            'cep_rt' => '03021-040',
            'bairro_rt' => 'TESTE BAIRRO RT NOVO',
            'logradouro_rt' => 'RUA TESTE DO RT NOVO',
            'numero_rt' => '155',
            'complemento_rt' => 'FUNDOS',
            'cidade_rt' => 'BELO HORIZONTE',
            'uf_rt' => 'MG',
            'nome_mae_rt' => str_replace("'", "", mb_strtoupper($this->faker()->name, 'UTF-8')),
            'nome_pai_rt' => null,
            'titulo_eleitor_rt' => '875698541263',
            'zona_rt' => '321',
            'secao_rt' => '54321',
            'ra_reservista_rt' => '789547896352',
        ];

        foreach($campos as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax', ['preRegistro' => 1]), [
                'classe' => 'pessoaJuridica.responsavelTecnico',
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
    public function view_justifications_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');
            
        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs());
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
                '<a class="nav-link" data-toggle="pill" href="#parte_contato_rt">',
                'Contato / RT&nbsp',
                '<span class="badge badge-danger">',
                '</a>',
            ])
            ->assertSee('value="'. route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo]) .'"');
    }

    /** @test */
    public function view_justifications_text_rt_by_contabilidade()
    {
        $externo = $this->signInAsUserExterno('contabil');

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['preRegistro' => 1]), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');
            
        $this->put(route('externo.inserir.preregistro', ['preRegistro' => 1]))
        ->assertRedirect(route('externo.preregistro.view', ['preRegistro' => 1]));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs());
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
     * TESTES PRE-REGISTRO RESPONSÁVEL TÉCNICO VIA AJAX - ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function can_update_justificativa()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs(), ['registro' => null]));

        $justificativas = array();
        foreach($dados as $campo)
        {
            $texto = $this->faker()->text(500);
            $justificativas[$campo] = $texto;
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $texto
            ])->assertStatus(200);   
            
            $this->assertEquals(PreRegistro::first()->getJustificativaArray(), $justificativas);
            $this->assertEquals(PreRegistro::first()->idusuario, $admin->idusuario);
        }

        $this->assertDatabaseHas('pre_registros', [
            'justificativa' => json_encode($justificativas, JSON_FORCE_OBJECT)
        ]);
    }

    /** @test */
    public function can_update_justificativa_with_status_em_analise_or_analise_da_correcao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs(), ['registro' => null]));

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            if(in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                foreach($dados as $campo)
                    $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs(), ['registro' => null]));

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => ''
            ])->assertStatus(200);    

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs(), ['registro' => null]));

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
        {
            $texto = $this->faker()->text(900);
            $justificativas[$campo] = $texto;
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs(), ['registro' => null]));

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo . '_erro',
                'valor' => $this->faker()->text(500)
            ])->assertSessionHasErrors('campo');
    }

    /** @test */
    public function cannot_update_justificativa_with_wrong_input_acao()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs(), ['registro' => null]));

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs(), ['registro' => null]));

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        foreach(PreRegistro::getStatus() as $status)
        {
            $preRegistroCnpj->preRegistro->update(['status' => $status]);
            if(!in_array($status, [PreRegistro::STATUS_ANALISE_INICIAL, PreRegistro::STATUS_ANALISE_CORRECAO]))
                foreach($dados as $campo)
                    $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
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

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs(), ['registro' => null]));

        foreach($dados as $campo)
        {
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertOk(); 

            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
            $txt = $inicio . 'Usuário (usuário 1) fez a ação de "justificar" o campo "' . $campo . '", ';
            $txt .= 'inserindo ou removendo valor *pré-registro* (id: '.$preRegistroCnpj->preRegistro->id.')';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function can_remove_all_justificativas()
    {
        $admin = $this->signInAsAdmin();
        
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $dados = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs(), ['registro' => null]));

        foreach($dados as $campo)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->preRegistro->id), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(500)
            ])->assertStatus(200);   

        $preRegistroCnpj->preRegistro->update(['status' => PreRegistro::STATUS_ANALISE_CORRECAO]);

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
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
    public function can_save_inputs()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $campos = ['registro' => '000011234'];

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo,
                'valor' => $valor
            ])->assertStatus(200);    

        $this->assertDatabaseHas('responsaveis_tecnicos', $campos);
    }

    /** @test */
    public function log_is_generated_when_save_inputs()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $campos = ['registro' => '000011234'];

        foreach($campos as $campo => $valor)
        {
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo,
                'valor' => $valor
            ])->assertStatus(200);  

            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '['. now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
            $txt = $inicio . 'Usuário (usuário 1) fez a ação de "editar" o campo "' . $campo . '", ';
            $txt .= 'inserindo ou removendo valor *pré-registro* (id: '.$preRegistroCnpj->preRegistro->id.')';
            $this->assertStringContainsString($txt, $log);
        }  

        $this->assertDatabaseHas('responsaveis_tecnicos', $campos);
    }

    /** @test */
    public function can_clean_inputs_saved_after_update()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $campos = ['registro' => '000011234'];

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo,
                'valor' => $valor
            ])->assertStatus(200);    

        $this->assertDatabaseHas('responsaveis_tecnicos', $campos);

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo,
                'valor' => ''
            ])->assertStatus(200);    

        $this->assertDatabaseMissing('responsaveis_tecnicos', $campos);
    }

    /** @test */
    public function cannot_save_input_registro_with_more_than_20_chars()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $campos = ['registro' => '000011234'];

        $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
            'acao' => 'editar',
            'campo' => 'registro',
            'valor' => '000011234541235987532'
        ])->assertSessionHasErrors('valor');    
    }

    /** @test */
    public function cannot_save_inputs_with_wrong_action()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $campos = ['registro' => '000011234'];

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'editar_',
                'campo' => $campo,
                'valor' => $valor
            ])->assertSessionHasErrors('acao');    
    }

    /** @test */
    public function cannot_save_inputs_with_wrong_field()
    {
        $admin = $this->signInAsAdmin();
        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);
        $campos = ['registro' => '000011234'];

        foreach($campos as $campo => $valor)
            $this->post(route('preregistro.update.ajax', $preRegistroCnpj->pre_registro_id), [
                'acao' => 'editar',
                'campo' => $campo . '-',
                'valor' => $valor
            ])->assertSessionHasErrors('campo');     
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

        $rt = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ])->responsavelTecnico;
        
        $this->get(route('preregistro.view', 1))
        ->assertSeeInOrder([
            '<p id="cpf_rt">', ' - CPF: </span>', formataCpfCnpj($rt->cpf),
            '<p id="registro">', ' - Registro: <span class="font-weight-bolder text-danger">*</span></span>', 'placeholder="Registro Obrigatório"',
            '<p id="nome_rt">', ' - Nome Completo: </span>', $rt->nome,
            '<p id="nome_social_rt">', ' - Nome Social: </span>', $rt->nome_social,
            '<p id="dt_nascimento_rt">', ' - Data de Nascimento: </span>', onlyDate($rt->dt_nascimento),
            '<p id="sexo_rt">', ' - Gênero: </span>', $rt->sexo,
            '<p id="tipo_identidade_rt">', ' - Tipo do documento de identidade: </span>', $rt->tipo_identidade,
            '<p id="identidade_rt">', ' - N° do documento de identidade: </span>', $rt->identidade,
            '<p id="orgao_emissor_rt">', ' - Órgão Emissor: </span>', $rt->orgao_emissor,
            '<p id="dt_expedicao_rt">', ' - Data de Expedição: </span>', onlyDate($rt->dt_expedicao),
            '<p id="titulo_eleitor_rt">', ' - Título de Eleitor: </span>', $rt->titulo_eleitor,
            '<p id="zona_rt">', ' - Zona Eleitoral: </span>', $rt->zona,
            '<p id="secao_rt">', ' - Seção Eleitoral: </span>', $rt->secao,
            '<p id="ra_reservista_rt">', ' - RA Reservista: </span>', $rt->ra_reservista,
            '<p id="cep_rt">', ' - CEP: </span>', $rt->cep,
            '<p id="bairro_rt">', ' - Bairro: </span>', $rt->bairro,
            '<p id="logradouro_rt">', ' - Logradouro: </span>', $rt->logradouro,
            '<p id="numero_rt">', ' - Número: </span>', $rt->numero,
            '<p id="complemento_rt">', ' - Complemento: </span>', '-----',
            '<p id="cidade_rt">', ' - Município: </span>', $rt->cidade,
            '<p id="uf_rt">', ' - Estado: </span>', $rt->uf,
            '<p id="nome_mae_rt">', ' - Nome da Mãe: </span>', $rt->nome_mae,
            '<p id="nome_pai_rt">', ' - Nome do Pai: </span>', $rt->nome_pai,
        ]);
    }

    /** @test */
    public function view_text_justificado_rt()
    {
        $admin = $this->signInAsAdmin();

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create()
        ]);

        $keys = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs(), ['registro' => null]));
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $justificativas = $preRegistroCnpj->preRegistro->fresh()->getJustificativaArray();

        $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
        ->assertSeeText($justificativas['cpf_rt'])
        ->assertSeeText($justificativas['registro'])
        ->assertSeeText($justificativas['nome_rt'])
        ->assertSeeText($justificativas['nome_social_rt'])
        ->assertSeeText($justificativas['sexo_rt'])
        ->assertSeeText($justificativas['dt_nascimento_rt'])
        ->assertSeeText($justificativas['cep_rt'])
        ->assertSeeText($justificativas['logradouro_rt'])
        ->assertSeeText($justificativas['numero_rt'])
        ->assertSeeText($justificativas['complemento_rt'])
        ->assertSeeText($justificativas['bairro_rt'])
        ->assertSeeText($justificativas['cidade_rt'])
        ->assertSeeText($justificativas['uf_rt'])
        ->assertSeeText($justificativas['nome_mae_rt'])
        ->assertSeeText($justificativas['nome_pai_rt'])
        ->assertSeeText($justificativas['tipo_identidade_rt'])
        ->assertSeeText($justificativas['identidade_rt'])
        ->assertSeeText($justificativas['orgao_emissor_rt'])
        ->assertSeeText($justificativas['dt_expedicao_rt'])
        ->assertSeeText($justificativas['titulo_eleitor_rt'])
        ->assertSeeText($justificativas['zona_rt'])
        ->assertSeeText($justificativas['secao_rt'])
        ->assertSeeText($justificativas['ra_reservista_rt']);
    }

    /** @test */
    public function view_justifications_text_rt_by_url()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['checkPreRegistro' => 'on']), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs(), ['registro' => null]));
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
    public function view_historico_justificativas_rt()
    {
        $externo = $this->signInAsUserExterno('user_externo', factory('App\UserExterno')->states('pj')->create());

        $preRegistroCnpj = factory('App\PreRegistroCnpj')->create();

        $this->put(route('externo.verifica.inserir.preregistro', ['checkPreRegistro' => 'on']), ['pergunta' => "25 meses"])
        ->assertViewIs('site.userExterno.inserir-pre-registro');

        $this->put(route('externo.inserir.preregistro'))
        ->assertRedirect(route('externo.preregistro.view'));

        $admin = $this->signIn(PreRegistro::first()->user);

        $keys = array_keys(array_merge(PreRegistro::first()->pessoaJuridica->responsavelTecnico->arrayValidacaoInputs(), ['registro' => null]));
        foreach($keys as $campo)
            $this->post(route('preregistro.update.ajax', 1), [
                'acao' => 'justificar',
                'campo' => $campo,
                'valor' => $this->faker()->text(100)
            ])->assertStatus(200);

        $this->put(route('preregistro.update.status', 1), ['situacao' => 'corrigir']);
        $data_hora = now()->format('Y-m-d H:i:s');

        foreach($keys as $campo)
            $this->get(route('preregistro.view', $preRegistroCnpj->preRegistro->id))
            ->assertSee('value="'.route('externo.preregistro.justificativa.view', ['preRegistro' => 1, 'campo' => $campo, 'data_hora' => urlencode($data_hora)]).'"');
    }

    /** @test */
    public function view_label_campo_alterado_rt()
    {
        $this->filled_campos_editados_rt_when_form_is_submitted_when_status_aguardando_correcao();
        
        $admin = $this->signIn(PreRegistro::first()->user);

        $camposEditados = json_decode(PreRegistro::first()->campos_editados, true);

        $this->get(route('preregistro.view', 1))
        ->assertSeeInOrder([
            '<a class="card-link" data-toggle="collapse" href="#parte_contato_rt">',
            '<div class="card-header bg-secondary text-center text-uppercase font-weight-bolder menuPR">',
            '4. Contato / RT',
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
    public function view_label_justificado_rt()
    {
        $this->view_text_justificado_rt();

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
