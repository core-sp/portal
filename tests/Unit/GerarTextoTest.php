<?php

namespace Tests\Unit;

use App\GerarTexto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

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
        $textos = factory('App\GerarTexto', 5)->states('sumario_publicado')->create();
        $textos = factory('App\GerarTexto', 7)->states('sumario_publicado', 'prestacao-contas')->create();

        $resultado = GerarTexto::resultadoByDoc('carta-servicos');
        $this->assertEquals(5, $resultado->count());
        $resultado = GerarTexto::resultadoByDoc('prestacao-contas');
        $this->assertEquals(7, $resultado->count());

        $resultado = GerarTexto::resultadoByDoc('carta-servicos', $user);
        $this->assertEquals(5, $resultado->count());
        $resultado = GerarTexto::resultadoByDoc('prestacao-contas', $user);
        $this->assertEquals(7, $resultado->count());

        GerarTexto::where('publicar', true)->update(['publicar' => false]);

        $resultado = GerarTexto::resultadoByDoc('carta-servicos');
        $this->assertEquals(0, $resultado->count());
        $resultado = GerarTexto::resultadoByDoc('prestacao-contas');
        $this->assertEquals(0, $resultado->count());

        $resultado = GerarTexto::resultadoByDoc('carta-servicos', $user);
        $this->assertEquals(5, $resultado->count());
        $resultado = GerarTexto::resultadoByDoc('prestacao-contas', $user);
        $this->assertEquals(7, $resultado->count());
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
}
