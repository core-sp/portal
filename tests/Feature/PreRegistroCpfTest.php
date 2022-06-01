<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\PreRegistro;

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
        
        $this->assertDatabaseHas('pre_registros_cpf', [
            'nome_social' => $preRegistroCpf['nome_social'],
            'dt_nascimento' => $preRegistroCpf['dt_nascimento'],
            'sexo' => $preRegistroCpf['sexo'],
            'estado_civil' => $preRegistroCpf['estado_civil'],
            'naturalidade' => $preRegistroCpf['naturalidade'],
            'nacionalidade' => $preRegistroCpf['nacionalidade'],
            'nome_mae' => $preRegistroCpf['nome_mae'],
            'nome_pai' => $preRegistroCpf['nome_pai'],
            'identidade' => $preRegistroCpf['identidade'],
            'orgao_emissor' => $preRegistroCpf['orgao_emissor'],
            'dt_expedicao' => $preRegistroCpf['dt_expedicao'],
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
    }

    /** @test */
    public function can_create_anexos_pf_by_ajax()
    {
        $externo = $this->signInAsUserExterno();

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
        
        $this->assertDatabaseMissing('pre_registros_cpf', [
            'nome_social' => $preRegistroCpf['nome_social'],
            'dt_nascimento' => $preRegistroCpf['dt_nascimento'],
            'sexo' => $preRegistroCpf['sexo'],
            'estado_civil' => $preRegistroCpf['estado_civil'],
            'naturalidade' => $preRegistroCpf['naturalidade'],
            'nacionalidade' => $preRegistroCpf['nacionalidade'],
            'nome_mae' => $preRegistroCpf['nome_mae'],
            'nome_pai' => $preRegistroCpf['nome_pai'],
            'identidade' => $preRegistroCpf['identidade'],
            'orgao_emissor' => $preRegistroCpf['orgao_emissor'],
            'dt_expedicao' => $preRegistroCpf['dt_expedicao'],
        ]);
    }
    
    /** @test */
    public function cannot_create_anexos_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno();

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
