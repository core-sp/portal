<?php

namespace App\Services;

use App\Contracts\PlantaoJuridicoServiceInterface;
use App\Contracts\PlantaoJuridicoBloqueioSubServiceInterface;
use App\PlantaoJuridico;
use Carbon\Carbon;
use App\Events\CrudEvent;

class PlantaoJuridicoService implements PlantaoJuridicoServiceInterface {

    private $variaveis;
    private $bloqueio;

    public function __construct(PlantaoJuridicoBloqueioSubServiceInterface $bloqueio)
    {
        $this->variaveis = [
            'singular' => 'plantão jurídico',
            'singulariza' => 'o plantão jurídico',
            'plural' => 'plantões jurídicos',
            'pluraliza' => 'plantão jurídico',
            'form' => 'plantao_juridico',
        ];

        $this->bloqueio = $bloqueio;
    }

    private function tabelaCompleta($user, $resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Id',
            'Regional',
            'Status do Plantão',
            'Período',
            'Horários',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userPodeEditar = $user->can('updateOther', $user);
        foreach($resultados as $resultado) {
            $msgPrazoExpirado = $resultado->expirou() ? '<br><small class="text-danger"><strong>Período expirado, DESATIVE o plantão</strong></small>' : '';
            $msgAtivado = '<span class="text-success">Ativado</span><br><small>com '.$resultado->qtd_advogados.' advogado(s)</small>';
            $acoes = '';
            if($userPodeEditar)
                $acoes = '<a href="' .route('plantao.juridico.editar.view', $resultado->id). '" class="btn btn-sm btn-primary">Editar</a> ';
            $conteudo = [
                $resultado->id,
                $resultado->regional->regional,
                $resultado->ativado() ? $msgAtivado : '<span class="text-danger">Desativado</span>',
                isset($resultado->dataInicial) && isset($resultado->dataFinal) ? onlyDate($resultado->dataInicial).' - '.onlyDate($resultado->dataFinal).$msgPrazoExpirado : '',
                $resultado->horarios,
                $acoes
            ];
            array_push($contents, $conteudo);
        }

        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];
        $tabela = montaTabela($headers, $contents, $classes);
        
        return $tabela;
    }

    public function listar($user)
    {
        $plantoes = PlantaoJuridico::with('regional')
        ->orderBy('qtd_advogados', 'DESC')
        ->get();

        return [
            'tabela' => $this->tabelaCompleta($user, $plantoes),
            'resultados' => $plantoes,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($id)
    {
        $plantao = PlantaoJuridico::with('regional')->findOrFail($id);
        $dataInicial = Carbon::parse($plantao->dataInicial);
        $inicial = $dataInicial->gte(Carbon::today()) ? $plantao->dataInicial : Carbon::today()->format('Y-m-d');
        
        $agendados = $plantao->expirou() ? null :
             $plantao->regional
                ->agendamentos()
                ->select('dia', 'hora')
                ->where('tiposervico', 'LIKE', 'Plantão Jurídico%')
                ->whereNull('status')
                ->whereBetween('dia', [$inicial, $plantao->dataFinal])
                ->orderby('dia')
                ->orderby('hora')
                ->get()
                ->groupBy([
                    'dia',
                    function ($item) {
                        return $item['hora'];
                    },
                ], $preserveKeys = false);
        
        return [
            'resultado' => $plantao,
            'variaveis' => (object) $this->variaveis,
            'agendamentos' => $agendados
        ];
    }

    public function save($request, $id)
    {
        PlantaoJuridico::findOrFail($id)->update([
            'qtd_advogados' => $request->qtd_advogados,
            'horarios' => isset($request->horarios) ? implode(',', $request->horarios) : null,
            'dataInicial' => $request->dataInicial,
            'dataFinal' => $request->dataFinal
        ]);
        event(new CrudEvent('plantão juridico', 'editou', $id));
    }

    public function plantaoJuridicoAtivo()
    {
        return PlantaoJuridico::where('qtd_advogados', '>', 0)->whereDate('dataFinal', '>=', date('Y-m-d'))->count() > 0;
    }

    public function getRegionaisAtivas()
    {
        $plantoes = PlantaoJuridico::select('idregional')
            ->where('qtd_advogados', '>', 0)
            ->whereDate('dataFinal', '>=', date('Y-m-d'))
            ->get();
        $resultado = array();
        foreach($plantoes as $plantao)
            array_push($resultado, $plantao->idregional);

        return $resultado;
    }

    public function bloqueio()
    {
        return $this->bloqueio;
    }
}