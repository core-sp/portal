<?php

namespace Tests\Unit;

use App\GerarTexto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\GerarTextoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GerarTextoTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** 
     * =======================================================================================================
     * TESTES NO MODEL
     * =======================================================================================================
     */

    /** @test */
    public function orientacao_sumario()
    {
        $this->assertEquals([
            'carta-servicos' => 'vertical',
            'prestacao-contas' => 'horizontal',
        ], GerarTexto::orientacaoSumario());
    }

    /** @test */
    public function tipos()
    {
        $this->assertEquals([
            'Título',
            'Subtítulo',
        ], GerarTexto::tipos());
    }

    /** @test */
    public function tipos_doc()
    {
        $this->assertEquals([
            'carta-servicos' => 'Carta de serviços ao usuário',
            'prestacao-contas' => 'Prestação de Contas',
        ], GerarTexto::tiposDoc());
    }

    /** @test */
    public function reordenar_por_tipo()
    {
        $textos = factory('App\GerarTexto', 5)->create()
        ->each(function ($texto) {
            $texto->update([
                'ordem' => $texto->id,
            ]);
        });

        $textos->get(1)->delete();
        $textos->get(3)->delete();

        $this->assertEquals([1, 3, 5], GerarTexto::get()->pluck('ordem')->all());
        GerarTexto::reordenarPorTipo('carta-servicos');
        $this->assertEquals([1, 2, 3], GerarTexto::get()->pluck('ordem')->all());

        $textos->get(0)->delete();

        $this->assertEquals([2, 3], GerarTexto::get()->pluck('ordem')->all());
        GerarTexto::reordenarPorTipo('carta-servicos');
        $this->assertEquals([1, 2], GerarTexto::get()->pluck('ordem')->all());
    }

    /** @test */
    public function criar()
    {
        $this->assertEquals(0, GerarTexto::count());

        GerarTexto::criar('prestacao-contas');
        $this->assertEquals(1, GerarTexto::count());
        $this->assertEquals('TÍTULO DO TEXTO...', GerarTexto::all()->get(0)->texto_tipo);
        $this->assertEquals('prestacao-contas', GerarTexto::all()->get(0)->tipo_doc);
        $this->assertEquals(false, GerarTexto::all()->get(0)->publicada);

        GerarTexto::criar('carta-servicos', 3);
        $this->assertEquals(4, GerarTexto::count());
        $this->assertEquals('TÍTULO DO TEXTO...', GerarTexto::all()->get(2)->texto_tipo);
        $this->assertEquals('carta-servicos', GerarTexto::all()->get(3)->tipo_doc);
        $this->assertEquals(3, GerarTexto::where('tipo_doc', 'carta-servicos')->count());

        GerarTexto::criar('carta-servicos', 1);
        $this->assertEquals(5, GerarTexto::count());

        GerarTexto::criar('prestacao-contas', 2);
        $this->assertEquals(7, GerarTexto::count());
        $this->assertEquals(3, GerarTexto::where('tipo_doc', 'prestacao-contas')->count());

        GerarTexto::criar('carta-servicos', 0);
        $this->assertEquals(8, GerarTexto::count());
    }

    /** @test */
    public function update_indice()
    {
        $textos = factory('App\GerarTexto', 5)->create([
            'com_numeracao' => false
        ]);
        $textos->get(2)->update(['tipo' => GerarTexto::TIPO_SUBTITULO, 'nivel' => 3, 'com_numeracao' => true]);
        $textos->get(3)->update(['tipo' => GerarTexto::TIPO_SUBTITULO, 'nivel' => 2, 'com_numeracao' => true]);

        // IDs
        $array = [1, 4, 2, 3, 5];

        GerarTexto::updateIndice($array, GerarTexto::all());

        $this->assertEquals([null, '0.1', null, '0.1', null], GerarTexto::orderBy('ordem')->get()->pluck('indice')->all());
        $this->assertEquals([0, 1, 0, 1, 0], GerarTexto::orderBy('ordem')->get()->pluck('nivel')->all());
        $this->assertEquals([1, 2, 3, 4, 5], GerarTexto::orderBy('ordem')->get()->pluck('ordem')->all());

        // *************************************************************************************************************************************
        // Situações onde título possui ou não numeração (quando tem numeração a indice deve ter o numero seguinte do último título numerado)
        // os retornos 'null' são títulos sem numeração

        GerarTexto::find(2)->update(['com_numeracao' => true]);
        factory('App\GerarTexto')->create([
            'ordem' => 63
        ]);

        $array = [1, 4, 2, 3, 5, 6];
        GerarTexto::updateIndice($array, GerarTexto::all());

        $this->assertEquals([null, '0.1', '1', '1.1', null, '2'], GerarTexto::orderBy('ordem')->get()->pluck('indice')->all());
        $this->assertEquals([1, 2, 3, 4, 5, 6], GerarTexto::orderBy('ordem')->get()->pluck('ordem')->all());

        factory('App\GerarTexto', 2)->create([
            'com_numeracao' => false,
            'ordem' => 4
        ]);
        factory('App\GerarTexto')->create([
            'ordem' => 45
        ]);

        $array = [1, 4, 2, 3, 5, 6, 7, 8, 9];
        GerarTexto::updateIndice($array, GerarTexto::all());

        $this->assertEquals([null, '0.1', '1', '1.1', null, '2', null, null, '3'], GerarTexto::orderBy('ordem')->get()->pluck('indice')->all());
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9], GerarTexto::orderBy('ordem')->get()->pluck('ordem')->all());
    }

    /** @test */
    public function resultado_by_doc()
    {
        $user = factory('App\User')->create();
        $textos_cs = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();
        $textos_pc = factory('App\GerarTexto', 10)->states('sumario_publicado', 'prestacao-contas')->create();

        $resultado = GerarTexto::resultadoByDoc('carta-servicos');
        $this->assertEquals(5, $resultado->count());
        $this->assertFalse(isset($resultado->get(0)->conteudo));
        $this->assertFalse(isset($resultado->get(3)->conteudo));
        $resultado = GerarTexto::resultadoByDoc('prestacao-contas');
        $this->assertEquals(10, $resultado->count());
        $this->assertTrue(isset($resultado->get(8)->conteudo));

        // buscar
        $resultado = GerarTexto::resultadoByDoc('carta-servicos', null, $textos_cs->get(0)->texto_tipo);
        $this->assertEquals(1, $resultado->count());
        $this->assertFalse(isset($resultado->get(0)->conteudo));

        $resultado = GerarTexto::resultadoByDoc('carta-servicos', $user);
        $this->assertEquals(5, $resultado->count());
        $this->assertFalse(isset($resultado->get(0)->conteudo));
        $this->assertFalse(isset($resultado->get(3)->conteudo));
        $resultado = GerarTexto::resultadoByDoc('prestacao-contas', $user);
        $this->assertEquals(10, $resultado->count());
        $this->assertTrue(isset($resultado->get(8)->conteudo));

        // buscar
        $resultado = GerarTexto::resultadoByDoc('carta-servicos', $user, $textos_cs->get(3)->texto_tipo);
        $this->assertEquals(1, $resultado->count());
        $this->assertFalse(isset($resultado->get(0)->conteudo));

        GerarTexto::where('publicar', true)->update(['publicar' => false]);

        $resultado = GerarTexto::resultadoByDoc('carta-servicos');
        $this->assertEquals(0, $resultado->count());
        $this->assertFalse(isset($resultado->get(0)->conteudo));
        $this->assertFalse(isset($resultado->get(3)->conteudo));
        $resultado = GerarTexto::resultadoByDoc('prestacao-contas');
        $this->assertEquals(0, $resultado->count());

        // buscar
        $resultado = GerarTexto::resultadoByDoc('carta-servicos', null, 'blá blá');
        $this->assertEquals(0, $resultado->count());
        $this->assertFalse(isset($resultado->get(0)->conteudo));
        $this->assertFalse(isset($resultado->get(3)->conteudo));

        $resultado = GerarTexto::resultadoByDoc('carta-servicos', $user);
        $this->assertEquals(5, $resultado->count());
        $this->assertFalse(isset($resultado->get(0)->conteudo));
        $this->assertFalse(isset($resultado->get(3)->conteudo));
        $resultado = GerarTexto::resultadoByDoc('prestacao-contas', $user);
        $this->assertEquals(10, $resultado->count());
        $this->assertTrue(isset($resultado->get(8)->conteudo));

        // buscar
        $resultado = GerarTexto::resultadoByDoc('carta-servicos', $user, $textos_cs->get(2)->texto_tipo);
        $this->assertEquals(1, $resultado->count());
        $this->assertFalse(isset($resultado->get(0)->conteudo));
    }

    /** @test */
    public function conteudo_titulo_com_subtitulo()
    {
        $user = factory('App\User')->create();

        factory('App\GerarTexto', 5)->states('sumario_publicado')->create();
        $resultado = GerarTexto::resultadoByDoc('carta-servicos', $user);

        $final = GerarTexto::find(1)->conteudoTituloComSubtitulo(true);
        $this->assertEquals([
            GerarTexto::find(1), GerarTexto::find(2), GerarTexto::find(3), GerarTexto::find(4),
        ], $final['textos']);
        $this->assertEquals(GerarTexto::find(1)->conteudo, $final['textos'][0]->conteudo);
        $this->assertEquals(GerarTexto::find(2)->conteudo, $final['textos'][1]->conteudo);
        $this->assertEquals(GerarTexto::find(3)->conteudo, $final['textos'][2]->conteudo);
        $this->assertEquals(GerarTexto::find(4)->conteudo, $final['textos'][3]->conteudo);

        $this->assertEquals(null, $final['btn_anterior']);
        $this->assertEquals(GerarTexto::select([
            'id', 'ordem'
        ])->find(5), $final['btn_proximo']);

        $final = GerarTexto::find(3)->conteudoTituloComSubtitulo();
        $this->assertEquals(GerarTexto::select([
            'id'
        ])->find(1), $final['btn_anterior']);
        $this->assertEquals(GerarTexto::select([
            'id', 'ordem'
        ])->find(5), $final['btn_proximo']);
        
        // retorno nulo, publicar == false
        GerarTexto::where('publicar', true)->update(['publicar' => false]);

        $final = GerarTexto::find(3)->conteudoTituloComSubtitulo();
        $this->assertEquals(null, $final);

        $final = GerarTexto::find(3)->conteudoTituloComSubtitulo(true);
        $this->assertEquals(GerarTexto::select([
            'id', 'ordem'
        ])->find(5), $final['btn_proximo']);
    }

    /** @test */
    public function ultima_atualizacao()
    {
        $data_antiga = now()->subDays(5)->format('Y-m-d H:i:s');

        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();
        GerarTexto::where('publicar', true)->update(['updated_at' => $data_antiga]);

        $this->assertEquals($data_antiga, GerarTexto::ultimaAtualizacao('carta-servicos'));

        GerarTexto::first()->update(['publicar' => false]);
        $this->assertEquals(now()->format('Y-m-d H:i:s'), GerarTexto::ultimaAtualizacao('carta-servicos'));
    }

    /** @test */
    public function get_layout_cliente()
    {
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();
        $resultado = GerarTexto::where('tipo_doc', 'carta-servicos')->get();

        $this->assertEquals($resultado, GerarTexto::getLayoutCliente($resultado, 'carta-servicos'));

        // Layout com collapse

        $resultado = GerarTexto::where('tipo_doc', 'prestacao-contas')->get();
        $this->assertEquals('<p><i>Informações sendo atualizadas.</i></p>', GerarTexto::getLayoutCliente($resultado, 'prestacao-contas'));

        $textos = factory('App\GerarTexto', 7)->states('sumario_publicado', 'prestacao-contas')->create();
        $resultado = GerarTexto::where('tipo_doc', 'carta-servicos')->get();

        $this->assertEquals("string", gettype(GerarTexto::getLayoutCliente($resultado, 'prestacao-contas')));
        $this->assertNotEquals('<p><i>Informações sendo atualizadas.</i></p>', GerarTexto::getLayoutCliente($resultado, 'prestacao-contas'));
    }

    /** @test */
    public function titulo_numerado()
    {
        $texto = factory('App\GerarTexto')->create();

        $this->assertTrue($texto->tituloNumerado());

        $texto = factory('App\GerarTexto')->create([
            'com_numeracao' => false
        ]);

        $this->assertTrue(!$texto->tituloNumerado());

        $texto = factory('App\GerarTexto')->create([
            'tipo' => GerarTexto::TIPO_SUBTITULO,
            'nivel' => 1
        ]);

        $this->assertTrue(!$texto->tituloNumerado());

        $texto = factory('App\GerarTexto')->create([
            'nivel' => 1
        ]);

        $this->assertTrue($texto->tituloNumerado());

        $texto = factory('App\GerarTexto')->create([
            'tipo' => GerarTexto::TIPO_SUBTITULO,
        ]);

        $this->assertTrue($texto->tituloNumerado());
    }

    /** @test */
    public function tipo_titulo()
    {
        $texto = factory('App\GerarTexto')->create();

        $this->assertTrue($texto->tipoTitulo());

        $texto = factory('App\GerarTexto')->create([
            'tipo' => GerarTexto::TIPO_SUBTITULO,
        ]);

        $this->assertTrue($texto->tipoTitulo());

        $texto = factory('App\GerarTexto')->create([
            'tipo' => GerarTexto::TIPO_SUBTITULO,
            'nivel' => 1
        ]);

        $this->assertTrue(!$texto->tipoTitulo());

        $texto = factory('App\GerarTexto')->create([
            'nivel' => 1
        ]);

        $this->assertTrue($texto->tipoTitulo());
    }

    /** @test */
    public function indice_formatada()
    {
        $texto = factory('App\GerarTexto')->create();

        $this->assertEquals('', $texto->indiceFormatada());

        $texto = factory('App\GerarTexto')->create([
            'indice' => '1',
        ]);

        $this->assertEquals('1. ', $texto->indiceFormatada());
    }

    /** @test */
    public function titulo_formatado()
    {
        $texto = factory('App\GerarTexto')->create();

        $this->assertEquals('' . $texto->texto_tipo, $texto->tituloFormatado());

        $texto = factory('App\GerarTexto')->create([
            'indice' => '1',
        ]);

        $this->assertEquals('1. ' . $texto->texto_tipo, $texto->tituloFormatado());
    }

    /** @test */
    public function subtitulo_formatado()
    {
        $texto = factory('App\GerarTexto')->create([
            'tipo' => GerarTexto::TIPO_SUBTITULO,
            'nivel' => 1,
            'indice' => '1.1.1',
        ]);

        $this->assertEquals($texto->indice . ' - ' . $texto->texto_tipo, $texto->subtituloFormatado());
    }

    /** @test */
    public function possui_conteudo()
    {
        $texto = factory('App\GerarTexto')->create();

        $this->assertTrue($texto->possuiConteudo());

        $texto = factory('App\GerarTexto')->create([
            'conteudo' => null
        ]);

        $this->assertTrue(!$texto->possuiConteudo());

        $texto = factory('App\GerarTexto')->states('prestacao-contas')->create();

        $this->assertTrue(!$texto->possuiConteudo());

        $texto = factory('App\GerarTexto')->states('prestacao-contas')->create([
            'conteudo' => str_replace('http:', 'https:', $this->faker()->url)
        ]);

        $this->assertTrue($texto->possuiConteudo());
    }

    /** @test */
    public function texto_tipo_slug()
    {
        $texto = factory('App\GerarTexto')->create([
            'texto_tipo' => 'TESTE TEXTO SLUG'
        ]);

        $this->assertEquals('teste-texto-slug', $texto->textoTipoSlug());
    }

    /** @test */
    public function texto_tipo_studly()
    {
        $texto = factory('App\GerarTexto')->create([
            'texto_tipo' => 'TESTE TEXTO STUDLY'
        ]);

        $this->assertEquals('TesteTextoStudly', $texto->textoTipoStudly());
    }

    /** @test */
    public function existe_img()
    {
        $texto = factory('App\GerarTexto')->create([
            'texto_tipo' => 'INFORMAÇÕES GERAIS'
        ]);

        $this->assertTrue(!$texto->existeImg());

        $texto = factory('App\GerarTexto')->states('prestacao-contas')->create([
            'texto_tipo' => 'INFORMAÇÕES GERAIS'
        ]);

        $this->assertTrue($texto->existeImg());
    }

    /** @test */
    public function get_img_HTML()
    {
        $texto = factory('App\GerarTexto')->create([
            'texto_tipo' => 'INFORMAÇÕES GERAIS'
        ]);

        $this->assertEquals(null, $texto->getImgHTML());

        $texto = factory('App\GerarTexto')->states('prestacao-contas')->create([
            'texto_tipo' => 'INFORMAÇÕES GERAIS'
        ]);

        $this->assertEquals('<img src="'. asset('img/icone-' . $texto->textoTipoSlug() . '.png') . '" width="320" height="143" />', $texto->getImgHTML());
    }

    /** 
     * =======================================================================================================
     * TESTES NO GERARTEXTOSERVICE
     * =======================================================================================================
     */

    /** @test */
    public function limite_criar_textos()
    {
        $service = new GerarTextoService;
        $this->assertEquals(10, $service->limiteCriarTextos());
    }

    /** @test */
    public function view()
    {
        $this->signInAsAdmin();

        $texto = factory('App\GerarTexto')->create();

        $service = new GerarTextoService;

        $final = $service->view('carta-servicos');

        $this->assertEquals("Illuminate\Database\Eloquent\Collection", get_class($final['resultado']));
        $this->assertEquals([
            "id", "tipo", "texto_tipo", "com_numeracao", "ordem", "nivel", "indice", "publicar"
        ], array_keys($final['resultado']->get(0)->attributesToArray()));
        $this->assertEquals("vertical", $final['orientacao_sumario']);
        $this->assertEquals(10, $final['limite_criar_textos']);

        $final = $service->view('carta-servicos', 1);

        $this->assertEquals("Illuminate\Database\Eloquent\Collection", get_class($final['resultado']));
        $this->assertEquals([
            "tipo", "texto_tipo", "com_numeracao", "nivel", "indice", "conteudo"
        ], array_keys($final['resultado']->get(0)->attributesToArray()));
        $this->assertEquals("vertical", $final['orientacao_sumario']);
        $this->assertEquals(10, $final['limite_criar_textos']);
    }

    /** @test */
    public function criar_pelo_service()
    {
        $this->signInAsAdmin();

        $texto = factory('App\GerarTexto')->create();

        $service = new GerarTextoService;

        $final = $service->criar('carta-servicos');

        $this->assertEquals(['novo_texto' => 2], $final['novo_texto']);
        $this->assertEquals(GerarTexto::class, get_class($final));

        $final = $service->criar('carta-servicos', 3);

        $this->assertEquals(['novos_textos' => [3, 4, 5]], $final->novo_texto);
        $this->assertEquals('TÍTULO DO TEXTO...', $final->texto_tipo);
    }

    /** @test */
    public function update()
    {
        $this->signInAsAdmin();

        $texto = factory('App\GerarTexto', 5)->create();

        $service = new GerarTextoService;

        // atualizar campos
        $dados = [
            'tipo' => 'Subtítulo',
            'texto_tipo' => $this->faker()->sentence(100),
            'com_numeracao' => 1,
            'nivel' => 1,
            'conteudo' => $this->faker()->sentence(300)
        ];

        $this->assertEquals(' - ' . $dados['texto_tipo'], $service->update('carta-servicos', $dados, 1)->texto_tipo);

        $dados['com_numeracao'] = 0;
        $this->assertEquals(false, $service->update('carta-servicos', $dados, 3)->com_numeracao);

        // atualizar ordem e indice
        $dados = [3, 5, 4, 2, 1];

        $this->assertTrue($service->update('carta-servicos', $dados));

        $this->assertEquals(1, GerarTexto::orderBy('ordem')->find(3)->ordem);
        $this->assertEquals(2, GerarTexto::orderBy('ordem')->find(5)->ordem);
        $this->assertEquals(3, GerarTexto::orderBy('ordem')->find(4)->ordem);
        $this->assertEquals(4, GerarTexto::orderBy('ordem')->find(2)->ordem);
        $this->assertEquals(5, GerarTexto::orderBy('ordem')->find(1)->ordem);

        $this->assertEquals(null, GerarTexto::orderBy('ordem')->find(3)->indice);
        $this->assertEquals('1', GerarTexto::orderBy('ordem')->find(5)->indice);
        $this->assertEquals('2', GerarTexto::orderBy('ordem')->find(4)->indice);
        $this->assertEquals('3', GerarTexto::orderBy('ordem')->find(2)->indice);
        $this->assertEquals('3.1', GerarTexto::orderBy('ordem')->find(1)->indice);
    }

    /** @test */
    public function publicar()
    {
        $this->signInAsAdmin();

        $texto = factory('App\GerarTexto')->create();

        $service = new GerarTextoService;

        $this->assertTrue((bool) $service->publicar('carta-servicos', true));
        $this->assertTrue((bool) GerarTexto::first()->publicar);

        $this->assertTrue((bool) $service->publicar('carta-servicos', false));
        $this->assertFalse((bool) GerarTexto::first()->publicar);
    }

    /** @test */
    public function excluir()
    {
        $this->signInAsAdmin();

        $texto = factory('App\GerarTexto', 5)->create();

        $service = new GerarTextoService;

        $this->assertEquals([2, 4], $service->excluir('carta-servicos', [2, 4]));
        $this->assertEquals([1, 2, 3], GerarTexto::orderBy('ordem')->get()->pluck('ordem')->all());
    }

    /** @test */
    public function nao_excluir_sem_ids()
    {
        $this->signInAsAdmin();

        $texto = factory('App\GerarTexto', 5)->create();

        $service = new GerarTextoService;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Deve existir no mínimo um texto.');

        $service->excluir('carta-servicos');
    }

    /** @test */
    public function nao_excluir_com_total_ids_maior_ou_igual()
    {
        $this->signInAsAdmin();

        $texto = factory('App\GerarTexto', 5)->create();

        $service = new GerarTextoService;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Deve existir no mínimo um texto.');

        $service->excluir('carta-servicos', [1, 2, 3, 4, 5]);
    }

    /** @test */
    public function show_pelo_service_sem_publicar()
    {
        $user = $this->signInAsAdmin();

        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create([
            'com_numeracao' => false
        ]);

        $service = new GerarTextoService;
        $resultado = GerarTexto::resultadoByDoc('carta-servicos');

        $final = $service->show('carta-servicos');
        $this->assertEquals([], $final['textos']);
        $this->assertEquals(null, $final['btn_anterior']);
        $this->assertEquals(null, $final['btn_proximo']);
        $this->assertEquals($resultado, $final['resultado']);

        $resultado = GerarTexto::resultadoByDoc('carta-servicos', $user);

        $final = $service->show('carta-servicos', null, $user);
        $this->assertEquals([], $final['textos']);
        $this->assertEquals(null, $final['btn_anterior']);
        $this->assertEquals(null, $final['btn_proximo']);
        $this->assertEquals($resultado, $final['resultado']);

        // Com ID
        $resultado = GerarTexto::resultadoByDoc('carta-servicos', $user);

        $final = $service->show('carta-servicos', 1, $user);
        $this->assertEquals(GerarTexto::find(1), $final['textos'][0]);
        $this->assertEquals(null, $final['btn_anterior']);
        $this->assertEquals(route('carta-servicos', 5), $final['btn_proximo']);

        $final = $service->show('carta-servicos', 3, $user);
        $this->assertEquals(GerarTexto::find(3), $final['textos'][0]);
        $this->assertEquals(route('carta-servicos', 1), $final['btn_anterior']);
        $this->assertEquals(route('carta-servicos', 5), $final['btn_proximo']);
    }

    /** @test */
    public function show_pelo_service_com_id_inexistente_ou_sem_publicar()
    {
        $textos = factory('App\GerarTexto', 5)->create([
            'com_numeracao' => false
        ]);

        $service = new GerarTextoService;

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('No query results for model [App\GerarTexto].');

        $service->show('carta-servicos', 3);
    }

    /** @test */
    public function buscar()
    {
        $user = $this->signInAsAdmin();

        factory('App\GerarTexto', 5)->states('sumario_publicado')->create([
            'com_numeracao' => false
        ]);

        GerarTexto::find(1)->update(['texto_tipo' => GerarTexto::find(1)->texto_tipo . ' teste.']);
        GerarTexto::find(2)->update(['conteudo' => GerarTexto::find(2)->conteudo . ' teste.']);

        $service = new GerarTextoService;

        $resultado = GerarTexto::resultadoByDoc('carta-servicos', $user);

        $final = $service->buscar('carta-servicos', 'teste', $user);
        $this->assertEquals(2, $final['busca']->count());

        $final = $service->buscar('carta-servicos', null, $user);
        $this->assertEquals(0, $final['busca']->count());

        $final = $service->buscar('carta-servicos', 'te', $user);
        $this->assertEquals(0, $final['busca']->count());
    }
}
