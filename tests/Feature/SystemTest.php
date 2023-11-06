<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Contracts\MediadorServiceInterface;

class SystemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function mediador_interface_get_instace_all_services()
    {
        $servicos = [
            'Suporte' => new \App\Services\SuporteService(),
            'PlantaoJuridico' => new \App\Services\PlantaoJuridicoService(),
            'Regional' => new \App\Services\RegionalService(),
            'TermoConsentimento' => new \App\Services\TermoConsentimentoService(),
            'Agendamento' => new \App\Services\AgendamentoService(),
            'Licitacao' => new \App\Services\LicitacaoService(),
            'Fiscalizacao' => new \App\Services\FiscalizacaoService(),
            'Post' => new \App\Services\PostService(),
            'Noticia' => new \App\Services\NoticiaService(),
            'Cedula' => new \App\Services\CedulaService(),
            'Representante' => new \App\Services\RepresentanteService(),
            'SalaReuniao' => new \App\Services\SalaReuniaoService(),
            'Aviso' => new \App\Services\AvisoService(),
            'Curso' => new \App\Services\CursoService(),
        ];
        $mediador = $this->app->make(MediadorServiceInterface::class);

        foreach($servicos as $key => $servico)
            $this->assertEquals($mediador->getService($key), $servico);
    }

    /** @test */
    public function mediador_interface_cannot_loading_services()
    {
        $servico = new \App\Services\SuporteService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals(count(get_object_vars($mediador)), 0);
    }

    /** @test */
    public function cannot_instance_services_in_mediador_interface()
    {
        $servicosCarregados = [
            'Mediador' => 'App\Contracts\MediadorServiceInterface',
            'Suporte' => 'App\Contracts\SuporteServiceInterface',
        ];
        $servicosNaoCarregados = [
            'PlantaoJuridico' => 'App\Contracts\PlantaoJuridicoServiceInterface',
            'Regional' => 'App\Contracts\RegionalServiceInterface',
            'TermoConsentimento' => 'App\Contracts\TermoConsentimentoServiceInterface',
            'Agendamento' => 'App\Contracts\AgendamentoServiceInterface',
            'Licitacao' => 'App\Contracts\LicitacaoServiceInterface',
            'Fiscalizacao' => 'App\Contracts\FiscalizacaoServiceInterface',
            'Post' => 'App\Contracts\PostServiceInterface',
            'Noticia' => 'App\Contracts\NoticiaServiceInterface',
            'Cedula' => 'App\Contracts\CedulaServiceInterface',
            'Representante' => 'App\Contracts\RepresentanteServiceInterface',
            'SalaReuniao' => 'App\Contracts\SalaReuniaoServiceInterface',
            'Aviso' => 'App\Contracts\AvisoServiceInterface',
            'Curso' => 'App\Contracts\CursoServiceInterface',
        ];

        $this->get('/simulador')->assertOk();

        foreach($servicosNaoCarregados as $key => $servico)
            $this->assertEquals($this->app->resolved($servico), false);

        foreach($servicosCarregados as $key => $servico)
            $this->assertEquals($this->app->resolved($servico), true);
    }

    /** @test */
    public function mediador_interface_not_find_service()
    {
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Serviço Erro não encontrado no Sistema');
        $mediador->getService('Erro');
    }

    /** @test */
    public function mediador_interface_exception_sub_service()
    {
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Serviço SuporteAdminSub não encontrado no Sistema');
        $mediador->getService('SuporteAdminSub');
    }

    /** @test */
    public function log_is_generated_when_mediador_interface_not_find_service()
    {
        try{
            $mediador = $this->app->make(MediadorServiceInterface::class);
            $mediador->getService('Erro');
        }
        catch(\Exception $e){
            $log = tailCustom(storage_path($this->pathLogErros()));
            $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.ERROR: ';
            $txt = $inicio . '[Erro: Target class [App\Contracts\ErroServiceInterface] does not exist.], [Código: 0], ';
            $txt .= '[Arquivo: /home/vagrant/Workspace/portal/vendor/laravel/framework/src/Illuminate/Container/Container.php], [Linha: 805]';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function log_is_generated_when_mediador_interface_get_sub_service()
    {
        try{
            $mediador = $this->app->make(MediadorServiceInterface::class);
            $mediador->getService('SuporteAdminSub');
        }
        catch(\Exception $e){
            $log = tailCustom(storage_path($this->pathLogErros()));
            $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.ERROR: ';
            $txt = $inicio . '[Erro: Sub Service deve ser chamado pelo serviço principal], [Código: 500], ';
            $txt  .= '[Arquivo: /home/vagrant/Workspace/portal/app/Services/MediadorService.php], [Linha: 14]';
            $this->assertStringContainsString($txt, $log);
        }
    }

    /** @test */
    public function mediador_interface_get_instace_suporte_service()
    {
        $servico = new \App\Services\SuporteService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('Suporte'), $servico);
    }

    /** @test */
    public function mediador_interface_get_instace_plantao_juridico_service()
    {
        $servico = new \App\Services\PlantaoJuridicoService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('PlantaoJuridico'), $servico);
    }

    /** @test */
    public function mediador_interface_get_instace_regional_service()
    {
        $servico = new \App\Services\RegionalService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('Regional'), $servico);
    }

    /** @test */
    public function mediador_interface_get_instace_termo_consentimento_service()
    {
        $servico = new \App\Services\TermoConsentimentoService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('TermoConsentimento'), $servico);
    }

    /** @test */
    public function mediador_interface_get_instace_agendamento_service()
    {
        $servico = new \App\Services\AgendamentoService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('Agendamento'), $servico);
    }

    /** @test */
    public function mediador_interface_get_instace_licitacao_service()
    {
        $servico = new \App\Services\LicitacaoService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('Licitacao'), $servico);
    }

    /** @test */
    public function mediador_interface_get_instace_fiscalizacao_service()
    {
        $servico = new \App\Services\FiscalizacaoService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('Fiscalizacao'), $servico);
    }

    /** @test */
    public function mediador_interface_get_instace_post_service()
    {
        $servico = new \App\Services\PostService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('Post'), $servico);
    }

    /** @test */
    public function mediador_interface_get_instace_noticia_service()
    {
        $servico = new \App\Services\NoticiaService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('Noticia'), $servico);
    }

    /** @test */
    public function mediador_interface_get_instace_cedula_service()
    {
        $servico = new \App\Services\CedulaService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('Cedula'), $servico);
    }

    /** @test */
    public function mediador_interface_get_instace_representante_service()
    {
        $servico = new \App\Services\RepresentanteService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('Representante'), $servico);
    }

    public function mediador_interface_get_instace_sala_reuniao_service()
    {
        $servico = new \App\Services\SalaReuniaoService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('SalaReuniao'), $servico);
    }

    /** @test */
    public function mediador_interface_get_instace_aviso_service()
    {
        $servico = new \App\Services\AvisoService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('Aviso'), $servico);
    }

    /** @test */
    public function mediador_interface_get_instace_curso_service()
    {
        $servico = new \App\Services\CursoService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('Curso'), $servico);
    }
}
