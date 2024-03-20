<?php

namespace Tests\Feature;

use App\GerarTexto;
use App\Permissao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;

class GerarTextoTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** 
     * =======================================================================================================
     * TESTES NO ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $texto = factory('App\GerarTexto')->states($tipo)->create();

            $this->get(route('textos.orientacao', ['tipo_doc' => $tipo, 'orientacao' => 'horizontal']))->assertRedirect(route('login'));
            $this->get(route('textos.view', $tipo))->assertRedirect(route('login'));
            $this->post(route('textos.create', $tipo))->assertRedirect(route('login'));
            $this->post(route('textos.update.campos', [$tipo, $texto->id]))->assertRedirect(route('login'));
            $this->post(route('textos.publicar', $tipo))->assertRedirect(route('login'));
            $this->delete(route('textos.delete', $tipo))->assertRedirect(route('login'));
            $this->put(route('textos.update.indice', $tipo))->assertRedirect(route('login'));
        }
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $texto = factory('App\GerarTexto')->states($tipo)->create();

            $this->get(route('textos.orientacao', ['tipo_doc' => $tipo, 'orientacao' => 'horizontal']))
            ->assertRedirect(route('textos.view', $tipo));
            $this->get(route('textos.view', $tipo))->assertForbidden();
            $this->post(route('textos.create', $tipo))->assertForbidden();
            $this->post(route('textos.update.campos', [$tipo, $texto->id]))->assertForbidden();
            $this->post(route('textos.publicar', $tipo))->assertForbidden();
            $this->delete(route('textos.delete', $tipo))->assertForbidden();
            $this->put(route('textos.update.indice', $tipo))->assertForbidden();
        }
    }

    /** @test */
    public function texto_can_be_created_by_an_user()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $this->get(route('textos.view', $tipo))->assertOk();
            $this->post(route('textos.create', $tipo))
            ->assertRedirect(route('textos.view', $tipo))
            ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Novo texto com o título: "'.GerarTexto::where('tipo_doc', $tipo)->first()->texto_tipo.'" foi criado com sucesso e inserido no final do sumário!');
    
            $this->get(route('textos.view', $tipo))
            ->assertSee(GerarTexto::where('tipo_doc', $tipo)->first()->tipo)
            ->assertSee(GerarTexto::where('tipo_doc', $tipo)->first()->texto_tipo);
    
            $this->assertDatabaseHas('gerar_textos', GerarTexto::where('tipo_doc', $tipo)->first()->toArray());
        }
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
        $tipos = array_keys(GerarTexto::tiposDoc());
        $total = 5 * count($tipos);

        for($cont = 1; $cont <= $total; $cont++){
            $tipo = $cont <= 5 ? $tipos[0] : $tipos[1];
            $this->get(route('textos.view', $tipo))->assertOk();
            $this->post(route('textos.create', $tipo))
            ->assertRedirect(route('textos.view', $tipo))
            ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Novo texto com o título: "'.GerarTexto::find($cont)->texto_tipo.'" foi criado com sucesso e inserido no final do sumário!');
    
            $this->get(route('textos.view', $tipo))
            ->assertSee('<button type="button" class="btn btn-link btn-sm pl-0 abrir" value="'.$cont.'">')
            ->assertSee('<input type="hidden" name="id-'.$cont.'" value="'.$cont.'" />');
    
            $this->assertDatabaseHas('gerar_textos', GerarTexto::find($cont)->toArray());
        }

        $this->assertEquals($total, GerarTexto::count());
    }

    /** @test */
    public function log_is_generated_when_texto_is_created()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $key => $tipo)
        {
            $this->post(route('textos.create', $tipo));

            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
            $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') criou *novo texto do documento '.$tipo.'* (id: '. ++$key .')';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function texto_is_shown_on_admin_panel_after_its_creation()
    {
        $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $txt = factory('App\GerarTexto')->states($tipo)->create();
        
            $this->get(route('textos.view', $tipo))
                ->assertSee('>'.$txt->tituloFormatado().'</span>')
                ->assertSee('<input type="hidden" name="id-'.$txt->id.'" value="'.$txt->id.'" />')
                ->assertSee('<input type="checkbox" class="form-check-input mt-2" name="excluir_ids" value="'.$txt->id.'">')
                ->assertSee('<button type="button" class="btn btn-link btn-sm pl-0 abrir" value="'.$txt->id.'">');
        }
    }

    /** @test */
    public function can_sort_index()
    {
        $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $txt = factory('App\GerarTexto')->states($tipo)->create();
        
            $this->get(route('textos.orientacao', ['tipo_doc' => $tipo, 'orientacao' => 'horizontal']))
            ->assertSessionHas('orientacao_sumario', 'horizontal')
            ->assertRedirect(route('textos.view', $tipo));

            $this->get(route('textos.view', $tipo))
            ->assertSee('<div class="d-flex flex-wrap ')
            ->assertDontSee('<div class="col-3">')
            ->assertSee('<a type="button" class="btn btn-link pt-0 disabled" href="'.route('textos.orientacao', ['tipo_doc' => $tipo, 'orientacao' => 'horizontal']).'">Horizontal</a>')
            ->assertSee('<a type="button" class="btn btn-link pt-0 " href="'.route('textos.orientacao', ['tipo_doc' => $tipo, 'orientacao' => 'vertical']).'">Vertical</a>');

            $this->get(route('textos.orientacao', ['tipo_doc' => $tipo, 'orientacao' => 'vertical']))
            ->assertSessionHas('orientacao_sumario', 'vertical')
            ->assertRedirect(route('textos.view', $tipo));

            $this->get(route('textos.view', $tipo))
            ->assertSee('<div class="col-3">')
            ->assertDontSee('<div class="d-flex flex-wrap ')
            ->assertSee('<a type="button" class="btn btn-link pt-0 " href="'.route('textos.orientacao', ['tipo_doc' => $tipo, 'orientacao' => 'horizontal']).'">Horizontal</a>')
            ->assertSee('<a type="button" class="btn btn-link pt-0 disabled" href="'.route('textos.orientacao', ['tipo_doc' => $tipo, 'orientacao' => 'vertical']).'">Vertical</a>');
        }
    }

    /** @test */
    public function cannot_sort_index_with_orientacao_invalid()
    {
        $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $txt = factory('App\GerarTexto')->states($tipo)->create();
        
            $this->get(route('textos.orientacao', ['tipo_doc' => $tipo, 'orientacao' => 'horizontall']))
            ->assertSessionMissing('orientacao_sumario')
            ->assertNotFound();

            $this->get(route('textos.orientacao', ['tipo_doc' => $tipo, 'orientacao' => 'vrtical']))
            ->assertSessionMissing('orientacao_sumario')
            ->assertNotFound();
        }
    }

    /** @test */
    public function texto_can_be_updated_by_an_user()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $texto = factory('App\GerarTexto')->states($tipo)->create();
            $dados = $texto->toArray();
            $dados['com_numeracao'] = true;
            $dados['tipo'] = 'Subtítulo';
            $dados['texto_tipo'] = 'Teste do update';
            $dados['nivel'] = 1;
    
            $this->get(route('textos.view', $tipo))->assertOk();
            $this->post(route('textos.update.campos', [$tipo, $texto->id]), $dados)
            ->assertJsonFragment([
                'nivel' => $dados['nivel'],
                'tipo' => $dados['tipo'],
                'conteudo' => $dados['conteudo'],
            ]);
    
            $this->assertDatabaseHas('gerar_textos', GerarTexto::where('tipo_doc', $tipo)->first()->toArray());
            $this->assertDatabaseMissing('gerar_textos', $texto->toArray());
        }
    }

    /** @test */
    public function level_adjusted_by_order()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 2)->states($tipo)->create();
            $dados = $textos->get(1)->toArray();
            $dados['com_numeracao'] = true;
            $dados['tipo'] = 'Subtítulo';
            $dados['texto_tipo'] = 'Teste do update';
            // Quando o nível escolhido tem diferença maior que 1 sendo ele um subtítulo e difere da índice 
            $dados['nivel'] = 3;
    
            $this->get(route('textos.view', $tipo))->assertOk();
            $this->post(route('textos.update.campos', [$tipo, $textos->get(1)->id]), $dados);
    
            $dados = array();
            foreach($textos as $key => $val)
                $dados['id-'.$val->id] = $val->id;

            $this->put(route('textos.update.indice', $tipo), $dados);
            $this->assertDatabaseHas('gerar_textos', ['tipo' => 'Subtítulo', 'nivel' => 1, 'indice' => '1.1']);
        }
    }

    /** @test */
    public function texto_cannot_be_updated_without_changes()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $texto = factory('App\GerarTexto')->states($tipo)->create([
                'texto_tipo' => mb_strtoupper('Texto Fixo', 'UTF-8'),
                'updated_at' => now()->subDays(3)->format('Y-m-d H:i:s')
            ])->toArray();
    
            $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
            $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
            ->assertJsonFragment([
                'nivel' => $texto['nivel'],
                'tipo' => $texto['tipo'],
                'conteudo' => $texto['conteudo'],
                'updated_at' => $texto['updated_at']
            ]);
    
            $this->assertDatabaseMissing('gerar_textos', [
                'updated_at' => now()->format('Y-m-d H:i:s')
            ]);
        }
    }

    /** @test */
    public function texto_cannot_be_updated_without_input_tipo()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $texto = factory('App\GerarTexto')->states($tipo)->create()->toArray();
            $texto['tipo'] = null;
    
            $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
            $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
            ->assertSessionHasErrors([
                'tipo'
            ]);
        }
    }

    /** @test */
    public function texto_cannot_be_updated_with_tipo_invalid()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $texto = factory('App\GerarTexto')->states($tipo)->create()->toArray();
            $texto['tipo'] = 'Teste';
    
            $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
            $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
            ->assertSessionHasErrors([
                'tipo'
            ]);
        }
    }

    /** @test */
    public function texto_cannot_be_updated_without_input_texto_tipo()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $texto = factory('App\GerarTexto')->states($tipo)->create()->toArray();
            $texto['texto_tipo'] = null;
    
            $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
            $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
            ->assertSessionHasErrors([
                'texto_tipo'
            ]);
        }
    }

    /** @test */
    public function texto_cannot_be_updated_with_texto_tipo_more_than_191_chars()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $texto = factory('App\GerarTexto')->states($tipo)->create()->toArray();
            $texto['texto_tipo'] = $this->faker()->sentence(400);
    
            $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
            $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
            ->assertSessionHasErrors([
                'texto_tipo'
            ]);
        }
    }

    /** @test */
    public function texto_cannot_be_updated_without_input_nivel()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $texto = factory('App\GerarTexto')->states($tipo)->create()->toArray();
            $texto['nivel'] = null;
    
            $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
            $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
            ->assertSessionHasErrors([
                'nivel'
            ]);
        }
    }

    /** @test */
    public function texto_cannot_be_updated_with_nivel_invalid()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $texto = factory('App\GerarTexto')->states($tipo)->create()->toArray();
            $texto['nivel'] = 1;
    
            $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
            $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
            ->assertSessionHasErrors([
                'nivel'
            ]);
    
            GerarTexto::where('tipo_doc', $tipo)->first()->update([
                'tipo' => 'Subtítulo'
            ]);
            $texto = GerarTexto::where('tipo_doc', $tipo)->first()->fresh()->toArray();
            $texto['nivel'] = 0;
    
            $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
            $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
            ->assertSessionHasErrors([
                'nivel'
            ]);
        }
    }

    /** @test */
    public function texto_cannot_be_updated_without_input_com_numeracao()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $texto = factory('App\GerarTexto')->states($tipo)->create()->toArray();
            $texto['com_numeracao'] = null;
    
            $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
            $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
            ->assertSessionHasErrors([
                'com_numeracao'
            ]);
        }
    }

    /** @test */
    public function texto_cannot_be_updated_with_com_numeracao_invalid()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $texto = factory('App\GerarTexto')->states($tipo)->create()->toArray();
            $texto['com_numeracao'] = 2;
    
            $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
            $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
            ->assertSessionHasErrors([
                'com_numeracao'
            ]);
    
            GerarTexto::where('tipo_doc', $tipo)->first()->update([
                'tipo' => 'Subtítulo'
            ]);
            $texto = GerarTexto::where('tipo_doc', $tipo)->first()->fresh()->toArray();
            $texto['com_numeracao'] = 0;
    
            $this->get(route('textos.view', $texto['tipo_doc']))->assertOk();
            $this->post(route('textos.update.campos', [$texto['tipo_doc'], $texto['id']]), $texto)
            ->assertSessionHasErrors([
                'com_numeracao'
            ]);
        }
    }

    /** @test */
    public function log_is_generated_when_texto_is_updated()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $key => $tipo)
        {
            $texto = factory('App\GerarTexto')->states($tipo)->create();
            $dados = $texto->toArray();
            $dados['com_numeracao'] = true;
    
            $this->get(route('textos.view', $tipo))->assertOk();
            $this->post(route('textos.update.campos', [$tipo, $texto->id]), $dados);
    
            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
            $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') atualizou *campos do texto do documento '.$tipo.'* (id: '.++$key.')';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function texto_can_be_deleted()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 2)->states($tipo)->create();

            $id = $textos->get(1)->id;
            $this->get(route('textos.view', $tipo))->assertOk();
            $this->delete(route('textos.delete', $tipo), ['excluir_ids' => $id])
            ->assertJson([$id]);
    
            $this->assertDatabaseMissing('gerar_textos', $textos->get(1)->toArray());
        }
    }

    /** @test */
    public function textos_can_be_deleted()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 3)->states($tipo)->create();

            $ids = $tipo == GerarTexto::DOC_PREST_CONT ? '4,5' : '1,2';
            $this->get(route('textos.view', $tipo))->assertOk();
            $this->delete(route('textos.delete', $tipo), ['excluir_ids' => $ids])
            ->assertJson([
                $tipo == GerarTexto::DOC_PREST_CONT ? '4' : '1',
                $tipo == GerarTexto::DOC_PREST_CONT ? '5' : '2',
            ]);
    
            $this->assertDatabaseMissing('gerar_textos', $textos->get(0)->toArray());
            $this->assertDatabaseMissing('gerar_textos', $textos->get(1)->toArray());
        }
    }

    /** @test */
    public function textos_cannot_be_deleted_with_invalid_id()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 4)->states($tipo)->create();

            $ids = $tipo == GerarTexto::DOC_PREST_CONT ? '5,25' : '1,22';
            $this->get(route('textos.view', $tipo))->assertOk();
            $this->delete(route('textos.delete', $tipo), ['excluir_ids' => $ids])
            ->assertJson([
                $tipo == GerarTexto::DOC_PREST_CONT ? '5' : '1',
            ])->assertJsonMissing([
                $tipo == GerarTexto::DOC_PREST_CONT ? '25' : '22'
            ]);
    
            $this->assertDatabaseMissing('gerar_textos', $textos->get(0)->toArray());
            $this->assertEquals(GerarTexto::where('tipo_doc', $tipo)->count(), 3);
    
            $ids = 'ddd,2d';
            $this->get(route('textos.view', $tipo))->assertOk();
            $this->delete(route('textos.delete', $tipo), ['excluir_ids' => $ids])
            ->assertJsonMissing([
                'ddd',
                '2d'
            ]);
    
            $this->assertDatabaseHas('gerar_textos', $textos->get(1)->toArray());
            $this->assertEquals(GerarTexto::where('tipo_doc', $tipo)->count(), 3);
        }
    }

    /** @test */
    public function texto_cannot_be_deleted_when_only_one()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $texto = factory('App\GerarTexto')->states($tipo)->create();

            $this->get(route('textos.view', $tipo))->assertOk();
            $this->delete(route('textos.delete', $tipo), ['excluir_ids' => $texto->id])
            ->assertStatus(400);
    
            $this->assertDatabaseHas('gerar_textos', $texto->toArray());
        }
    }

    /** @test */
    public function log_is_generated_when_texto_is_deleted()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 2)->states($tipo)->create();
        
            $this->delete(route('textos.delete', $tipo), ['excluir_ids' => $textos->get(0)->id]);
    
            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
            $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') excluiu *o texto do documento '.$tipo.' com o nome: '.$textos->get(0)->texto_tipo.'* (id: '.$textos->get(0)->id.')';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function can_be_published()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 2)->states($tipo)->create();

            $this->get(route('textos.view', $tipo))->assertOk();
            $this->post(route('textos.publicar', $tipo), ['publicar' => 1])
            ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Foi publicada no site com sucesso!')
            ->assertRedirect(route('textos.view', $tipo));
    
            $this->assertDatabaseHas('gerar_textos', ['tipo_doc' => $tipo, 'publicar' => 1]);
            $this->assertDatabaseMissing('gerar_textos', ['tipo_doc' => $tipo, 'publicar' => 0]);
        }
    }

    /** @test */
    public function log_is_generated_when_texto_is_published()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 2)->states($tipo)->create();
        
            $this->post(route('textos.publicar', $tipo), ['publicar' => 1]);
    
            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
            $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') publicou *os textos do documento '.$tipo.'* (id: ---)';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function can_be_not_published()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 2)->states($tipo, 'sumario_publicado')->create();

            $this->get(route('textos.view', $tipo))->assertOk();
            $this->post(route('textos.publicar', $tipo), ['publicar' => 0])
            ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Foi revertida a publicação no site com sucesso!')
            ->assertRedirect(route('textos.view', $tipo));
    
            $this->assertDatabaseHas('gerar_textos', ['tipo_doc' => $tipo, 'publicar' => 0]);
            $this->assertDatabaseMissing('gerar_textos', ['tipo_doc' => $tipo, 'publicar' => 1]);
        }
    }

    /** @test */
    public function log_is_generated_when_texto_is_not_published()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 2)->states($tipo, 'sumario_publicado')->create();
        
            $this->post(route('textos.publicar', $tipo), ['publicar' => 0]);
    
            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
            $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') reverteu publicação *os textos do documento '.$tipo.'* (id: ---)';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function cannot_be_published_without_input_publicar()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 2)->states($tipo)->create();

            $this->get(route('textos.view', $tipo))->assertOk();
            $this->post(route('textos.publicar', $tipo), ['publicar' => null])
            ->assertSessionHasErrors([
                'publicar'
            ]);
        }
    }

    /** @test */
    public function cannot_be_published_with_publicar_not_boolean()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 2)->states($tipo)->create();

            $this->get(route('textos.view', $tipo))->assertOk();
            $this->post(route('textos.publicar', $tipo), ['publicar' => 3])
            ->assertSessionHasErrors([
                'publicar'
            ]);
        }
    }

    /** @test */
    public function show_on_portal_not_published_and_authenticated_admin_when_tipo_doc_carta_servicos()
    {
        $textos = factory('App\GerarTexto', 2)->create();

        $this->assertGuest();

        $this->get(route('carta-servicos'))
        ->assertSeeText('Ainda não consta a publicação atual.')
        ->assertDontSee('<option value="'.$textos->get(0)->id.'">');

        $this->get(route('carta-servicos', 1))
        ->assertNotFound();

        $user = $this->signInAsAdmin();

        $this->get(route('carta-servicos'))
        ->assertDontSeeText('Ainda não consta a publicação atual.')
        ->assertSeeInOrder([
            '<option value="" style="font-style: italic;">Escolha um título ou subtítulo ...</option>',
            '<option value="'.$textos->get(0)->id.'" style="" >'.$textos->get(0)->tituloFormatado().'</option>',
            '</select>'
        ]);

        $this->get(route('carta-servicos', 1))
        ->assertSeeText($textos->get(0)->conteudo);
    }

    /** @test */
    public function show_on_portal_not_published_and_authenticated_admin_when_tipo_doc_prestacao_contas()
    {
        $textos = factory('App\GerarTexto', 2)->states('prestacao-contas')->create();

        $this->assertGuest();

        $this->get(route('prestacao-contas'))
        ->assertSeeText('Informações sendo atualizadas.');

        $user = $this->signInAsAdmin();

        $this->get(route('prestacao-contas'))
        ->assertDontSeeText('Informações sendo atualizadas.')
        ->assertSeeInOrder([
            '<div id="accordionPrimario" class="accordion">',
            '<a href="#lista-'. Str::slug(strtolower($textos->get(0)->texto_tipo), '-') .'" data-toggle="collapse">',
            '<strong><u>'.$textos->get(0)->texto_tipo.'</u></strong>'
        ]);
    }

    /** @test */
    public function show_on_portal_after_published_when_tipo_doc_carta_servicos()
    {
        $textos = factory('App\GerarTexto', 2)->create();

        $this->get(route('carta-servicos'))
        ->assertSeeText('Ainda não consta a publicação atual.')
        ->assertDontSee('<option value="'.$textos->get(0)->id.'">');

        $user = $this->signInAsAdmin();

        $this->post(route('textos.publicar', $textos->get(0)->tipo_doc), ['publicar' => 1]);
        $this->post('/admin/logout', []);

        $this->assertGuest();

        $this->get(route('carta-servicos'))
        ->assertDontSeeText('Ainda não consta a publicação atual.')
        ->assertSeeInOrder([
            '<option value="" style="font-style: italic;">Escolha um título ou subtítulo ...</option>',
            '<option value="'.$textos->get(0)->id.'" style="" >'.$textos->get(0)->tituloFormatado().'</option>',
            '</select>'
        ]);
    }

    /** @test */
    public function show_on_portal_after_published_when_tipo_doc_prestacao_contas()
    {
        $textos = factory('App\GerarTexto', 2)->states('prestacao-contas')->create();

        $this->get(route('prestacao-contas'))
        ->assertSeeText('Informações sendo atualizadas.');

        $user = $this->signInAsAdmin();

        $this->post(route('textos.publicar', $textos->get(0)->tipo_doc), ['publicar' => 1]);
        $this->post('/admin/logout', []);

        $this->assertGuest();

        $this->get(route('prestacao-contas'))
        ->assertDontSeeText('Informações sendo atualizadas.')
        ->assertSeeInOrder([
            '<div id="accordionPrimario" class="accordion">',
            '<a href="#lista-'. Str::slug(strtolower($textos->get(0)->texto_tipo), '-') .'" data-toggle="collapse">',
            '<strong><u>'.$textos->get(0)->texto_tipo.'</u></strong>'
        ]);
    }

    /** @test */
    public function can_be_update_indice_only_titulo()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 5)->states($tipo)->create();
            $dados = array();
            foreach($textos as $key => $val)
                $dados['id-'.$val->id] = $val->id;
    
            $this->get(route('textos.view', $tipo))->assertOk();
    
            $this->put(route('textos.update.indice', $tipo), array_reverse($dados))
            ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Índice atualizada com sucesso!')
            ->assertRedirect(route('textos.view', $tipo));
    
            $this->get(route('textos.view', $tipo))
            ->assertSeeText($textos->get(0)->tituloFormatado())
            ->assertSeeText($textos->get(1)->tituloFormatado())
            ->assertSeeText($textos->get(2)->tituloFormatado())
            ->assertSeeText($textos->get(3)->tituloFormatado())
            ->assertSeeText($textos->get(4)->tituloFormatado());
    
            $this->assertDatabaseHas('gerar_textos', [
                'tipo_doc' => $tipo,
                'indice' => '1', 'indice' => '2', 'indice' => '3', 'indice' => '4', 'indice' => '5',
                'ordem' => '1', 'ordem' => '2', 'ordem' => '3', 'ordem' => '4', 'ordem' => '5'
            ]);
        }
    }

    /** @test */
    public function can_be_update_indice_with_count_300()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 300)->states($tipo)->create();
            $dados = array();
            foreach($textos as $key => $val)
                $dados['id-'.$val->id] = $val->id;
    
            $this->get(route('textos.view', $tipo))->assertOk();
    
            $this->put(route('textos.update.indice', $tipo), array_reverse($dados))
            ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Índice atualizada com sucesso!')
            ->assertRedirect(route('textos.view', $tipo));
    
            $this->get(route('textos.view', $tipo))
            ->assertSeeInOrder(array_keys(array_reverse($dados)));
        }
    }

    /** @test */
    public function log_is_generated_when_sumario_is_updated()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 5)->states($tipo)->create();
            $dados = array();
            foreach($textos as $key => $val)
                $dados['id-'.$val->id] = $val->id;
            
            $this->put(route('textos.update.indice', $tipo), array_reverse($dados));
    
            $log = tailCustom(storage_path($this->pathLogInterno()));
            $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
            $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') atualizou *índice do texto do documento '.$tipo.'* (id: ----)';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function can_be_update_indice_titulo_and_subtitulo()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {                        
            $textos = factory('App\GerarTexto', 5)
            ->states($tipo)
            ->create()
            ->each(function ($texto) {
                if(in_array($texto->id, [2, 4, 7, 9]))
                    $texto->update([
                        'tipo' => 'Subtítulo',
                        'nivel' => 1,
                        'com_numeracao' => 1
                    ]);
            });
    
            $dados = array();
            foreach($textos as $key => $val)
                $dados['id-'.$val->id] = $val->id;

            $this->put(route('textos.update.indice', $tipo), array_reverse($dados))
            ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Índice atualizada com sucesso!')
            ->assertRedirect(route('textos.view', $tipo));

            $this->get(route('textos.view', $tipo))
            ->assertSeeText($textos->fresh()->get(0)->tituloFormatado())
            ->assertSeeText($textos->fresh()->get(1)->subtituloFormatado())
            ->assertSeeText($textos->fresh()->get(2)->tituloFormatado())
            ->assertSeeText($textos->fresh()->get(3)->subtituloFormatado())
            ->assertSeeText($textos->fresh()->get(4)->tituloFormatado());
    
            $this->assertDatabaseHas('gerar_textos', [
                'tipo_doc' => $tipo,
                'indice' => '1', 'indice' => '1.1', 'indice' => '2', 'indice' => '2.1', 'indice' => '3',
                'ordem' => '1', 'ordem' => '2', 'ordem' => '3', 'ordem' => '4', 'ordem' => '5'
            ]);
        }
    }

    /** @test */
    public function can_be_update_indice_titulo_and_subtitulo_nivel1_and_nivel2()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 5)
            ->states($tipo)
            ->create()
            ->each(function ($texto) {
                if(in_array($texto->id, [2, 3, 7, 8]))
                    $texto->update([
                        'tipo' => 'Subtítulo',
                        'nivel' => 1,
                        'com_numeracao' => 1
                    ]);
                elseif(in_array($texto->id, [4, 5, 9, 10]))
                    $texto->update([
                        'tipo' => 'Subtítulo',
                        'nivel' => 2,
                        'com_numeracao' => 1
                    ]);
            });
    
            $dados = array();
            foreach($textos as $key => $val)
                $dados['id-'.$val->id] = $val->id;
    
            $this->put(route('textos.update.indice', $tipo), $dados)
            ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Índice atualizada com sucesso!')
            ->assertRedirect(route('textos.view', $tipo));
    
            $this->get(route('textos.view', $tipo))
            ->assertSeeText($textos->fresh()->get(0)->tituloFormatado())
            ->assertSeeText($textos->fresh()->get(1)->subtituloFormatado())
            ->assertSeeText($textos->fresh()->get(2)->subtituloFormatado())
            ->assertSeeText($textos->fresh()->get(3)->subtituloFormatado())
            ->assertSeeText($textos->fresh()->get(4)->subtituloFormatado());
    
            $this->assertDatabaseHas('gerar_textos', [
                'tipo_doc' => $tipo,
                'indice' => '1', 'indice' => '1.1', 'indice' => '1.2', 'indice' => '1.2.1', 'indice' => '1.2.2',
                'ordem' => '1', 'ordem' => '2', 'ordem' => '3', 'ordem' => '4', 'ordem' => '5'
            ]);
        }
    }

    /** @test */
    public function can_be_update_indice_titulo_and_subtitulo_nivel1_and_nivel3()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 5)
            ->states($tipo)
            ->create()
            ->each(function ($texto) {
                if(in_array($texto->id, [2, 3, 7, 8]))
                    $texto->update([
                        'tipo' => 'Subtítulo',
                        'nivel' => 1,
                        'com_numeracao' => 1
                    ]);
                elseif(in_array($texto->id, [4, 5, 9, 10]))
                    $texto->update([
                        'tipo' => 'Subtítulo',
                        'nivel' => 3,
                        'com_numeracao' => 1
                    ]);
            });
    
            $dados = array();
            foreach($textos as $key => $val)
                $dados['id-'.$val->id] = $val->id;
    
            $this->put(route('textos.update.indice', $tipo), $dados)
            ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Índice atualizada com sucesso!')
            ->assertRedirect(route('textos.view', $tipo));
    
            $this->get(route('textos.view', $textos->get(0)->tipo_doc))
            ->assertSeeText($textos->fresh()->get(0)->tituloFormatado())
            ->assertSeeText($textos->fresh()->get(1)->subtituloFormatado())
            ->assertSeeText($textos->fresh()->get(2)->subtituloFormatado())
            ->assertSeeText($textos->fresh()->get(3)->subtituloFormatado())
            ->assertSeeText($textos->fresh()->get(4)->subtituloFormatado());
    
            $this->assertDatabaseHas('gerar_textos', [
                'tipo_doc' => $tipo,
                'indice' => '1', 'indice' => '1.1', 'indice' => '1.2', 'indice' => '1.2.1.1', 'indice' => '1.2.1.1',
                'ordem' => '1', 'ordem' => '2', 'ordem' => '3', 'ordem' => '4', 'ordem' => '5'
            ]);
        }
    }

    /** @test */
    public function view_sumario_after_updated_indice()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 5)
            ->states($tipo)
            ->create()
            ->each(function ($texto) {
                if(in_array($texto->id, [2, 3, 7, 8]))
                    $texto->update([
                        'tipo' => 'Subtítulo',
                        'nivel' => 1,
                        'com_numeracao' => 1
                    ]);
                elseif(in_array($texto->id, [4, 9]))
                    $texto->update([
                        'tipo' => 'Subtítulo',
                        'nivel' => 3,
                        'com_numeracao' => 1
                    ]);
            });
    
            $dados = array();
            foreach($textos as $key => $val)
                $dados['id-'.$val->id] = $val->id;
    
            $this->put(route('textos.update.indice', $tipo), array_reverse($dados));
    
            $this->get(route('textos.view', $tipo))
            ->assertSeeTextInOrder([
                'Sumário:',
                GerarTexto::where('ordem', 1)->where('tipo_doc', $tipo)->first()->indice,
                GerarTexto::where('ordem', 2)->where('tipo_doc', $tipo)->first()->indice,
                GerarTexto::where('ordem', 3)->where('tipo_doc', $tipo)->first()->indice,
                GerarTexto::where('ordem', 4)->where('tipo_doc', $tipo)->first()->indice,
                GerarTexto::where('ordem', 5)->where('tipo_doc', $tipo)->first()->indice,
            ]);
        }
    }

    /** @test */
    public function cannot_view_sumario_after_updated_indice_with_ids_invalid()
    {
        $user = $this->signInAsAdmin();

        foreach(array_keys(GerarTexto::tiposDoc()) as $tipo)
        {
            $textos = factory('App\GerarTexto', 5)->states($tipo)->create();

            $dados = array();
            for($cont = 20; $cont < 26; $cont++)
                $dados['id-'.$cont] = $cont;
    
            $this->get(route('textos.view', $tipo))->assertOk();
            $this->put(route('textos.update.indice', $tipo), array_reverse($dados));
    
            $this->get(route('textos.view', $tipo))
            ->assertSee('<input type="hidden" name="id-'.$textos->get(1)->id.'" value="'.$textos->get(1)->id.'" />')
            ->assertDontSee('<input type="hidden" name="id-20" value="20" />')
            ->assertDontSee('<input type="hidden" name="id-21" value="21" />')
            ->assertDontSee('<input type="hidden" name="id-22" value="22" />')
            ->assertDontSee('<input type="hidden" name="id-23" value="23" />')
            ->assertDontSee('<input type="hidden" name="id-24" value="24" />')
            ->assertDontSee('<input type="hidden" name="id-25" value="25" />');
        }
    }

    /** 
     * =======================================================================================================
     * TESTES NO PORTAL
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_admin_cannot_view_sumario_on_portal_when_not_published_when_tipo_doc_carta_servicos()
    {
        $textos = factory('App\GerarTexto', 5)->create();

        $this->get(route($textos->get(0)->tipo_doc))
        ->assertDontSee('<label for="textosSumario">Sumário:</label>')
        ->assertSee('<strong>Ainda não consta a publicação atual.</strong>');

        $this->get(route($textos->get(0)->tipo_doc, $textos->get(0)->id))
        ->assertStatus(404);
    }

    /** @test */
    public function non_authenticated_admin_cannot_view_sumario_on_portal_when_not_published_when_tipo_doc_prestacao_contas()
    {
        $textos = factory('App\GerarTexto', 5)->states('prestacao-contas')->create();

        $this->get(route($textos->get(0)->tipo_doc))
        ->assertDontSee('<div id="accordionPrimario" class="accordion">')
        ->assertSee('<p><i>Informações sendo atualizadas.</i></p>');
    }

    /** @test */
    public function non_authenticated_admin_cannot_view_content_sumario_on_portal_when_not_published()
    {
        $textos = factory('App\GerarTexto', 5)->create();

        $this->get(route($textos->get(0)->tipo_doc, $textos->get(0)->id))
        ->assertStatus(404);
    }

    /** @test */
    public function can_view_sumario_on_portal_when_published_when_tipo_doc_carta_servicos()
    {
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();

        $this->get(route($textos->get(0)->tipo_doc))
        ->assertDontSee('<strong>Ainda não consta a publicação atual.</strong>')
        ->assertSee('<option value="'.$textos->get(0)->id.'" style="" >'.$textos->get(0)->tituloFormatado().'</option>')
        ->assertSee('<option value="'.$textos->get(1)->id.'" style="font-weight: bold;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$textos->get(1)->subtituloFormatado().'</option>')
        ->assertSee('<option value="'.$textos->get(2)->id.'" style="font-weight: bold;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$textos->get(2)->subtituloFormatado().'</option>')
        ->assertSee('<option value="'.$textos->get(3)->id.'" style="font-weight: bold;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$textos->get(3)->subtituloFormatado().'</option>')
        ->assertSee('<option value="'.$textos->get(4)->id.'" style="" >'.$textos->get(4)->tituloFormatado().'</option>');
    }

    /** @test */
    public function can_view_content_sumario_on_portal_when_published()
    {
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();

        $this->get(route($textos->get(0)->tipo_doc, $textos->get(3)->id))
        ->assertSeeText($textos->get(3)->conteudo)
        ->assertSee('<option value="'.$textos->get(3)->id.'" style="font-weight: bold;" selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$textos->get(3)->subtituloFormatado().'</option>');

        $textos = factory('App\GerarTexto', 5)->states('prestacao-contas', 'sumario_publicado')->create()->sortBy('ordem');

        $this->get(route($textos->get(0)->tipo_doc))
        ->assertSeeInOrder([
            '<div id="accordionPrimario" class="accordion">',
            '<strong><u>'.$textos->get(0)->texto_tipo.'</u></strong>',
            '<div id="lista-'.Str::slug(strtolower($textos->get(0)->texto_tipo), '-').'" class="collapse" data-parent="#accordionPrimario">',
            '<div id="accordion'.Str::studly(Str::slug(strtolower($textos->get(0)->texto_tipo), '-')).'" class="accordion">',
            '<ul class="mb-0 pb-0">',
            '<li>',
            '<a href="#lista-'.Str::slug(strtolower($textos->get(1)->texto_tipo), '-').'"',
            'target="_blank" rel="noopener"',
            'data-toggle="collapse"',
            $textos->get(1)->texto_tipo,
            '<div id="lista-'.Str::slug(strtolower($textos->get(2)->texto_tipo), '-').'" class="collapse" data-parent="#lista-'.Str::slug(strtolower($textos->get(1)->texto_tipo), '-').'">',
            '<ul class="mb-0 pb-0">',
            '<li>',
            '<a href="'.strip_tags($textos->get(3)->conteudo).'"',
            $textos->get(3)->texto_tipo,
            '</li>',
            '</ul></div></li></ul></div></li>',
            '</ul>',
            '</div>',
            '</div>',
            '<strong><u>'.$textos->get(4)->texto_tipo.'</u></strong>',
            '<div id="lista-'.Str::slug(strtolower($textos->get(4)->texto_tipo), '-').'" class="collapse" data-parent="#accordionPrimario">',
            '<div id="accordion'.Str::studly(Str::slug(strtolower($textos->get(4)->texto_tipo), '-')).'" class="accordion">',
            '<ul class="mb-0 pb-0">',
            '</ul>',
            '</div>',
            '</div>',
            '</div>',
        ]);
    }

    /** @test */
    public function can_view_titulo_with_subtitulo()
    {
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();

        // id = 5 é outro título
        $this->get(route($textos->get(0)->tipo_doc, $textos->get(0)->id))
        ->assertDontSeeText($textos->get(4)->conteudo)
        ->assertSeeTextInOrder([
            $textos->get(0)->texto_tipo,
            $textos->get(0)->conteudo,
            $textos->get(1)->texto_tipo,
            $textos->get(1)->conteudo,
            $textos->get(2)->texto_tipo,
            $textos->get(2)->conteudo,
            $textos->get(3)->texto_tipo,
            $textos->get(3)->conteudo,
        ]);

        $textos = factory('App\GerarTexto', 5)->states('prestacao-contas', 'sumario_publicado')->create()->sortBy('ordem');

        $this->get(route($textos->get(0)->tipo_doc))
        ->assertSeeInOrder([
            $textos->get(0)->texto_tipo,
            $textos->get(1)->texto_tipo,
            $textos->get(2)->texto_tipo,
            strip_tags($textos->get(3)->conteudo),
            $textos->get(3)->texto_tipo,
            $textos->get(4)->texto_tipo,
        ]);
    }

    /** @test */
    public function cannot_view_texto_when_not_find()
    {
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();

        $this->get(route($textos->get(0)->tipo_doc, 22))
        ->assertNotFound();
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
    public function authenticated_admin_can_view_search_bar_when_not_published()
    {
        $user = $this->signInAsAdmin();

        $textos = factory('App\GerarTexto', 5)->create();

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
        ->assertSee('<a href="'. route($textos->get(0)->tipo_doc, $textos->get(0)->id).'" class="list-group-item list-group-item-action"><strong>'.$textos->get(0)->tituloFormatado().'</strong></a>');

        $this->get(route($textos->get(0)->tipo_doc . '-buscar', [
            'buscaTexto' => $textos->get(1)->texto_tipo
        ]))
        ->assertSee('<p class="light">Busca por: <strong>'.$textos->get(1)->texto_tipo.'</strong>')
        ->assertSee('<div class="list-group list-group-flush">')
        ->assertSee('<a href="'. route($textos->get(0)->tipo_doc, $textos->get(1)->id).'" class="list-group-item list-group-item-action"><strong>'.$textos->get(1)->subtituloFormatado().'</strong></a>');

        $this->get(route($textos->get(0)->tipo_doc . '-buscar', [
            'buscaTexto' => 'xxxxxx'
        ]))
        ->assertSee('<p class="light">Busca por: <strong>xxxxxx</strong>')
        ->assertDontSee('<div class="list-group list-group-flush">')
        ->assertDontSee('<a href="'. route($textos->get(0)->tipo_doc, $textos->get(1)->id).'" class="list-group-item list-group-item-action"><strong>'.$textos->get(1)->subtituloFormatado().'</strong></a>');
    }

    /** @test */
    public function authenticated_admin_can_search_when_not_published()
    {
        $user = $this->signInAsAdmin();

        $textos = factory('App\GerarTexto', 5)->create();

        $this->get(route($textos->get(0)->tipo_doc . '-buscar', [
            'buscaTexto' => $textos->get(0)->texto_tipo
        ]))
        ->assertSee('<p class="light">Busca por: <strong>'.$textos->get(0)->texto_tipo.'</strong>')
        ->assertSee('<div class="list-group list-group-flush">')
        ->assertSee('<a href="'. route($textos->get(0)->tipo_doc, $textos->get(0)->id).'" class="list-group-item list-group-item-action"><strong>'.$textos->get(0)->tituloFormatado().'</strong></a>');

        $this->get(route($textos->get(0)->tipo_doc . '-buscar', [
            'buscaTexto' => $textos->get(1)->texto_tipo
        ]))
        ->assertSee('<p class="light">Busca por: <strong>'.$textos->get(1)->texto_tipo.'</strong>')
        ->assertSee('<div class="list-group list-group-flush">')
        ->assertSee('<a href="'. route($textos->get(0)->tipo_doc, $textos->get(1)->id).'" class="list-group-item list-group-item-action"><strong>'.$textos->get(1)->tituloFormatado().'</strong></a>');

        $this->get(route($textos->get(0)->tipo_doc . '-buscar', [
            'buscaTexto' => 'xxxxxx'
        ]))
        ->assertSee('<p class="light">Busca por: <strong>xxxxxx</strong>')
        ->assertDontSee('<div class="list-group list-group-flush">')
        ->assertDontSee('<a href="'. route($textos->get(0)->tipo_doc, $textos->get(1)->id).'" class="list-group-item list-group-item-action"><strong>'.$textos->get(1)->tituloFormatado().'</strong></a>');
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
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();

        $this->get(route($textos->get(0)->tipo_doc . '-buscar', [
            'buscaTexto' => $this->faker()->sentence(400)
        ]))
        ->assertSessionHasErrors([
            'buscaTexto'
        ]);
    }
}
