<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Concurso;
use App\Curso;
use App\Licitacao;
use App\Noticia;
use App\Pagina;
use App\Regional;
use App\Http\Controllers\CursoInscritoController;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewTest extends TestCase
{
    public function testViewAgendamento()
    {
        $this->get('/agendamento')->assertStatus(200);
    }

    public function testViewBdo()
    {
        $this->get('/balcao-de-oportunidades')->assertStatus(200);
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
        $this->get('/concurso/'.$concurso->idconcurso)->assertStatus(200);
    }

    public function testViewConcursos()
    {
        $this->get('/concursos')->assertStatus(200);
    }

    public function testViewCursoInscricao()
    {
        $curso = Curso::select('idcurso')->first();
        if(CursoInscritoController::permiteInscricao($curso->idcurso))
            $this->get('/curso/inscricao/'.$curso->idcurso)->assertStatus(200);
    }

    public function testViewCurso()
    {
        $curso = Curso::first();
        $this->get('/curso/'.$curso->idcurso)->assertStatus(200);
    }

    public function testViewCursosAnteriores()
    {
        $this->get('/cursos-anteriores')->assertStatus(200);
    }

    public function testViewCursos()
    {
        $this->get('/cursos')->assertStatus(200);
    }

    public function testViewHome()
    {
        $this->get('/')->assertStatus(200);
    }

    public function testViewLicitacao()
    {
        $licitacao = Licitacao::first();
        $this->get('/licitacao/'.$licitacao->idlicitacao)->assertStatus(200);
    }

    public function testViewLicitacoes()
    {
        $this->get('/licitacoes')->assertStatus(200);
    }

    public function testViewNoticia()
    {
        $noticia = Noticia::first();
        $this->get('/noticia/'.$noticia->slug)->assertStatus(200);
    }

    public function testViewNoticias()
    {
        $this->get('/noticias')->assertStatus(200);
    }

    public function testViewPagina()
    {
        $pagina = Pagina::first();
        if(!isset($pagina->idcategoria))
            $this->get('/'.$pagina->slug)->assertStatus(200);
        else
            $this->get('/'.$pagina->paginacategoria->nome.'/'.$pagina->slug)->assertStatus(200);
    }

    public function testViewRegionais()
    {
        $this->get('/seccionais')->assertStatus(200);
    }

    public function testViewRegional()
    {
        $regional = Regional::first();
        $this->get('/seccional/'.$regional->idregional)->assertStatus(200);
    }
}
