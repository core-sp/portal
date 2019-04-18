<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Concurso;
use App\Curso;
use App\Licitacao;
use App\Noticia;
use App\Pagina;
use App\Regional;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewTest extends TestCase
{
    public function testViewAgendamento()
    {
        $response = $this->get('/agendamento');
        $response->assertStatus(200);
    }

    public function testViewBdo()
    {
        $response = $this->get('/balcao-de-oportunidades');
        $response->assertStatus(200);
    }

    public function testViewBusca()
    {
        $response = $this->get('/busca');
        if(isset($_GET['busca'])) {
            $response->assertStatus(200);
        } else {
            $response->assertStatus(302);
        }
    }

    public function testViewConcurso()
    {
        $concurso = Concurso::first();
        $response = $this->get('/concurso/'.$concurso->idconcurso);
        $response->assertStatus(200);
    }

    public function testViewConcursos()
    {
        $response = $this->get('/concursos');
        $response->assertStatus(200);
    }

    public function testViewCursoInscricao()
    {
        $curso = Curso::first();
        $response = $this->get('/curso/inscricao/'.$curso->idcurso);
        $response->assertStatus(200);
    }

    public function testViewCurso()
    {
        $curso = Curso::first();
        $response = $this->get('/curso/'.$curso->idcurso);
        $response->assertStatus(200);
    }

    public function testViewCursosAnteriores()
    {
        $response = $this->get('/cursos-anteriores');
        $response->assertStatus(200);
    }

    public function testViewCursos()
    {
        $response = $this->get('/cursos');
        $response->assertStatus(200);
    }

    public function testViewHome()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function testViewLicitacao()
    {
        $licitacao = Licitacao::first();
        $response = $this->get('/licitacao/'.$licitacao->idlicitacao);
        $response->assertStatus(200);
    }

    public function testViewLicitacoes()
    {
        $response = $this->get('/licitacoes');
        $response->assertStatus(200);
    }

    public function testViewNoticia()
    {
        $noticia = Noticia::first();
        $response = $this->get('/noticia/'.$noticia->slug);
        $response->assertStatus(200);
    }

    public function testViewNoticias()
    {
        $response = $this->get('/noticias');
        $response->assertStatus(200);
    }

    public function testViewPagina()
    {
        $pagina = Pagina::first();
        if(!isset($pagina->idcategoria))
            $response = $this->get('/'.$pagina->slug);
        else
            $response = $this->get('/'.$pagina->paginacategoria->nome.'/'.$pagina->slug);
        $response->assertStatus(200);
    }

    public function testViewRegionais()
    {
        $response = $this->get('/seccionais');
        $response->assertStatus(200);
    }

    public function testViewRegional()
    {
        $regional = Regional::first();
        $response = $this->get('/seccional/'.$regional->idregional);
        $response->assertStatus(200);
    }
}
