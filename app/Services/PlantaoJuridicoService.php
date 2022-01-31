<?php

namespace App\Services;

use App\Contracts\PlantaoJuridicoServiceInterface;
use App\PlantaoJuridico;
use App\PlantaoJuridicoBloqueio;
use Carbon\Carbon;

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
        foreach($resultados as $resultado) {
            $msgAtivado = '<span class="text-success">Ativado</span><br><small>com '.$resultado->qtd_advogados.' advogado(s)</small>';
            $acoes = '<a href="' .route('plantao.juridico.editar.view', $resultado->id). '" class="btn btn-sm btn-primary">Editar</a> ';
            $conteudo = [
                $resultado->id,
                $resultado->regional->regional,
                $resultado->temPlantaoJuridico() ? $msgAtivado : '<span class="text-danger">Desativado</span>',
                isset($resultado->dataInicial) && isset($resultado->dataFinal) ? onlyDate($resultado->dataInicial).' - '.onlyDate($resultado->dataFinal) : '',
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
            'Horas Bloqueadas',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) {
            $acoes = '';
            if($resultado->podeEditar())
                $acoes .= '<a href="' .route('plantao.juridico.bloqueios.editar.view', $resultado->id). '" class="btn btn-sm btn-primary">Editar</a> ';
            $acoes .= '<form method="POST" action="'.route('plantao.juridico.bloqueios.excluir', $resultado->id).'" class="d-inline">';
            $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
            $acoes .= '<input type="hidden" name="_method" value="delete" />';
            $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Apagar" onclick="return confirm(\'Tem certeza que deseja excluir esse bloqueio?\')" />';
            $acoes .= '</form>';
            $conteudo = [
                $resultado->id,
                $resultado->plantaoJuridico->regional->regional,
                onlyDate($resultado->dataInicial).' - '.onlyDate($resultado->dataFinal),
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

    private function validacaoBloqueio($request, $id = null)
    {
        $plantao = $this->getById($request->plantaoBloqueio);
        $horarios = explode(',', $plantao->horarios);

        if(Carbon::parse($request->dataInicialBloqueio)->lt($plantao->dataInicial) && 
        Carbon::parse($request->dataFinalBloqueio)->gt($plantao->dataFinal))
            return $erro = [
                'message' => '<i class="icon fa fa-ban"></i>A(s) data(s) escolhida(s) fora da datas do plantão',
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

    public function listar()
    {
        $plantoes = PlantaoJuridico::with('regional')
        ->orderBy('qtd_advogados', 'DESC')
        ->get();

        return $dados = [
            'tabela' => $this->tabelaCompleta($plantoes),
            'resultados' => $plantoes,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function visualizar($id)
    {
        return $dados = [
            'resultado' => $this->getById($id),
            'variaveis' => (object) $this->variaveis
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
    }

    public function listarBloqueios()
    {
        $bloqueios = PlantaoJuridicoBloqueio::with('plantaoJuridico')->get();

        return $dados = [
            'tabela' => $this->tabelaCompletaBloqueios($bloqueios),
            'resultados' => $bloqueios,
            'variaveis' => (object) $this->variaveisBloqueios
        ];
    }

    public function visualizarBloqueio($id = null)
    {
        if(isset($id))
        {
            return $dados = [
                'resultado' => PlantaoJuridicoBloqueio::findOrFail($id),
                'variaveis' => (object) $this->variaveisBloqueios
            ];
        }

        return $dados = [
            'plantoes' => PlantaoJuridico::with('regional')
            ->whereDate('dataInicial', '>=', date('Y-m-d'))->get(),
            'variaveis' => (object) $this->variaveisBloqueios
        ];
    }

    public function getDatasHorasPlantaoAjax($id)
    {
        $plantao = $this->getById($id);
        
        return [
            'horarios' => explode(',', $plantao->horarios),
            'datas' => [$plantao->dataInicial, $plantao->dataFinal]
        ];
    }

    public function saveBloqueio($request, $id = null)
    {
        $valid = $this->validacaoBloqueio($request, $id);

        if(!isset($valid))
        {
            if(isset($id))
            {
                PlantaoJuridicoBloqueio::findOrFail($id)->update([
                    'dataInicial' => $request->dataInicialBloqueio,
                    'dataFinal' => $request->dataFinalBloqueio,
                    'horarios' => implode(',', $request->horariosBloqueio),
                    'idusuario' => auth()->user()->idusuario
                ]);

                return null;
            }      
    
            PlantaoJuridicoBloqueio::create([
                'idplantaojuridico' => $request->plantaoBloqueio,
                'dataInicial' => $request->dataInicialBloqueio,
                'dataFinal' => $request->dataFinalBloqueio,
                'horarios' => implode(',', $request->horariosBloqueio),
                'idusuario' => auth()->user()->idusuario
            ]);
        }

        return $valid;
    }
}