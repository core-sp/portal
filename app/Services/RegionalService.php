<?php

namespace App\Services;

use App\Contracts\RegionalServiceInterface;
use App\Regional;
use App\Events\CrudEvent;

class RegionalService implements RegionalServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'regional',
            'singulariza' => 'a regional',
            'plural' => 'regionais',
            'pluraliza' => 'regionais'
        ];
    }

    private function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Regional',
            'Telefone/Fax',
            'Email',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userPodeEditar = auth()->user()->can('updateOther', auth()->user());
        foreach($resultados as $resultado) 
        {
            $acoes = '<a href="'.route('regionais.show', $resultado->idregional).'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if($userPodeEditar)
                $acoes .= '<a href="'.route('regionais.edit', $resultado->idregional).'" class="btn btn-sm btn-primary">Editar</a> ';
            $conteudo = [
                $resultado->idregional,
                $resultado->prefixo.' - '.$resultado->regional,
                $resultado->telefone.'<br /><span style="white-space:nowrap;">'.$resultado->fax.'</span>',
                $resultado->email,
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

    public function index()
    {
        $resultados = Regional::orderBy('regional')->get();

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($id)
    {
        return [
            'resultado' => Regional::findOrFail($id), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function save($validated, $id)
    {
        Regional::findOrFail($id)->update([
            'endereco' => $validated->endereco,
            'bairro' => $validated->bairro,
            'numero' => $validated->numero,
            'complemento' => $validated->complemento,
            'cep' => $validated->cep,
            'telefone' => $validated->telefone,
            'fax' => $validated->fax,
            'email' => $validated->email,
            'funcionamento' => $validated->funcionamento,
            'ageporhorario' => $validated->ageporhorario,
            'horariosage' => implode(',', $validated->horariosage),
            'responsavel' => $validated->responsavel,
            'descricao' => $validated->descricao
        ]);
        event(new CrudEvent('regional', 'editou', $id));
    }

    public function viewSite($id)
    {
        // $regional = Regional::with('noticias')
        //     ->where('idregional', $id)
        //     ->limit(3)
        //     ->get();
        //     dd($regional);

        // return [
        //     'resultado' => Regional::findOrFail($id),
        //     'noticias' => $regional->noticias->sortDesc('created_at')
        // ];
    }

    public function buscar($busca)
    {
        $resultados = Regional::where('regional','LIKE','%'.$busca.'%')
            ->orWhere('email','LIKE','%'.$busca.'%')
            ->orWhere('responsavel','LIKE','%'.$busca.'%')
            ->paginate(10);

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function all()
    {
        return Regional::get();
    }
}