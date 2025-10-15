<?php

namespace App\Services;

use App\Contracts\RegionalServiceInterface;
use App\Regional;
use App\Events\CrudEvent;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        $desabilitada = '<span class="badge badge-danger">Desativada</span><br />';
        foreach($resultados as $resultado) 
        {
            $aviso = $resultado->idregional == 14 ? $desabilitada : '';
            $acoes = '<a href="'.route('regionais.show', $resultado->idregional).'" class="btn btn-sm btn-default" target="_blank">Ver</a> ';
            if($userPodeEditar)
                $acoes .= '<a href="'.route('regionais.edit', $resultado->idregional).'" class="btn btn-sm btn-primary">Editar</a> ';
            $conteudo = [
                $resultado->idregional,
                $aviso . $resultado->prefixo.' - '.$resultado->regional,
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
        $resultados = Regional::select('idregional', 'prefixo', 'regional', 'telefone', 'fax', 'email')
            ->orderBy('regional')
            ->get();

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
        $validated['horariosage'] = implode(',', $validated['horariosage']);

        Regional::findOrFail($id)->update($validated);

        event(new CrudEvent('regional', 'editou', $id));
    }

    public function viewSite($id)
    {
        if($id == 14)
            throw new ModelNotFoundException("No query results for model [App\Regional] " . $id);

        $regional = Regional::findOrFail($id);

        return [
            'resultado' => $regional,
            'noticias' => $regional->noticias->isNotEmpty() ? 
                $regional->noticias()->select('slug', 'img', 'created_at', 'titulo', 'idregional')->orderBy('created_at', 'DESC')->limit(3)->get() : 
                $regional->noticias
        ];
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

    public function getById($id)
    {
        return Regional::find($id);
    }

    /**
     * 
     * Métodos abaixo temporários até refatorar suas respectivas classes
     * Apenas copia e cola do repositório
     * 
    */

    public function getByName($regional)
    {
        return Regional::where('regional','LIKE','%'.$regional.'%')
            ->where('idregional', '!=', 14)
            ->get()
            ->first();
    }

    /**
     * Método retorna apenas regionais. Retorna apenas SEDE, ES01 ~ ES12 (exclui Alameda Santos).
     */
    public function getRegionais()
    {
        $regionaisFiscalizacao = Regional::select('idregional', 'regional', 'prefixo')->where('idregional', '<=', 13)->get();

        return $regionaisFiscalizacao;
    }
}