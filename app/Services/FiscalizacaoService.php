<?php

namespace App\Services;

use App\Contracts\FiscalizacaoServiceInterface;
use App\PeriodoFiscalizacao;
use App\Events\CrudEvent;
use App\Contracts\MediadorServiceInterface;

class FiscalizacaoService implements FiscalizacaoServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'ano de fiscalização',
            'singulariza' => 'o ano de fiscalização',
            'plural' => 'anos de fiscalização',
            'pluraliza' => 'anos de fiscalização',
            'titulo_criar' => 'Cria ano de fiscalização',
            'busca' => 'fiscalizacao',
            'slug' => 'fiscalizacao'
        ];
    }

    private function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'ID',
            'Ano',
            'Status',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userPodeEditar = auth()->user()->can('updateOther', auth()->user());
        foreach($resultados as $resultado) 
        {
            $acoes = '';
            if($userPodeEditar)
            {
                $acoes .= "<form method='POST' action='" . route('fiscalizacao.updatestatus', $resultado->id) . "' class='d-inline'>";
                $acoes .= "<input type='hidden' name='_token' value='" . csrf_token() . "'/>";
                $acoes .= '<input type="hidden" name="_method" value="PUT" id="method" />';
                if($resultado->status)
                    $acoes .= "<button type='submit' class='btn btn-sm btn-danger ml-1'>Reverter Publicação</button></form>";
                else
                    $acoes .= "<button type='submit' class='btn btn-sm btn-primary'>Publicar</button></form>";
                
                $acoes .= " <a href='" . route('fiscalizacao.editperiodo', $resultado->id) . "' class='btn btn-sm btn-default'>Editar</a>";
            }
            $conteudo = [
                $resultado->id,
                $resultado->periodo,
                $resultado->status ? PeriodoFiscalizacao::STATUS_PUBLICADO : PeriodoFiscalizacao::STATUS_NAO_PUBLICADO,
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
        $resultados = PeriodoFiscalizacao::orderBy('periodo', 'DESC')->paginate(25);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($id = null)
    {
        $this->variaveis['form'] = isset($id) ? 'periodofiscalizacaoedit' : 'periodofiscalizacaocreate';
        $resultado = isset($id) ? PeriodoFiscalizacao::with(['dadoFiscalizacao', 'dadoFiscalizacao.regional'])->findOrFail($id) : null;

        return [
            'resultado' => $resultado,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function save($validated, MediadorServiceInterface $service = null, $id = null)
    {
        if(!isset($id))
        {
            $periodo = PeriodoFiscalizacao::create(['periodo' => $validated['periodo']]);
            $regionais = $service->getService('Regional')->all()->slice(0,13);
            foreach($regionais as $regional)
                $periodo->dadoFiscalizacao()->create(['idregional' => $regional->idregional]);
            event(new CrudEvent('período fiscalização', 'criou', $periodo->id));

            return null;
        }
        
        $anoUpdate = PeriodoFiscalizacao::with(['dadoFiscalizacao', 'dadoFiscalizacao.regional'])->findOrFail($id);
        $dados = $anoUpdate->dadoFiscalizacao;
        foreach($validated['dados'] as $key => $array)
            $dados->find($array['id'])->update(array_combine($array['campo'], $array['valor']));
        event(new CrudEvent('dados do período da fiscalização', 'atualizou', $id));

        return null;
    }

    public function updateStatus($id)
    {
        $resultado = PeriodoFiscalizacao::findOrFail($id);
        $valor = $resultado->status ? 0 : 1;
        $texto = $resultado->status ? 'realizou' : 'reverteu';
        $resultado->update(['status' => $valor]);
        event(new CrudEvent($texto, ' atualizou a publicação do período da fiscalização com o status ', $id));
    }

    public function buscar($busca)
    {
        $resultados = PeriodoFiscalizacao::where('periodo', 'LIKE', '%'.$busca.'%')
            ->paginate(10);

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function mapaSite($id = null)
    {
        $todosPeriodos = PeriodoFiscalizacao::with(['dadoFiscalizacao', 'dadoFiscalizacao.regional'])
        ->where('status', true)
        ->orderBy('periodo', 'DESC')
        ->paginate(25);
        
        $periodoSelecionado = $todosPeriodos->total() > 0 ? $todosPeriodos->first() : null;
        
        if(isset($id))
            $periodoSelecionado = $todosPeriodos->find($id);

        $dataAtualizacao = isset($periodoSelecionado) ? onlyDate($periodoSelecionado->dadoFiscalizacao->sortByDesc("updated_at")->first()->updated_at) : null;

        return [
            'todosPeriodos' => $todosPeriodos->total() == 0 ? null : $todosPeriodos,
            'periodoSelecionado' => $periodoSelecionado,
            'dataAtualizacao' => $dataAtualizacao
        ];
    }
}