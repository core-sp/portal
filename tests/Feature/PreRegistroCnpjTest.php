<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\PreRegistro;

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
        
        $this->assertDatabaseHas('pre_registros_cnpj', [
            'razao_social' => $preRegistroCnpj['razao_social'],
            'nire' => $preRegistroCnpj['nire'],
            'tipo_empresa' => $preRegistroCnpj['tipo_empresa'],
            'dt_inicio_atividade' => $preRegistroCnpj['dt_inicio_atividade'],
            'inscricao_municipal' => $preRegistroCnpj['inscricao_municipal'],
            'inscricao_estadual' => $preRegistroCnpj['inscricao_estadual'],
            'capital_social' => $preRegistroCnpj['capital_social'],
            'cep' => $preRegistroCnpj['cep'],
            'logradouro' => $preRegistroCnpj['logradouro'],
            'numero' => $preRegistroCnpj['numero'],
            'complemento' => $preRegistroCnpj['complemento'],
            'bairro' => $preRegistroCnpj['bairro'],
            'cidade' => $preRegistroCnpj['cidade'],
            'uf' => $preRegistroCnpj['uf'],
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
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
        
        $this->assertDatabaseHas('responsaveis_tecnicos', [
            'cpf' => $rt['cpf'],
            'registro' => $rt['registro'],
            'nome' => $rt['nome'],
            'nome_social' => $rt['nome_social'],
            'sexo' => $rt['sexo'],
            'dt_nascimento' => $rt['dt_nascimento'],
            'cep' => $rt['cep'],
            'logradouro' => $rt['logradouro'],
            'numero' => $rt['numero'],
            'complemento' => $rt['complemento'],
            'bairro' => $rt['bairro'],
            'cidade' => $rt['cidade'],
            'uf' => $rt['uf'],
            'nome_mae' => $rt['nome_mae'],
            'nome_pai' => $rt['nome_pai'],
            'identidade' => $rt['identidade'],
            'orgao_emissor' => $rt['orgao_emissor'],
            'dt_expedicao' => $rt['dt_expedicao'],
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function can_create_anexos_pj_by_ajax()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $anexos = [
            UploadedFile::fake()->image('random.jpg'),
            UploadedFile::fake()->image('random1.png'),
            UploadedFile::fake()->image('random2.jpeg'),
        ];
        
        foreach($anexos as $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path',
                'valor' => $value
            ])->assertStatus(200);

        $this->assertDatabaseHas('anexos', [
            'nome_original' => 'random.jpg',
            'nome_original' => 'random1.png',
            'nome_original' => 'random2.jpeg',
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
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
        
        $this->assertDatabaseMissing('pre_registros_cnpj', [
            'razao_social' => $preRegistroCnpj['razao_social'],
            'nire' => $preRegistroCnpj['nire'],
            'tipo_empresa' => $preRegistroCnpj['tipo_empresa'],
            'dt_inicio_atividade' => $preRegistroCnpj['dt_inicio_atividade'],
            'inscricao_municipal' => $preRegistroCnpj['inscricao_municipal'],
            'inscricao_estadual' => $preRegistroCnpj['inscricao_estadual'],
            'capital_social' => $preRegistroCnpj['capital_social'],
            'cep' => $preRegistroCnpj['cep'],
            'logradouro' => $preRegistroCnpj['logradouro'],
            'numero' => $preRegistroCnpj['numero'],
            'complemento' => $preRegistroCnpj['complemento'],
            'bairro' => $preRegistroCnpj['bairro'],
            'cidade' => $preRegistroCnpj['cidade'],
            'uf' => $preRegistroCnpj['uf'],
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
        
        $this->assertDatabaseMissing('responsaveis_tecnicos', [
            'cpf' => $rt['cpf'],
            'registro' => $rt['registro'],
            'nome' => $rt['nome'],
            'nome_social' => $rt['nome_social'],
            'sexo' => $rt['sexo'],
            'dt_nascimento' => $rt['dt_nascimento'],
            'cep' => $rt['cep'],
            'logradouro' => $rt['logradouro'],
            'numero' => $rt['numero'],
            'complemento' => $rt['complemento'],
            'bairro' => $rt['bairro'],
            'cidade' => $rt['cidade'],
            'uf' => $rt['uf'],
            'nome_mae' => $rt['nome_mae'],
            'nome_pai' => $rt['nome_pai'],
            'identidade' => $rt['identidade'],
            'orgao_emissor' => $rt['orgao_emissor'],
            'dt_expedicao' => $rt['dt_expedicao'],
        ]);

        $this->assertDatabaseHas('pre_registros_cnpj', [
            'responsavel_tecnico_id' => PreRegistro::first()->pessoaJuridica->responsavel_tecnico_id
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '06985713000138'
        ]));

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $anexos = [
            UploadedFile::fake()->image('random.jpg'),
            UploadedFile::fake()->image('random1.png'),
            UploadedFile::fake()->image('random2.jpeg'),
        ];
        
        foreach($anexos as $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'anexos',
                'campo' => 'path_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'nome_original' => 'random1.png',
            'nome_original' => 'random2.jpeg',
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
    }
}
