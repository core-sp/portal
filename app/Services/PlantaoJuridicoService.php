<?php

namespace App\Services;

use App\Contracts\PlantaoJuridicoServiceInterface;
use App\PlantaoJuridico;
use App\PlantaoJuridicoBloqueio;
use Carbon\Carbon;
use App\Events\CrudEvent;
// Temporário até refatorar o Agendamento no Service
use App\Repositories\AgendamentoRepository;

class PlantaoJuridicoService implements PlantaoJuridicoServiceInterface {

    private $variaveis;
    private $variaveisBloqueios;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'plantão jurídico',
            'singulariza' => 'o plantão jurídico',
            'plural' => 'plantões jurídicos',
            'pluraliza' => 'plantão jurídico',
            'form' => 'plantao_juridico',
        ];

        $this->variaveisBloqueios = [
            'singular' => 'bloqueio plantão jurídico',
            'singulariza' => 'o bloqueio do plantão jurídico',
            'plural' => 'bloqueios dos plantões jurídicos',
            'pluraliza' => 'bloqueios plantão jurídico',
            'form' => 'plantao_juridico_bloqueio',
            'btn_criar' => '<a href="'.route('plantao.juridico.bloqueios.criar.view').'" class="btn btn-primary mr-1">Novo Bloqueio</a>',
            'titulo_criar' => 'Criar bloqueio',
        ];
    }

    private function tabelaCompleta($resultados)
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
        $userPodeEditar = auth()->user()->can('updateOther', auth()->user());
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

    private function tabelaCompletaBloqueios($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Id',
            'Regional',
            'Período',
            'Período do Plantão',
            'Horas Bloqueadas',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userPodeEditar = auth()->user()->can('updateOther', auth()->user());
        $userPodeExcluir = auth()->user()->can('delete', auth()->user());
        foreach($resultados as $resultado) {
            $acoes = '';
            if($resultado->podeEditar() && $userPodeEditar)
                $acoes .= '<a href="' .route('plantao.juridico.bloqueios.editar.view', $resultado->id). '" class="btn btn-sm btn-primary">Editar</a> ';
            if($userPodeExcluir)
            {
                $acoes .= '<form method="POST" action="'.route('plantao.juridico.bloqueios.excluir', $resultado->id).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir esse bloqueio?\')" />';
                $acoes .= '</form>';
            }
            $conteudo = [
                $resultado->id,
                $resultado->plantaoJuridico->regional->regional,
                onlyDate($resultado->dataInicial).' - '.onlyDate($resultado->dataFinal),
                $resultado->podeEditar() ? 
                    onlyDate($resultado->plantaoJuridico->dataInicial).' - '.onlyDate($resultado->plantaoJuridico->dataFinal) : 
                   '<p class="text-danger"><strong>Expirado</strong></p>',
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

    private function getById($id)
    {
        return PlantaoJuridico::findOrFail($id);
    }

    private function validacaoBloqueio($request)
    {
        $plantao = $this->getById($request->plantaoBloqueio);
        $horarios = explode(',', $plantao->horarios);
        $inicial = Carbon::parse($plantao->dataInicial);
        $final = Carbon::parse($plantao->dataFinal);

        if(Carbon::parse($request->dataInicialBloqueio)->lt($inicial) ||
        Carbon::parse($request->dataInicialBloqueio)->gt($final) ||
        Carbon::parse($request->dataFinalBloqueio)->lt($inicial) ||
        Carbon::parse($request->dataFinalBloqueio)->gt($final))
            return $erro = [
                'message' => '<i class="icon fa fa-ban"></i>A(s) data(s) escolhida(s) fora das datas do plantão',
                'class' => 'alert-danger'
            ];

        foreach($request->horariosBloqueio as $hora)
            if(!in_array($hora, $horarios))
                return $erro = [
                    'message' => '<i class="icon fa fa-ban"></i>A(s) hora(s) escolhida(s) não inclusa(s) nas horas do plantão',
                    'class' => 'alert-danger'
                ];

        return null;
    }

    private function getHorariosComBloqueio($plantao, $dia)
    {
        $horarios = explode(',', $plantao->horarios);

        if($plantao->bloqueios->count() > 0)
        {
            foreach($plantao->bloqueios as $bloqueio)
            {
                $inicialBloqueio = Carbon::parse($bloqueio->dataInicial);
                $finalBloqueio = Carbon::parse($bloqueio->dataFinal);
                $dia = Carbon::parse($dia);

                if($inicialBloqueio->lte($dia) && $finalBloqueio->gte($dia))
                {
                    $horariosBloqueios = explode(',', $bloqueio->horarios);
                    foreach($horariosBloqueios as $horario)
                        unset($horarios[array_search($horario, $horarios)]);
                }
            }
        }

        return $horarios;
    }

    public function listar()
    {
        $plantoes = PlantaoJuridico::with('regional')
        ->orderBy('qtd_advogados', 'DESC')
        ->get();

        return [
            'tabela' => $this->tabelaCompleta($plantoes),
            'resultados' => $plantoes,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function visualizar($id, AgendamentoRepository $agendamento)
    {
        $plantao = $this->getById($id);

        return [
            'resultado' => $plantao,
            'variaveis' => (object) $this->variaveis,
            'agendamentos' => $plantao->expirou() ? null : $agendamento->getPlantaoJuridicoPorPeriodo($plantao->idregional, $plantao->dataInicial, $plantao->dataFinal) 
        ];
    }

    public function save($request, $id)
    {
        $this->getById($id)->update([
            'qtd_advogados' => $request->qtd_advogados,
            'horarios' => isset($request->horarios) ? implode(',', $request->horarios) : null,
            'dataInicial' => isset($request->dataInicial) ? $request->dataInicial : null,
            'dataFinal' => isset($request->dataFinal) ? $request->dataFinal : null
        ]);
        event(new CrudEvent('plantão juridico', 'editou', $id));
    }

    public function listarBloqueios()
    {
        $bloqueios = PlantaoJuridicoBloqueio::with('plantaoJuridico', 'user')->get();

        if(auth()->user()->cannot('create', auth()->user()))
            unset($this->variaveis['btn_criar']);

        return [
            'tabela' => $this->tabelaCompletaBloqueios($bloqueios),
            'resultados' => $bloqueios,
            'variaveis' => (object) $this->variaveisBloqueios
        ];
    }

    public function visualizarBloqueio($id = null)
    {
        if(isset($id))
        {
            $bloqueio = PlantaoJuridicoBloqueio::findOrFail($id);

            return $bloqueio->podeEditar() ? 
                ['resultado' => $bloqueio, 'variaveis' => (object) $this->variaveisBloqueios] : 
                ['message' => '<i class="icon fa fa-ban"></i>O bloqueio não pode mais ser editado devido o período do plantão ter expirado',
                'class' => 'alert-danger'];
        }

        return [
            'plantoes' => PlantaoJuridico::with('regional')
            ->whereDate('dataFinal', '>=', date('Y-m-d'))->get(),
            'variaveis' => (object) $this->variaveisBloqueios
        ];
    }

    public function getDatasHorasPlantaoAjax($id)
    {
        $plantao = $this->getById($id);
        if(isset($plantao))
        {
            $inicial = Carbon::parse($plantao->dataInicial);
            $hoje = Carbon::today();
            
            return [
                'horarios' => explode(',', $plantao->horarios),
                'datas' => [$inicial->lte($hoje) ? Carbon::tomorrow()->format('Y-m-d') : $plantao->dataInicial, $plantao->dataFinal]
            ];
        }
    }

    public function saveBloqueio($request, $id = null)
    {
        $valid = $this->validacaoBloqueio($request);

        if(!isset($valid))
        {
            if(isset($id))
            {
                $bloqueio = PlantaoJuridicoBloqueio::findOrFail($id);

                if(!$bloqueio->podeEditar()) 
                    return [
                        'message' => '<i class="icon fa fa-ban"></i>O bloqueio não pode mais ser editado devido o período do plantão ter expirado',
                        'class' => 'alert-danger'
                    ];

                $bloqueio->update([
                    'dataInicial' => $request->dataInicialBloqueio,
                    'dataFinal' => $request->dataFinalBloqueio,
                    'horarios' => implode(',', $request->horariosBloqueio),
                    'idusuario' => auth()->user()->idusuario
                ]);
                event(new CrudEvent('plantão juridico bloqueio', 'editou', $id));
            }else  
            {
                $id = PlantaoJuridicoBloqueio::create([
                    'idplantaojuridico' => $request->plantaoBloqueio,
                    'dataInicial' => $request->dataInicialBloqueio,
                    'dataFinal' => $request->dataFinalBloqueio,
                    'horarios' => implode(',', $request->horariosBloqueio),
                    'idusuario' => auth()->user()->idusuario
                ]);
                event(new CrudEvent('plantão juridico bloqueio', 'criou', $id));
            }    
        }

        return $valid;
    }

    public function destroy($id)
    {
        PlantaoJuridicoBloqueio::findOrFail($id)->delete() ? event(new CrudEvent('plantão juridico bloqueio', 'excluiu', $id)) : null;
    }

    public function plantaoJuridicoAtivo()
    {
        return PlantaoJuridico::where('qtd_advogados', '>', 0)->count() > 0 ? true : false;
    }

    public function getRegionaisDesativadas()
    {
        $plantoes = PlantaoJuridico::select('idregional')->where('qtd_advogados', 0)->get();
        $resultado = array();
        foreach($plantoes as $plantao)
            array_push($resultado, $plantao->idregional);

        return $resultado;
    }

    public function getPlantaoAtivoComBloqueioPorRegional($idregional)
    {
        return PlantaoJuridico::with('bloqueios')
        ->where('idregional', $idregional)
        ->where('qtd_advogados', '>', 0)
        ->first();
    }

    public function removeHorariosSeLotado($agendados, $plantao, $dia)
    {
        $horarios = $this->getHorariosComBloqueio($plantao, $dia);

        foreach($agendados as $agendado)
            if(isset($agendado->total) && ($plantao->qtd_advogados == $agendado->total))
                unset($horarios[array_search($agendado->hora, $horarios)]);
        
        return $horarios;
    }

    public function getDiasSeLotado($agendados, $plantao)
    {
        $dtI = Carbon::parse($plantao->dataInicial);
        $inicial = $dtI->lte(Carbon::today()) ? Carbon::tomorrow() : $dtI;
        $final = Carbon::parse($plantao->dataFinal);
        $diasLotados = array();

        for($dia = $inicial; $dia->lte($final); $dia->addDay())
        {
            $agendado = isset($agendados[$dia->format('Y-m-d')]) ? $agendados[$dia->format('Y-m-d')] : null;
            if(isset($agendado))
            {
                $horarios = $this->removeHorariosSeLotado($agendado, $plantao, $dia);
                if(empty($horarios))
                    array_push($diasLotados, [$dia->month, $dia->day, 'lotado']);
            }
        }

        return $diasLotados;
    }

    public function validacaoAgendarPlantao($plantao, $diaEscolhido, $agendados = null, $horaEscolhida = null)
    {
        $inicial = Carbon::parse($plantao->dataInicial);
        $final = Carbon::parse($plantao->dataFinal);
        $dia = Carbon::parse($diaEscolhido);
        $hoje = Carbon::today();

        if($dia->lt($inicial) || $dia->gt($final) || $dia->lte($hoje))
            return false;

        if(isset($agendados) && !isset($horaEscolhida))
        {
            $diasLotados = $this->getDiasSeLotado($agendados, $plantao);
            if(in_array([$dia->month, $dia->day, 'lotado'], $diasLotados))
                return false;
        }

        if(isset($agendados) && isset($horaEscolhida))
        {
            $horarios = $this->removeHorariosSeLotado($agendados, $plantao, $dia);
            if(!in_array($horaEscolhida, $horarios))
                return false;
        }

        return true;
    }
}