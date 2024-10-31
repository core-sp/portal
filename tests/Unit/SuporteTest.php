<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\SuporteIp;
use App\Services\SuporteService;

class SuporteTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** 
     * =======================================================================================================
     * TESTES SUPORTEIP MODEL
     * =======================================================================================================
     */

    // TESTES DO SERVIÇO DE BLOQUEIO DE IP

    /** @test */
    public function pode_atualizar_tentativa()
    {
        $ip = factory('App\SuporteIp')->create();

        $this->assertTrue($ip->isUpdateTentativa());

        $ip = factory('App\SuporteIp')->states('bloqueado')->create();

        $this->assertFalse($ip->isUpdateTentativa());

        $ip = factory('App\SuporteIp')->states('liberado')->create();

        $this->assertFalse($ip->isUpdateTentativa());
    }

    /** @test */
    public function esta_liberado()
    {
        $ip = factory('App\SuporteIp')->create();

        $this->assertFalse($ip->isLiberado());

        $ip = factory('App\SuporteIp')->states('bloqueado')->create();

        $this->assertFalse($ip->isLiberado());

        $ip = factory('App\SuporteIp')->states('liberado')->create();

        $this->assertTrue($ip->isLiberado());
    }

    /** @test */
    public function esta_desbloqueado()
    {
        $ip = factory('App\SuporteIp')->create();

        $this->assertTrue($ip->isDesbloqueado());

        $ip = factory('App\SuporteIp')->states('bloqueado')->create();

        $this->assertFalse($ip->isDesbloqueado());

        $ip = factory('App\SuporteIp')->states('liberado')->create();

        $this->assertFalse($ip->isDesbloqueado());
    }

    /** @test */
    public function esta_bloqueado()
    {
        $ip = factory('App\SuporteIp')->create();

        $this->assertFalse($ip->isBloqueado());

        $ip = factory('App\SuporteIp')->states('bloqueado')->create();

        $this->assertTrue($ip->isBloqueado());

        $ip = factory('App\SuporteIp')->states('liberado')->create();

        $this->assertFalse($ip->isBloqueado());
    }

    /** @test */
    public function atualizar_tentativa()
    {
        $ip = factory('App\SuporteIp')->create();

        $this->assertEquals(2, $ip->updateTentativa()->tentativas);
        $this->assertEquals(SuporteIp::DESBLOQUEADO, $ip->updateTentativa()->status);

        $ip = factory('App\SuporteIp')->create([
            'updated_at' => now()->subDay()->format('Y-m-d')
        ]);

        $this->assertEquals(1, $ip->updateTentativa()->tentativas);
        $this->assertEquals(SuporteIp::DESBLOQUEADO, $ip->updateTentativa()->status);

        $ip = factory('App\SuporteIp')->create([
            'tentativas' => SuporteIp::TOTAL_TENTATIVAS
        ]);

        $this->assertEquals(SuporteIp::TOTAL_TENTATIVAS, $ip->updateTentativa()->tentativas);
        $this->assertEquals(SuporteIp::BLOQUEADO, $ip->updateTentativa()->status);

        $ip = factory('App\SuporteIp')->states('bloqueado')->create();

        $this->assertEquals(SuporteIp::TOTAL_TENTATIVAS, $ip->updateTentativa()->tentativas);
        $this->assertEquals(SuporteIp::BLOQUEADO, $ip->updateTentativa()->status);

        $ip = factory('App\SuporteIp')->states('liberado')->create();

        $this->assertEquals(1, $ip->updateTentativa()->tentativas);
        $this->assertEquals(SuporteIp::LIBERADO, $ip->updateTentativa()->status);
    }

    /** 
     * =======================================================================================================
     * TESTES SUPORTESERVICE
     * =======================================================================================================
     */

    // TESTES DO SERVIÇO DE BLOQUEIO DE IP

    /** @test */
    public function ips_bloqueados()
    {
        factory('App\SuporteIp', 7)->create();
        $service = new SuporteService;

        $this->assertEquals(0, $service->ipsBloqueados()->count());

        $ip_block = factory('App\SuporteIp')->states('bloqueado')->create();

        $this->assertEquals(1, $service->ipsBloqueados()->count());

        $ip = factory('App\SuporteIp')->states('liberado')->create();

        $this->assertEquals(1, $service->ipsBloqueados()->count());

        $this->assertTrue(is_null($service->ipsBloqueados(1)));
        $this->assertTrue(is_null($service->ipsBloqueados($ip->ip)));
        $this->assertFalse(is_null($service->ipsBloqueados($ip_block->ip)));
    }

    /** @test */
    public function ips()
    {
        $service = new SuporteService;

        $final = $service->ips();

        $this->assertEquals(0, $final['ips']->total());
        $this->assertEquals((object) [
            'mostra' => 'suporte_ips',
            'singular' => 'Tabela de IPs bloqueados e liberados',
            'singulariza' => 'os ips',
        ], $final['variaveis']);

        factory('App\SuporteIp', 7)->states('bloqueado')->create();
        factory('App\SuporteIp', 2)->states('liberado')->create();
        factory('App\SuporteIp', 10)->create();

        $final = $service->ips();

        $this->assertEquals(9, $final['ips']->total());
        $this->assertEquals((object) [
            'mostra' => 'suporte_ips',
            'singular' => 'Tabela de IPs bloqueados e liberados',
            'singulariza' => 'os ips',
        ], $final['variaveis']);
    }

    /** @test */
    public function bloquear_ip()
    {
        $service = new SuporteService;

        $ip = $this->faker()->ipv4;

        $this->assertEquals($ip, $service->bloquearIp($ip)->ip);
        $this->assertTrue($service->bloquearIp($ip)->isDesbloqueado());

        $ip = factory('App\SuporteIp')->states('bloqueado')->create()->ip;

        $this->assertEquals($ip, $service->bloquearIp($ip)->ip);
        $this->assertTrue($service->bloquearIp($ip)->isBloqueado());
        $this->assertEquals(SuporteIp::TOTAL_TENTATIVAS, $service->bloquearIp($ip)->tentativas);

        $ip = factory('App\SuporteIp')->states('liberado')->create()->ip;

        $this->assertEquals($ip, $service->bloquearIp($ip)->ip);
        $this->assertTrue($service->bloquearIp($ip)->isLiberado());
        $this->assertEquals(0, $service->bloquearIp($ip)->tentativas);

        $ip = factory('App\SuporteIp')->raw()['ip'];
        $total = SuporteIp::TOTAL_TENTATIVAS + 3;
        for($i = 1; $i <= $total; $i++)
        {
            $final = $service->bloquearIp($ip);

            if($i > SuporteIp::TOTAL_TENTATIVAS){
                $this->assertTrue($final->isBloqueado());
                $this->assertEquals(SuporteIp::TOTAL_TENTATIVAS, $final->tentativas);
                continue;
            }

            $this->assertEquals($ip, $final->ip);
            $this->assertTrue($final->isDesbloqueado());
            $this->assertEquals($i, $final->tentativas);
        }
    }

    /** @test */
    public function liberar_ip()
    {
        $service = new SuporteService;

        $user = $this->signInAsAdmin();

        $ip = $this->faker()->ipv4;

        $this->assertFalse($service->liberarIp($ip));

        $ip = factory('App\SuporteIp')->create()->ip;

        $this->assertTrue($service->liberarIp($ip));

        $ip = factory('App\SuporteIp')->create()->ip;

        $this->assertFalse($service->liberarIp($ip, $user));

        $ip = factory('App\SuporteIp')->states('liberado')->create()->ip;

        $this->assertFalse($service->liberarIp($ip));

        $ip = factory('App\SuporteIp')->states('bloqueado')->create()->ip;

        $this->assertFalse($service->liberarIp($ip));

        $ip = factory('App\SuporteIp')->states('bloqueado')->create()->ip;

        $this->assertTrue($service->liberarIp($ip, $user));
    }
}