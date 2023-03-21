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
            'UserExterno' => new \App\Services\UserExternoService(),
            'PreRegistro' => new \App\Services\PreRegistroService(new \App\Services\PreRegistroAdminSubService),
        ];
        $mediador = $this->app->make(MediadorServiceInterface::class);

        foreach($servicos as $key => $servico)
            $this->assertEquals($mediador->getService($key), $servico);
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

    /** @test */
    public function mediador_interface_get_instace_user_externo_service()
    {
        $servico = new \App\Services\UserExternoService();
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('UserExterno'), $servico);
    }

    /** @test */
    public function mediador_interface_get_instace_pre_registro_service()
    {
        $servico = new \App\Services\PreRegistroService(new \App\Services\PreRegistroAdminSubService);
        $mediador = $this->app->make(MediadorServiceInterface::class);
        $this->assertEquals($mediador->getService('PreRegistro'), $servico);

        $servico = new \App\Services\PreRegistroAdminSubService();
        $this->assertEquals($mediador->getService('PreRegistro')->getAdminService(), $servico);
    }
}
