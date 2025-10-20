<?php

namespace App\Services;

use App\Contracts\PlantaoJuridicoServiceInterface;
use App\PlantaoJuridico;
use App\PlantaoJuridicoBloqueio;
use Carbon\Carbon;
use App\Events\CrudEvent;

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
            'btn_criar' => '<a href="'.route('plantao.juridico.bloqueios.criar.view').'" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Novo Bloqueio</a>',
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
            'Período do Bloqueio',
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
                $acoes .= '<form method="POST" action="'.route('plantao.juridico.bloqueios.excluir', $resultado->id).'" class="d-inline acaoTabelaAdmin">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="hidden" class="cor-danger txtTabelaAdmin" value="Tem certeza que deseja excluir o bloqueio com ID <i>' . $resultado->id . '</i>?" />';
                $acoes .= '<button type="button" class="btn btn-sm btn-danger" value="' . $resultado->id . '">Apagar</button>';
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

    public function listar()
    {
        $plantoes = PlantaoJuridico::with('regional')
        ->where('id', '!=', 14)
        ->orderBy('qtd_advogados', 'DESC')
        ->get();

        return [
            'tabela' => $this->tabelaCompleta($plantoes),
            'resultados' => $plantoes,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function listarBloqueios()
    {
        $bloqueios = PlantaoJuridicoBloqueio::with('plantaoJuridico')->paginate(15);

        if(auth()->user()->cannot('create', auth()->user()))
            unset($this->variaveis['btn_criar']);

        return [
            'tabela' => $this->tabelaCompletaBloqueios($bloqueios),
            'resultados' => $bloqueios,
            'variaveis' => (object) $this->variaveisBloqueios
        ];
    }

    public function visualizar($id)
    {
        if($id == 14)
            throw new \Exception('Plantão Jurídico com id ' . $id . ' não está disponível para uso.', 404);

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
            ->whereDate('dataFinal', '>', date('Y-m-d'))->get(),
            'variaveis' => (object) $this->variaveisBloqueios
        ];
    }

    public function save($request, $id)
    {
        if($id == 14)
            throw new \Exception('Plantão Jurídico com id ' . $id . ' não está disponível para uso.', 404);
        
        PlantaoJuridico::findOrFail($id)->update([
            'qtd_advogados' => $request->qtd_advogados,
            'horarios' => isset($request->horarios) ? implode(',', $request->horarios) : null,
            'dataInicial' => $request->dataInicial,
            'dataFinal' => $request->dataFinal
        ]);
        event(new CrudEvent('plantão juridico', 'editou', $id));
    }

    public function saveBloqueio($request, $id = null)
    {
        $dados = [
            'dataInicial' => $request->dataInicialBloqueio,
            'dataFinal' => $request->dataFinalBloqueio,
            'horarios' => implode(',', $request->horariosBloqueio),
            'idusuario' => auth()->user()->idusuario
        ];

        if(isset($id))
        {
            $bloqueio = PlantaoJuridicoBloqueio::findOrFail($id);

            if(!$bloqueio->podeEditar()) 
                return [
                    'message' => '<i class="icon fa fa-ban"></i>O bloqueio não pode mais ser editado devido o período do plantão ter expirado',
                    'class' => 'alert-danger'
                ];

            $bloqueio->update($dados);
            event(new CrudEvent('plantão juridico bloqueio', 'editou', $id));
        }else  
        {
            $dados['idplantaojuridico'] = $request->plantaoBloqueio;
            $id = PlantaoJuridicoBloqueio::create($dados)->id;
            event(new CrudEvent('plantão juridico bloqueio', 'criou', $id));
        }    
    }

    public function getDatasHorasLinkPlantaoAjax($id)
    {
        $plantao = PlantaoJuridico::find($id);
        if(isset($plantao))
        {
            $inicial = Carbon::parse($plantao->dataInicial);
            $hoje = Carbon::today();
            
            return [
                'horarios' => explode(',', $plantao->horarios),
                'datas' => [$inicial->lte($hoje) ? Carbon::tomorrow()->format('Y-m-d') : $plantao->dataInicial, $plantao->dataFinal],
                'link-agendados' => $plantao->ativado() ? route('plantao.juridico.editar.view', $plantao->id) : null
            ];
        }
    }

    public function destroy($id)
    {
        return PlantaoJuridicoBloqueio::findOrFail($id)->delete() ? event(new CrudEvent('plantão juridico bloqueio', 'excluiu', $id)) : null;
    }

    public function plantaoJuridicoAtivo()
    {
        return PlantaoJuridico::where('qtd_advogados', '>', 0)->count() > 0;
    }

    public function getRegionaisAtivas()
    {
        $plantoes = PlantaoJuridico::select('idregional')->where('qtd_advogados', '>', 0)->get();
        $resultado = array();
        foreach($plantoes as $plantao)
            array_push($resultado, $plantao->idregional);

        return $resultado;
    }
}