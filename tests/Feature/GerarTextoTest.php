<?php

namespace Tests\Feature;

use App\GerarTexto;
use App\Permissao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class GerarTextoTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * =======================================================================================================
     * TESTES NO ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $texto = factory('App\GerarTexto')->create();

        $this->get(route('textos.view', $texto->tipo_doc))->assertRedirect(route('login'));
        $this->post(route('textos.create', $texto->tipo_doc))->assertRedirect(route('login'));
        $this->post(route('textos.update.campos', [$texto->tipo_doc, $texto->id]))->assertRedirect(route('login'));
        $this->post(route('textos.publicar', $texto->tipo_doc))->assertRedirect(route('login'));
        $this->delete(route('textos.delete', [$texto->tipo_doc, $texto->id]))->assertRedirect(route('login'));
        $this->put(route('textos.update.indice', $texto->tipo_doc))->assertRedirect(route('login'));
    }

    // /** @test */
    // public function non_authorized_users_cannot_access_links()
    // {
    //     $this->signIn();
    //     $this->assertAuthenticated('web');
        
    //     $licitacao = factory('App\Licitacao')->create();
    //     $licitacao->datarealizacao = Carbon::create($licitacao->datarealizacao)->format('Y-m-d H:i');

    //     $this->get(route('licitacoes.index'))->assertForbidden();
    //     $this->get(route('licitacoes.create'))->assertForbidden();
    //     $this->get(route('licitacoes.edit', $licitacao->idlicitacao))->assertForbidden();
    //     $this->post(route('licitacoes.store'), $licitacao->toArray())->assertForbidden();
    //     $this->patch(route('licitacoes.update', $licitacao->idlicitacao), $licitacao->toArray())->assertForbidden();
    //     $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao))->assertForbidden();
    //     $this->get(route('licitacoes.restore', $licitacao->idlicitacao))->assertForbidden();
    //     $this->get(route('licitacoes.busca'))->assertForbidden();
    //     $this->get(route('licitacoes.trashed'))->assertForbidden();
    // }

    /** @test */
    public function texto_can_be_created_by_an_user()
    {
        $user = $this->signInAsAdmin();
        $tipo = array_keys(GerarTexto::tipos_doc())[0];

        $this->get(route('textos.view', $tipo))->assertOk();
        $this->post(route('textos.create', $tipo))
        ->assertRedirect(route('textos.view', $tipo));

        $this->get(route('textos.view', $tipo))
        ->assertSee(GerarTexto::first()->tipo)
        ->assertSee(GerarTexto::first()->texto_tipo);

        $this->assertDatabaseHas('gerar_textos', GerarTexto::first()->toArray());
    }

    /** @test */
    public function texto_cannot_be_created_with_tipo_invalid()
    {
        $user = $this->signInAsAdmin();
        $tipo = 'teste';

        $this->get(route('textos.view', $tipo))
        ->assertStatus(404);
        $this->post(route('textos.create', $tipo))
        ->assertStatus(404);

        $this->assertDatabaseMissing('gerar_textos', ['tipo' => 'Título']);
    }

    /** @test */
    public function textos_can_be_created_by_an_user()
    {
        $user = $this->signInAsAdmin();
        $tipo = array_keys(GerarTexto::tipos_doc())[0];

        for($cont = 0; $cont < 5; $cont++){
            $this->get(route('textos.view', $tipo))->assertOk();
            $this->post(route('textos.create', $tipo))
            ->assertRedirect(route('textos.view', $tipo));
    
            $this->get(route('textos.view', $tipo))
            ->assertSee(GerarTexto::all()->get($cont)->conteudo);
    
            $this->assertDatabaseHas('gerar_textos', GerarTexto::all()->get($cont)->toArray());
        }

        $this->assertEquals(5, GerarTexto::count());
    }

    /** @test */
    public function log_is_generated_when_texto_is_created()
    {
        $user = $this->signInAsAdmin();
        $tipo = array_keys(GerarTexto::tipos_doc())[0];
        
        $this->post(route('textos.create', $tipo));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') criou *novo texto do documento '.$tipo.'* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function texto_is_shown_on_admin_panel_after_its_creation()
    {
        $this->signInAsAdmin();
        $tipo = array_keys(GerarTexto::tipos_doc())[0];
        $txt = factory('App\GerarTexto')->create();
        
        $this->get(route('textos.view', $tipo))
            ->assertSeeText($txt->texto_tipo)
            ->assertSeeText($txt->tipo)
            ->assertSeeText($txt->conteudo)
            ->assertSeeText($txt->nivel);
    }

    /** @test */
    public function texto_can_be_updated_by_an_user()
    {
        $user = $this->signInAsAdmin();
        $texto = factory('App\GerarTexto')->create();
        $dados = $texto->toArray();
        $dados['com_numeracao'] = true;
        $dados['tipo'] = 'Subtítulo';
        $dados['texto_tipo'] = 'Teste do update';
        $dados['nivel'] = 1;

        $this->get(route('textos.view', $texto->tipo_doc))->assertOk();
        $this->post(route('textos.update.campos', [$texto->tipo_doc, $texto->id]), $dados)
        ->assertJsonFragment([true]);

        $this->assertDatabaseHas('gerar_textos', GerarTexto::first()->toArray());
        $this->assertDatabaseMissing('gerar_textos', $texto->toArray());
    }

    /** @test */
    public function texto_cannot_be_updated_without_input_tipo()
    {
        $user = $this->signInAsAdmin();
        $texto = factory('App\GerarTexto')->create()->toArray();
        $texto['tipo'] = null;

        $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
        $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
        ->assertSessionHasErrors([
            'tipo'
        ]);
    }

    /** @test */
    public function texto_cannot_be_updated_with_tipo_invalid()
    {
        $user = $this->signInAsAdmin();
        $texto = factory('App\GerarTexto')->create()->toArray();
        $texto['tipo'] = 'Teste';

        $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
        $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
        ->assertSessionHasErrors([
            'tipo'
        ]);
    }

    /** @test */
    public function texto_cannot_be_updated_without_input_texto_tipo()
    {
        $user = $this->signInAsAdmin();
        $texto = factory('App\GerarTexto')->create()->toArray();
        $texto['texto_tipo'] = null;

        $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
        $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
        ->assertSessionHasErrors([
            'texto_tipo'
        ]);
    }

    /** @test */
    public function texto_cannot_be_updated_with_texto_tipo_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();
        $texto = factory('App\GerarTexto')->create()->toArray();
        $texto['texto_tipo'] = $faker->sentence(400);

        $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
        $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
        ->assertSessionHasErrors([
            'texto_tipo'
        ]);
    }

    /** @test */
    public function texto_cannot_be_updated_without_input_nivel()
    {
        $user = $this->signInAsAdmin();
        $texto = factory('App\GerarTexto')->create()->toArray();
        $texto['nivel'] = null;

        $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
        $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
        ->assertSessionHasErrors([
            'nivel'
        ]);
    }

    /** @test */
    public function texto_cannot_be_updated_with_nivel_invalid()
    {
        $user = $this->signInAsAdmin();
        $texto = factory('App\GerarTexto')->create()->toArray();
        $texto['nivel'] = 1;

        $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
        $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
        ->assertSessionHasErrors([
            'nivel'
        ]);

        GerarTexto::first()->update([
            'tipo' => 'Subtítulo'
        ]);
        $texto = GerarTexto::first()->fresh()->toArray();
        $texto['nivel'] = 0;

        $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
        $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
        ->assertSessionHasErrors([
            'nivel'
        ]);
    }

    /** @test */
    public function texto_cannot_be_updated_without_input_com_numeracao()
    {
        $user = $this->signInAsAdmin();
        $texto = factory('App\GerarTexto')->create()->toArray();
        $texto['com_numeracao'] = null;

        $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
        $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
        ->assertSessionHasErrors([
            'com_numeracao'
        ]);
    }

    /** @test */
    public function texto_cannot_be_updated_with_com_numeracao_invalid()
    {
        $user = $this->signInAsAdmin();
        $texto = factory('App\GerarTexto')->create()->toArray();
        $texto['com_numeracao'] = 2;

        $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
        $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
        ->assertSessionHasErrors([
            'com_numeracao'
        ]);

        GerarTexto::first()->update([
            'tipo' => 'Subtítulo'
        ]);
        $texto = GerarTexto::first()->fresh()->toArray();
        $texto['com_numeracao'] = 0;

        $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
        $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
        ->assertSessionHasErrors([
            'com_numeracao'
        ]);
    }

    /** @test */
    public function log_is_generated_when_texto_is_updated()
    {
        $user = $this->signInAsAdmin();
        $texto = factory('App\GerarTexto')->create();
        $dados = $texto->toArray();
        $dados['com_numeracao'] = true;

        $this->get(route('textos.view', $texto->tipo_doc))->assertOk();
        $this->post(route('textos.update.campos', [$texto->tipo_doc, $texto->id]), $dados);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') atualizou *campos do texto do documento '.$texto->tipo_doc.'* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function texto_can_be_delete()
    {
        $user = $this->signInAsAdmin();
        $textos = factory('App\GerarTexto', 2)->create();

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))->assertOk();
        $this->delete(route('textos.delete', [$textos->get(0)->tipo_doc, $textos->get(0)->id]))
        ->assertJsonFragment([true]);

        $this->assertDatabaseMissing('gerar_textos', $textos->get(0)->toArray());
    }

    /** @test */
    public function texto_cannot_be_delete_when_only_one()
    {
        $user = $this->signInAsAdmin();
        $texto = factory('App\GerarTexto')->create();

        $this->get(route('textos.view', $texto->tipo_doc))->assertOk();
        $this->delete(route('textos.delete', [$texto->tipo_doc, $texto->id]))
        ->assertJsonFragment(["Deve existir no mínimo um texto."]);

        $this->assertDatabaseHas('gerar_textos', $texto->toArray());
    }

    /** @test */
    public function can_be_published()
    {
        $user = $this->signInAsAdmin();
        $textos = factory('App\GerarTexto', 2)->create();

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))->assertOk();
        $this->post(route('textos.publicar', $textos->get(0)->tipo_doc), ['publicar' => 1])
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Foi publicada no site com sucesso!')
        ->assertRedirect(route('textos.view', $textos->get(0)->tipo_doc));

        $this->assertDatabaseHas('gerar_textos', ['publicar' => 1]);
    }

    /** @test */
    public function can_be_not_published()
    {
        $user = $this->signInAsAdmin();
        $textos = factory('App\GerarTexto', 2)->create();

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))->assertOk();
        $this->post(route('textos.publicar', $textos->get(0)->tipo_doc), ['publicar' => 0])
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Foi revertida a publicação no site com sucesso!')
        ->assertRedirect(route('textos.view', $textos->get(0)->tipo_doc));

        $this->assertDatabaseHas('gerar_textos', ['publicar' => 0]);
    }

    /** @test */
    public function cannot_be_published_without_input_publicar()
    {
        $user = $this->signInAsAdmin();
        $textos = factory('App\GerarTexto', 2)->create();

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))->assertOk();
        $this->post(route('textos.publicar', $textos->get(0)->tipo_doc), ['publicar' => null])
        ->assertSessionHasErrors([
            'publicar'
        ]);
    }

    /** @test */
    public function cannot_be_published_with_publicar_not_boolean()
    {
        $user = $this->signInAsAdmin();
        $textos = factory('App\GerarTexto', 2)->create();

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))->assertOk();
        $this->post(route('textos.publicar', $textos->get(0)->tipo_doc), ['publicar' => 3])
        ->assertSessionHasErrors([
            'publicar'
        ]);
    }

    /** @test */
    public function show_on_portal_after_published()
    {
        $user = $this->signInAsAdmin();
        $textos = factory('App\GerarTexto', 2)->create();

        $this->get(route('carta-servicos'))
        ->assertSeeText('Ainda não consta a publicação atual.')
        ->assertDontSee('<option value="'.$textos->get(0)->id.'">');

        $this->post(route('textos.publicar', $textos->get(0)->tipo_doc), ['publicar' => 1]);

        $this->get(route('carta-servicos'))
        ->assertDontSeeText('Ainda não consta a publicação atual.')
        ->assertSee('<option value="'.$textos->get(0)->id.'">');
    }

    /** @test */
    public function can_be_update_indice_only_titulo()
    {
        $user = $this->signInAsAdmin();
        $textos = factory('App\GerarTexto', 5)->create();
        $dados = array();
        foreach($textos as $key => $val)
            $dados['id-'.$val->id] = $val->id;

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))->assertOk();
        $this->put(route('textos.update.indice', $textos->get(0)->tipo_doc), array_reverse($dados))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Índice atualizada com sucesso!')
        ->assertRedirect(route('textos.view', $textos->get(0)->tipo_doc));

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))
        ->assertSeeText($textos->get(0)->indice . '. '.$textos->get(0)->texto_tipo)
        ->assertSeeText($textos->get(1)->indice . '. '.$textos->get(1)->texto_tipo)
        ->assertSeeText($textos->get(2)->indice . '. '.$textos->get(2)->texto_tipo)
        ->assertSeeText($textos->get(3)->indice . '. '.$textos->get(3)->texto_tipo)
        ->assertSeeText($textos->get(4)->indice . '. '.$textos->get(4)->texto_tipo);

        $this->assertDatabaseHas('gerar_textos', [
            'indice' => '1', 'indice' => '2', 'indice' => '3', 'indice' => '4', 'indice' => '5',
            'ordem' => '1', 'ordem' => '2', 'ordem' => '3', 'ordem' => '4', 'ordem' => '5'
        ]);
    }

    /** @test */
    public function can_be_update_indice_titulo_and_subtitulo()
    {
        $user = $this->signInAsAdmin();
        $textos = factory('App\GerarTexto', 5)
        ->create()
        ->each(function ($texto) {
            $t = $texto->id % 2;
            if($t == 0)
                $texto->update([
                    'tipo' => 'Subtítulo',
                    'nivel' => 1,
                    'com_numeracao' => 1
                ]);
        });

        $dados = array();
        foreach($textos as $key => $val)
            $dados['id-'.$val->id] = $val->id;

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))->assertOk();
        $this->put(route('textos.update.indice', $textos->get(0)->tipo_doc), array_reverse($dados))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Índice atualizada com sucesso!')
        ->assertRedirect(route('textos.view', $textos->get(0)->tipo_doc));

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))
        ->assertSeeText($textos->get(0)->indice . '. '.$textos->get(0)->texto_tipo)
        ->assertSeeText($textos->get(1)->indice . ' - '.$textos->get(1)->texto_tipo)
        ->assertSeeText($textos->get(2)->indice . '. '.$textos->get(2)->texto_tipo)
        ->assertSeeText($textos->get(3)->indice . ' - '.$textos->get(3)->texto_tipo)
        ->assertSeeText($textos->get(4)->indice . '. '.$textos->get(4)->texto_tipo);

        $this->assertDatabaseHas('gerar_textos', [
            'indice' => '1', 'indice' => '1.1', 'indice' => '2', 'indice' => '2.1', 'indice' => '3',
            'ordem' => '1', 'ordem' => '2', 'ordem' => '3', 'ordem' => '4', 'ordem' => '5'
        ]);
    }

    /** @test */
    public function can_be_update_indice_titulo_and_subtitulo_nivel1_and_nivel2()
    {
        $user = $this->signInAsAdmin();
        $textos = factory('App\GerarTexto', 5)
        ->create()
        ->each(function ($texto) {
            if(($texto->id > 1) && ($texto->id < 4))
                $texto->update([
                    'tipo' => 'Subtítulo',
                    'nivel' => 1,
                    'com_numeracao' => 1
                ]);
            elseif($texto->id >= 4)
                $texto->update([
                    'tipo' => 'Subtítulo',
                    'nivel' => 2,
                    'com_numeracao' => 1
                ]);
        });

        $dados = array();
        foreach($textos as $key => $val)
            $dados['id-'.$val->id] = $val->id;

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))->assertOk();
        $this->put(route('textos.update.indice', $textos->get(0)->tipo_doc), $dados)
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Índice atualizada com sucesso!')
        ->assertRedirect(route('textos.view', $textos->get(0)->tipo_doc));

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))
        ->assertSeeText($textos->get(0)->indice . '. '.$textos->get(0)->texto_tipo)
        ->assertSeeText($textos->get(1)->indice . ' - '.$textos->get(1)->texto_tipo)
        ->assertSeeText($textos->get(2)->indice . ' - '.$textos->get(2)->texto_tipo)
        ->assertSeeText($textos->get(3)->indice . ' - '.$textos->get(3)->texto_tipo)
        ->assertSeeText($textos->get(4)->indice . ' - '.$textos->get(4)->texto_tipo);

        $this->assertDatabaseHas('gerar_textos', [
            'indice' => '1', 'indice' => '1.1', 'indice' => '1.2', 'indice' => '1.2.1', 'indice' => '1.2.2',
            'ordem' => '1', 'ordem' => '2', 'ordem' => '3', 'ordem' => '4', 'ordem' => '5'
        ]);
    }

    /** @test */
    public function can_be_update_indice_titulo_and_subtitulo_nivel1_and_nivel3()
    {
        $user = $this->signInAsAdmin();
        $textos = factory('App\GerarTexto', 5)
        ->create()
        ->each(function ($texto) {
            if(($texto->id > 1) && ($texto->id < 4))
                $texto->update([
                    'tipo' => 'Subtítulo',
                    'nivel' => 1,
                    'com_numeracao' => 1
                ]);
            elseif($texto->id >= 4)
                $texto->update([
                    'tipo' => 'Subtítulo',
                    'nivel' => 3,
                    'com_numeracao' => 1
                ]);
        });

        $dados = array();
        foreach($textos as $key => $val)
            $dados['id-'.$val->id] = $val->id;

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))->assertOk();
        $this->put(route('textos.update.indice', $textos->get(0)->tipo_doc), $dados)
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Índice atualizada com sucesso!')
        ->assertRedirect(route('textos.view', $textos->get(0)->tipo_doc));

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))
        ->assertSeeText($textos->get(0)->indice . '. '.$textos->get(0)->texto_tipo)
        ->assertSeeText($textos->get(1)->indice . ' - '.$textos->get(1)->texto_tipo)
        ->assertSeeText($textos->get(2)->indice . ' - '.$textos->get(2)->texto_tipo)
        ->assertSeeText($textos->get(3)->indice . ' - '.$textos->get(3)->texto_tipo)
        ->assertSeeText($textos->get(4)->indice . ' - '.$textos->get(4)->texto_tipo);

        $this->assertDatabaseHas('gerar_textos', [
            'indice' => '1', 'indice' => '1.1', 'indice' => '1.2', 'indice' => '1.2.1.1', 'indice' => '1.2.1.1',
            'ordem' => '1', 'ordem' => '2', 'ordem' => '3', 'ordem' => '4', 'ordem' => '5'
        ]);
    }

    /** @test */
    public function view_sumario_after_updated_indice()
    {
        $user = $this->signInAsAdmin();
        $textos = factory('App\GerarTexto', 5)
        ->create()
        ->each(function ($texto) {
            if(($texto->id > 1) && ($texto->id < 4))
                $texto->update([
                    'tipo' => 'Subtítulo',
                    'nivel' => 1,
                    'com_numeracao' => 1
                ]);
            elseif($texto->id == 4)
                $texto->update([
                    'tipo' => 'Subtítulo',
                    'nivel' => 3,
                    'com_numeracao' => 1
                ]);
        });

        $dados = array();
        foreach($textos as $key => $val)
            $dados['id-'.$val->id] = $val->id;

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))->assertOk();
        $this->put(route('textos.update.indice', $textos->get(0)->tipo_doc), array_reverse($dados));

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))
        ->assertSeeTextInOrder([
            'Sumário:',
            GerarTexto::where('ordem', 1)->first()->indice,
            GerarTexto::where('ordem', 2)->first()->indice,
            GerarTexto::where('ordem', 3)->first()->indice,
            GerarTexto::where('ordem', 4)->first()->indice,
            GerarTexto::where('ordem', 5)->first()->indice,
        ]);
    }

    /** @test */
    public function cannot_view_sumario_after_updated_indice_with_ids_invalid()
    {
        $user = $this->signInAsAdmin();
        $textos = factory('App\GerarTexto', 5)->create();

        $dados = array();
        for($cont = 20; $cont < 26; $cont++)
            $dados['id-'.$cont] = $cont;

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))->assertOk();
        $this->put(route('textos.update.indice', $textos->get(0)->tipo_doc), array_reverse($dados));

        $this->get(route('textos.view', $textos->get(0)->tipo_doc))
        ->assertDontSee('<p>&nbsp;&nbsp;&nbsp;<strong>'.GerarTexto::where('ordem', 1)->first()->indice.' - '.GerarTexto::where('ordem', 1)->first()->texto_tipo.'</strong></p>');
    }

    /** 
     * =======================================================================================================
     * TESTES NO PORTAL
     * =======================================================================================================
     */

    /** @test */
    public function cannot_view_sumario_on_portal_when_not_published()
    {
        $textos = factory('App\GerarTexto', 5)->create();

        $this->get(route($textos->get(0)->tipo_doc))
        ->assertDontSee('<label for="textosSumario">Sumário:</label>')
        ->assertSee('<strong>Ainda não consta a publicação atual.</strong>');

        $this->get(route($textos->get(0)->tipo_doc, $textos->get(0)->id))
        ->assertStatus(404);
    }

    /** @test */
    public function cannot_view_content_sumario_on_portal_when_not_published()
    {
        $textos = factory('App\GerarTexto', 5)->create();

        $this->get(route($textos->get(0)->tipo_doc, $textos->get(0)->id))
        ->assertStatus(404);
    }

    /** @test */
    public function can_view_sumario_on_portal_when_published()
    {
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();

        $this->get(route($textos->get(0)->tipo_doc))
        ->assertDontSee('<strong>Ainda não consta a publicação atual.</strong>')
        ->assertSee('<option value="'.$textos->get(0)->id.'">'.$textos->get(0)->indice.'. '.$textos->get(0)->texto_tipo.'</option>')
        ->assertSee('<option value="'.$textos->get(1)->id.'" style="font-weight: bold;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$textos->get(1)->indice.' - '.$textos->get(1)->texto_tipo.'</option>')
        ->assertSee('<option value="'.$textos->get(2)->id.'" style="font-weight: bold;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$textos->get(2)->indice.' - '.$textos->get(2)->texto_tipo.'</option>')
        ->assertSee('<option value="'.$textos->get(3)->id.'" style="font-weight: bold;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$textos->get(3)->indice.' - '.$textos->get(3)->texto_tipo.'</option>')
        ->assertSee('<option value="'.$textos->get(4)->id.'">'.$textos->get(4)->texto_tipo.'</option>');
    }

    /** @test */
    public function can_view_content_sumario_on_portal_when_published()
    {
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();

        $this->get(route($textos->get(0)->tipo_doc, $textos->get(3)->id))
        ->assertSeeText($textos->get(3)->conteudo);
    }

    /** @test */
    public function can_view_titulo_with_subtitulo()
    {
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();

        $this->get(route($textos->get(0)->tipo_doc, $textos->get(0)->id))
        ->assertSeeTextInOrder([
            $textos->get(0)->conteudo,
            $textos->get(1)->conteudo,
            $textos->get(2)->conteudo,
            $textos->get(3)->conteudo,
        ]);
    }

    /** @test */
    public function cannot_view_texto_when_not_find()
    {
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();

        $this->get(route($textos->get(0)->tipo_doc, 22))
        ->assertStatus(404);
    }

    /** @test */
    public function can_view_search_bar_when_published()
    {
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();

        $this->get(route($textos->get(0)->tipo_doc))
        ->assertSee('<label for="buscaTextoSumario" class="mb-2 mr-sm-2">Buscar:</label>')
        ->assertSee('name="buscaTexto"')
        ->assertSee('<button type="submit" class="btn btn-sm btn-primary mb-2">');
    }

    /** @test */
    public function cannot_view_search_bar_when_not_published()
    {
        $textos = factory('App\GerarTexto', 5)->create();

        $this->get(route($textos->get(0)->tipo_doc))
        ->assertDontSee('<label for="buscaTextoSumario" class="mb-2 mr-sm-2">Buscar:</label>')
        ->assertDontSee('name="buscaTexto"')
        ->assertDontSee('<button type="submit" class="btn btn-sm btn-primary mb-2">');
    }

    /** @test */
    public function cannot_search_when_not_published()
    {
        $textos = factory('App\GerarTexto', 5)->create();

        $this->get(route($textos->get(0)->tipo_doc . '-buscar', [
            'buscaTexto' => $textos->get(0)->texto_tipo
            ]))
            ->assertSee('<strong>Ainda não consta a publicação atual.</strong>');
    }

    /** @test */
    public function can_search_when_published()
    {
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();

        $this->get(route($textos->get(0)->tipo_doc . '-buscar', [
            'buscaTexto' => $textos->get(0)->texto_tipo
        ]))
        ->assertSee('<p class="light">Busca por: <strong>'.$textos->get(0)->texto_tipo.'</strong>')
        ->assertSee('<div class="list-group list-group-flush">')
        ->assertSee('<a href="'. route($textos->get(0)->tipo_doc, $textos->get(0)->id).'" class="list-group-item list-group-item-action"><strong>'.$textos->get(0)->indice.'. '.$textos->get(0)->texto_tipo.'</strong></a>');

        $this->get(route($textos->get(0)->tipo_doc . '-buscar', [
            'buscaTexto' => $textos->get(1)->texto_tipo
        ]))
        ->assertSee('<p class="light">Busca por: <strong>'.$textos->get(1)->texto_tipo.'</strong>')
        ->assertSee('<div class="list-group list-group-flush">')
        ->assertSee('<a href="'. route($textos->get(0)->tipo_doc, $textos->get(1)->id).'" class="list-group-item list-group-item-action"><strong>'.$textos->get(1)->indice.' - '.$textos->get(1)->texto_tipo.'</strong></a>');
    }

    /** @test */
    public function cannot_search_when_published_with_busca_texto_null()
    {
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();

        $this->get(route($textos->get(0)->tipo_doc . '-buscar', [
            'buscaTexto' => ''
        ]))
        ->assertSessionHasErrors([
            'buscaTexto'
        ]);
    }

    /** @test */
    public function cannot_search_when_published_with_busca_texto_less_than_3_chars()
    {
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();

        $this->get(route($textos->get(0)->tipo_doc . '-buscar', [
            'buscaTexto' => 'Te'
        ]))
        ->assertSessionHasErrors([
            'buscaTexto'
        ]);
    }

    /** @test */
    public function cannot_search_when_published_with_busca_texto_more_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();

        $this->get(route($textos->get(0)->tipo_doc . '-buscar', [
            'buscaTexto' => $faker->sentence(400)
        ]))
        ->assertSessionHasErrors([
            'buscaTexto'
        ]);
    }
}
