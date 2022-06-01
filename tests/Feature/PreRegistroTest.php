<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\PreRegistro;

class PreRegistroTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
                
        $this->get(route('externo.preregistro.view'))->assertRedirect(route('externo.login'));
        $this->get(route('externo.inserir.preregistro.view'))->assertRedirect(route('externo.login'));
        $this->put(route('externo.inserir.preregistro'))->assertRedirect(route('externo.login'));
        $this->post(route('externo.inserir.preregistro.ajax'))->assertRedirect(route('externo.login'));
        $this->get(route('externo.preregistro.anexo.download', 1))->assertRedirect(route('externo.login'));
        $this->delete(route('externo.preregistro.anexo.excluir', 1))->assertRedirect(route('externo.login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $externo = $this->signInAsUserExterno();

        $preRegistro = factory('App\PreRegistro')->raw([
            'id' => 1,
            'user_externo_id' => null,
        ]);
        $preRegistroCpf = factory('App\PreRegistroCpf')->raw([
            'pre_registro_id' => $preRegistro['id'],
        ]);
        $anexo = factory('App\Anexo')->raw([
            'pre_registro_id' => $preRegistro['id'],
            'path' => '/fake/qwertyuiop.jpg'
        ]);

        $dados = array_merge($preRegistro, $preRegistroCpf, $anexo);

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'contabil',
            'campo' => 'nome_contabil',
            'valor' => 'Teste Teste'
        ])->assertStatus(401);

        $this->put(route('externo.inserir.preregistro'), $dados)->assertStatus(302);
        $this->get(route('externo.preregistro.anexo.download', 1))->assertStatus(401);
        $this->delete(route('externo.preregistro.anexo.excluir', 1))->assertStatus(401);
    }

    /** @test */
    public function registered_users_cannot_create_pre_registro()
    {
        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '11748345000144'
        ]));

        $this->get(route('externo.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $this->get(route('externo.inserir.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $externo = $this->signInAsUserExterno(factory('App\UserExterno')->create([
            'cpf_cnpj' => '86294373085'
        ]));

        $this->get(route('externo.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');

        $this->get(route('externo.inserir.preregistro.view'))
        ->assertSeeText('Você já possui registro ativo no Core-SP: ');
    }

    /** @test */
    public function can_update_table_pre_registros_by_ajax()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $preRegistro = factory('App\PreRegistro')->raw([
            'user_externo_id' => $externo->id,
        ]);

        $preRegistro['tipo_telefone_1'] = tipos_contatos()[1];
        $preRegistro['telefone_1'] = '(11) 99999-8888';

        unset($preRegistro['user_externo_id']);
        unset($preRegistro['contabil_id']);
        unset($preRegistro['idusuario']);
        unset($preRegistro['status']);
        unset($preRegistro['justificativa']);
        
        foreach($preRegistro as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => $key,
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('pre_registros', [
            'ramo_atividade' => $preRegistro['ramo_atividade'], 
            'segmento' => $preRegistro['segmento'],
            'registro_secundario' => $preRegistro['registro_secundario'],
            'cep' => $preRegistro['cep'],
            'logradouro' => $preRegistro['logradouro'],
            'numero' => $preRegistro['numero'],
            'complemento' => $preRegistro['complemento'],
            'bairro' => $preRegistro['bairro'],
            'cidade' => $preRegistro['cidade'],
            'uf' => $preRegistro['uf'],
            'telefone' => $preRegistro['telefone'] . $preRegistro['telefone_1'],
            'tipo_telefone' => $preRegistro['tipo_telefone'] . $preRegistro['tipo_telefone_1'],
            'idregional' => $preRegistro['idregional']
        ]);
    }

    /** @test */
    public function can_update_table_contabeis_by_ajax()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => $key.'_contabil',
                'valor' => $value
            ])->assertStatus(200);
        
        $this->assertDatabaseHas('contabeis', [
            'cnpj' => $contabil['cnpj'],
            'nome' => $contabil['nome'],
            'email' => $contabil['email'],
            'nome_contato' => $contabil['nome_contato'],
            'telefone' => $contabil['telefone'],
        ]);

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function can_create_anexos_by_ajax()
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
    public function cannot_update_table_pre_registros_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $preRegistro = factory('App\PreRegistro')->raw([
            'user_externo_id' => $externo->id,
        ]);

        $preRegistro['tipo_telefone_1'] = tipos_contatos()[1];
        $preRegistro['telefone_1'] = '(11) 99999-8888';

        unset($preRegistro['user_externo_id']);
        unset($preRegistro['contabil_id']);
        unset($preRegistro['idusuario']);
        unset($preRegistro['status']);
        unset($preRegistro['justificativa']);
        
        foreach($preRegistro as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');

        $this->assertDatabaseMissing('pre_registros', [
            'ramo_atividade' => $preRegistro['ramo_atividade'], 
            'segmento' => $preRegistro['segmento'],
            'registro_secundario' => $preRegistro['registro_secundario'],
            'cep' => $preRegistro['cep'],
            'logradouro' => $preRegistro['logradouro'],
            'numero' => $preRegistro['numero'],
            'complemento' => $preRegistro['complemento'],
            'bairro' => $preRegistro['bairro'],
            'cidade' => $preRegistro['cidade'],
            'uf' => $preRegistro['uf'],
            'telefone' => $preRegistro['telefone'] . $preRegistro['telefone_1'],
            'tipo_telefone' => $preRegistro['tipo_telefone'] . $preRegistro['tipo_telefone_1'],
            'idregional' => $preRegistro['idregional']
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_wrong_input_name()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => $key.'_erro',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('contabeis', [
            'cnpj' => $contabil['cnpj'],
            'nome' => $contabil['nome'],
            'email' => $contabil['email'],
            'nome_contato' => $contabil['nome_contato'],
            'telefone' => $contabil['telefone'],
        ]);

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
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

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $preRegistro = factory('App\PreRegistro')->raw([
            'user_externo_id' => $externo->id,
        ]);

        $preRegistro['tipo_telefone_1'] = tipos_contatos()[1];
        $preRegistro['telefone_1'] = '(11) 99999-8888';

        unset($preRegistro['user_externo_id']);
        unset($preRegistro['contabil_id']);
        unset($preRegistro['idusuario']);
        unset($preRegistro['status']);
        unset($preRegistro['justificativa']);
        
        foreach($preRegistro as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');

        $this->assertDatabaseMissing('pre_registros', [
            'ramo_atividade' => $preRegistro['ramo_atividade'], 
            'segmento' => $preRegistro['segmento'],
            'registro_secundario' => $preRegistro['registro_secundario'],
            'cep' => $preRegistro['cep'],
            'logradouro' => $preRegistro['logradouro'],
            'numero' => $preRegistro['numero'],
            'complemento' => $preRegistro['complemento'],
            'bairro' => $preRegistro['bairro'],
            'cidade' => $preRegistro['cidade'],
            'uf' => $preRegistro['uf'],
            'telefone' => $preRegistro['telefone'] . $preRegistro['telefone_1'],
            'tipo_telefone' => $preRegistro['tipo_telefone'] . $preRegistro['tipo_telefone_1'],
            'idregional' => $preRegistro['idregional']
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_without_classe()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => '',
                'campo' => $key.'_contabil',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('contabeis', [
            'cnpj' => $contabil['cnpj'],
            'nome' => $contabil['nome'],
            'email' => $contabil['email'],
            'nome_contato' => $contabil['nome_contato'],
            'telefone' => $contabil['telefone'],
        ]);

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_without_classe()
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
                'classe' => '',
                'campo' => 'path',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'nome_original' => 'random1.png',
            'nome_original' => 'random2.jpeg',
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $preRegistro = factory('App\PreRegistro')->raw([
            'user_externo_id' => $externo->id,
        ]);

        $preRegistro['tipo_telefone_1'] = tipos_contatos()[1];
        $preRegistro['telefone_1'] = '(11) 99999-8888';

        unset($preRegistro['user_externo_id']);
        unset($preRegistro['contabil_id']);
        unset($preRegistro['idusuario']);
        unset($preRegistro['status']);
        unset($preRegistro['justificativa']);
        
        foreach($preRegistro as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro_erro',
                'campo' => $key,
                'valor' => $value
            ])->assertSessionHasErrors('classe');

        $this->assertDatabaseMissing('pre_registros', [
            'ramo_atividade' => $preRegistro['ramo_atividade'], 
            'segmento' => $preRegistro['segmento'],
            'registro_secundario' => $preRegistro['registro_secundario'],
            'cep' => $preRegistro['cep'],
            'logradouro' => $preRegistro['logradouro'],
            'numero' => $preRegistro['numero'],
            'complemento' => $preRegistro['complemento'],
            'bairro' => $preRegistro['bairro'],
            'cidade' => $preRegistro['cidade'],
            'uf' => $preRegistro['uf'],
            'telefone' => $preRegistro['telefone'] . $preRegistro['telefone_1'],
            'tipo_telefone' => $preRegistro['tipo_telefone'] . $preRegistro['tipo_telefone_1'],
            'idregional' => $preRegistro['idregional']
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_wrong_classe()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil_erro',
                'campo' => $key.'_contabil',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('contabeis', [
            'cnpj' => $contabil['cnpj'],
            'nome' => $contabil['nome'],
            'email' => $contabil['email'],
            'nome_contato' => $contabil['nome_contato'],
            'telefone' => $contabil['telefone'],
        ]);

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_wrong_classe()
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
                'classe' => 'anexos_erro',
                'campo' => 'path',
                'valor' => $value
            ])->assertSessionHasErrors('classe');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'nome_original' => 'random1.png',
            'nome_original' => 'random2.jpeg',
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $preRegistro = factory('App\PreRegistro')->raw([
            'user_externo_id' => $externo->id,
        ]);

        $preRegistro['tipo_telefone_1'] = tipos_contatos()[1];
        $preRegistro['telefone_1'] = '(11) 99999-8888';

        unset($preRegistro['user_externo_id']);
        unset($preRegistro['contabil_id']);
        unset($preRegistro['idusuario']);
        unset($preRegistro['status']);
        unset($preRegistro['justificativa']);
        
        foreach($preRegistro as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'preRegistro',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');

        $this->assertDatabaseMissing('pre_registros', [
            'ramo_atividade' => $preRegistro['ramo_atividade'], 
            'segmento' => $preRegistro['segmento'],
            'registro_secundario' => $preRegistro['registro_secundario'],
            'cep' => $preRegistro['cep'],
            'logradouro' => $preRegistro['logradouro'],
            'numero' => $preRegistro['numero'],
            'complemento' => $preRegistro['complemento'],
            'bairro' => $preRegistro['bairro'],
            'cidade' => $preRegistro['cidade'],
            'uf' => $preRegistro['uf'],
            'telefone' => $preRegistro['telefone'] . $preRegistro['telefone_1'],
            'tipo_telefone' => $preRegistro['tipo_telefone'] . $preRegistro['tipo_telefone_1'],
            'idregional' => $preRegistro['idregional']
        ]);
    }

    /** @test */
    public function cannot_update_table_contabeis_by_ajax_without_campo()
    {
        $externo = $this->signInAsUserExterno();

        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $contabil = factory('App\Contabil')->raw();
        
        foreach($contabil as $key => $value)
            $this->post(route('externo.inserir.preregistro.ajax'), [
                'classe' => 'contabil',
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('contabeis', [
            'cnpj' => $contabil['cnpj'],
            'nome' => $contabil['nome'],
            'email' => $contabil['email'],
            'nome_contato' => $contabil['nome_contato'],
            'telefone' => $contabil['telefone'],
        ]);

        $this->assertDatabaseHas('pre_registros', [
            'contabil_id' => PreRegistro::first()->contabil_id
        ]);
    }

    /** @test */
    public function cannot_create_anexos_by_ajax_without_campo()
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
                'campo' => '',
                'valor' => $value
            ])->assertSessionHasErrors('campo');
        
        $this->assertDatabaseMissing('anexos', [
            'nome_original' => 'random.jpg',
            'nome_original' => 'random1.png',
            'nome_original' => 'random2.jpeg',
            'pre_registro_id' => $externo->load('preRegistro')->preRegistro->id
        ]);
    }

    /** @test */
    public function cannot_update_table_pre_registros_by_ajax_with_wrong_idregional()
    {
        $externo = $this->signInAsUserExterno();
        $this->get(route('externo.inserir.preregistro.view'))->assertOk();

        $this->post(route('externo.inserir.preregistro.ajax'), [
            'classe' => 'preRegistro',
            'campo' => 'idregional',
            'valor' => 55
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('pre_registros', [
            'idregional' => null
        ]);
    }
}
