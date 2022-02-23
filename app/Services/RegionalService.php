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
            'regional' => $validated->regional,
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

    /**
     * 
     * Métodos abaixo temporários até refatorar suas respectivas classes
     * Apenas copia e cola do repositório
     * 
    */

    /**
     * Método retorna regionais para atendimento, incluindo unidade da Alameda Santos. Ordenando por regionais e 
     * renomeando "São Paulo" para facilitar vizualização do Representante Comercial.
     */
    public function getRegionaisAgendamento()
    {
        $regionaisAtendimento = Regional::select('idregional', 'regional', 'prefixo')
            ->orderByRaw('case prefixo WHEN "SEDE" THEN 0 ELSE 1 END, idregional ASC')
            ->get();

        $regionaisAtendimento[0]->regional = 'São Paulo - Avenida Brigadeiro Luís Antônio';

        return $regionaisAtendimento;
    }

    public function getAgeporhorarioById($id)
    {
        return Regional::findOrFail($id)->ageporhorario;
    }

    public function getHorariosAgendamento($id, $dia)
    {
        return Regional::find($id)->horariosDisponiveis($dia);
    }

    public function getById($id)
    {
        return Regional::findOrFail($id);
    }

    public function getToList()
    {
        return Regional::select('idregional', 'regional')->get();
    }

    public function getByName($regional)
    {
        return Regional::where('regional','LIKE','%'.$regional.'%')
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