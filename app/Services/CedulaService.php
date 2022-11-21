<?php

namespace App\Services;

use App\SolicitaCedula;
use App\Contracts\CedulaServiceInterface;
use App\Events\CrudEvent;
use App\Events\ExternoEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SolicitaCedulaMail;
use PDF;
use App\Repositories\GerentiRepositoryInterface;
use App\Contracts\MediadorServiceInterface;

class CedulaService implements CedulaServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'solicitação de cédula',
            'singulariza' => 'a solicitação de cédula',
            'plural' => 'solicita-cedula',
            'pluraliza' => 'solicitações de cédulas',
            'mostra' => 'solicita-cedula',
            'slug' => 'solicita-cedula',
            'busca' => 'solicita-cedula'
        ];
    }

    private function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Representante',
            'CPF/CNPJ',
            'Registro CORE',
            'Regional',
            'Solicitado em:',
            'Atualizado em:',
            'Status',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) 
        {
            $acoes = '<a href="'. route('solicita-cedula.show', $resultado->id) . '" class="btn btn-sm btn-default">Ver</a> ';
            if($resultado->podeGerarPdf())
                $acoes .= '<a href="' . route('solicita-cedula.pdf', $resultado->id) . '" target="_blank" class="btn btn-sm btn-warning">PDF</a> ';
            $conteudo = [
                $resultado->id,
                $resultado->representante->nome,
                $resultado->representante->cpf_cnpj,
                $resultado->representante->registro_core,
                $resultado->regional->regional,
                formataData($resultado->created_at),
                formataData($resultado->updated_at),
                '<strong class="' .$resultado->showStatus(). '">' .$resultado->status. '</strong>',
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

    private function validacaoFiltroAtivo($request)
    {
        $datemin = $request->filled('datemin') ? Carbon::parse($request->datemin) : Carbon::today();
        $datemax = $request->filled('datemax') ? Carbon::parse($request->datemax) : Carbon::today();

        if($datemax->lt($datemin))
            return [
                'message' => '<i class="icon fa fa-ban"></i>Data final deve ser maior ou igual a data inicial',
                'class' => 'alert-danger'
            ];

        return [
            'status' => $request->filled('status') ? $request->status : 'Qualquer',
            'datemin' => $datemin->format('Y-m-d'),
            'datemax' => $datemax->format('Y-m-d'),
        ];
    }

    private function getResultadosFiltro($dados)
    {
        if(isset($dados) && !isset($dados['message']))
        {
            $status = $dados['status'];

            return SolicitaCedula::when($status != 'Qualquer', function ($query) use($status) {
                    $query->where('status', $status);
                })
                ->whereDate('created_at', '>=', $dados['datemin'])
                ->whereDate('created_at', '<=', $dados['datemax'])
                ->orderBy('id')
                ->limit(25)
                ->paginate(10);
        }
    }

    private function filtro($request)
    {
        $filtro = '';
        $temFiltro = null;

        if(\Route::is('solicita-cedula.filtro'))
        {
            $temFiltro = true;
            $this->variaveis['continuacao_titulo'] = '<i>(filtro ativo)</i>';
        }

        $options = isset($request->status) && ($request->status == 'Qualquer') ? 
        getFiltroOptions('Qualquer', 'Qualquer', true) : getFiltroOptions('Qualquer', 'Qualquer');

        foreach($this->getAllStatus() as $s)
            $options .= isset($request->status) && ($request->status == $s) ? 
            getFiltroOptions($s, $s, true) : getFiltroOptions($s, $s);

        $filtro .= getFiltroCamposSelect('Status', 'status', $options);
        $filtro .= getFiltroCamposDate($request->datemin, $request->datemax);
        $filtro = getFiltro(route('solicita-cedula.filtro'), $filtro);

        $this->variaveis['filtro'] = $filtro;

        return $temFiltro;
    }

    public function getAllStatus()
    {
        return SolicitaCedula::allStatus();
    }

    public function listar($request)
    {
        session(['url' => url()->full()]);
        $this->variaveis['mostraFiltros'] = true;

        if(count($request->only(['datemin', 'datemax'])) > 0)
        {
            $dados = $this->validacaoFiltroAtivo($request);
            $resultados = $this->getResultadosFiltro($dados);
            $this->variaveis['mostraFiltros'] = true;
        }else
            $resultados = SolicitaCedula::orderByRaw(
                'CASE WHEN 
                status = "' . SolicitaCedula::STATUS_EM_ANDAMENTO . '" 
                THEN 0
                END DESC'
            )
            ->orderByDesc('updated_at')
            ->paginate(10);
    
        return [
            'erro' => isset($dados['message']) ? $dados : null,
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados), 
            'temFiltro' => $this->filtro($request),
            'variaveis' => (object) $this->variaveis,
        ];
    }

    public function view($id)
    {
        return [
            'resultado' => SolicitaCedula::findOrFail($id),
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function updateStatus($id, $dados, $user)
    {
        $dados['idusuario'] = $user->idusuario;
        $txt = $dados['status'] == SolicitaCedula::STATUS_ACEITO ? 'atendente aceitou' : 'atendente recusou e justificou';

        $cedula = SolicitaCedula::findOrFail($id);
        $cedula->update($dados);

        event(new CrudEvent('solicitação de cédula', $txt, $id));
        Mail::to($cedula->representante->email)->queue(new SolicitaCedulaMail($cedula));
    }

    public function gerarPdf($id)
    {
        $resultado = SolicitaCedula::findOrFail($id);

        if($resultado->podeGerarPdf())
        {
            $pdf = PDF::loadView('admin.forms.cedulaPDF', compact('resultado'))->setWarnings(false);
            return ['stream' => $pdf->stream('cedula_codigo_'.$id.'.pdf')];
        }

        return array();
    }

    public function buscar($busca)
    {
        $possuiNumeros = strlen(apenasLetras($busca)) == 0;

        $resultados = SolicitaCedula::when($possuiNumeros, function ($query) use ($busca) {
                return $query->where('id', $busca)
                ->orWhereHas('representante', function ($query2) use ($busca) {
                    $query2->where('cpf_cnpj', 'LIKE','%'.apenasNumeros($busca).'%')
                        ->orWhere('registro_core','LIKE','%'.apenasNumeros($busca).'%');
                });
            }, function ($query) use($busca) {
                return $query->where('status','LIKE','%'.$busca.'%')
                ->orWhereHas('representante', function ($query2) use ($busca) {
                    $query2->where('nome','LIKE','%'.$busca.'%');
                })
                ->orWhereHas('regional', function ($query3) use ($busca) {
                    $query3->where('regional', 'LIKE','%'.$busca.'%');
                });
            })->limit(25)
            ->paginate(10);

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }

    // Migrar métodos abaixo para o futuro servico de representante???
    public function getByRepresentante($user, GerentiRepositoryInterface $gerenti = null)
    {
        $cedulas = $user->cedulas()
            ->orderBy('id','DESC')
            ->paginate(5);
        
        $dados['cedulas'] = $cedulas;
        $dados['cedulaEmAndamento'] = $cedulas->where('status', SolicitaCedula::STATUS_EM_ANDAMENTO)->count();

        if(isset($gerenti))
        {
            $dados['nome'] = $user->tipoPessoa() == $user::PESSOA_FISICA ? $user->nome : null;
            $dados['rg'] = $user->tipoPessoa() == $user::PESSOA_FISICA ? $gerenti->gerentiDadosGeraisPF($user->ass_id)['identidade'] : null;
            $dados['cpf'] = $user->tipoPessoa() == $user::PESSOA_FISICA ? $user->cpf_cnpj : null;
        }

        return $dados;
    }

    public function save($dados, $user, GerentiRepositoryInterface $gerenti, MediadorServiceInterface $service)
    {
        unset($dados['tipo_pessoa']);
        $regional = $gerenti->gerentiDadosGerais($user->tipoPessoa(), $user->ass_id)['Regional'];
        $idregional = $service->getService('Regional')->getByName($regional)->idregional;
        $dados['idregional'] = $idregional;

        $cedula = $user->cedulas()->create($dados);

        event(new ExternoEvent('Usuário ' . $user->id . ' ("'. $user->registro_core .'") solicitou cédula.'));
        Mail::to($user->email)->queue(new SolicitaCedulaMail($cedula->fresh()));

        return $dados;
    }
}