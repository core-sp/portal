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
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $preRegistro = $externo->load('preRegistro')->preRegistro;

        $this->assertDatabaseHas('pre_registros', [
            'id' => $preRegistro->id,
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'id' => $preRegistro->pessoaJuridica->id,
        ]);
    }

    /** @test */
    public function can_update_table_pre_registros_cnpj_by_ajax()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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
    public function cannot_update_table_pre_registros_cnpj_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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
    public function cannot_update_table_pre_registros_cnpj_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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
    public function cannot_update_table_pre_registros_cnpj_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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
    public function cannot_update_table_pre_registros_cnpj_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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
    public function cannot_update_table_pre_registros_cnpj_by_ajax_with_input_type_text_more_191_chars()
    {
        $faker = \Faker\Factory::create();
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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
    public function cannot_update_table_pre_registro_cnpj_by_ajax_with_dt_inicio_atividade_after_today()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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
    public function cannot_update_table_pre_registro_cnpj_by_ajax_without_data_type()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

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
    public function cannot_update_table_responsaveis_tecnicos_by_ajax_without_data_type()
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
}
