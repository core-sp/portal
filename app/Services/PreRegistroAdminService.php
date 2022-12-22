<?php

namespace App\Services;

use App\Contracts\PreRegistroAdminServiceInterface;
use App\PreRegistro;
use App\Anexo;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreRegistroMail;
use App\Events\CrudEvent;

class PreRegistroAdminService implements PreRegistroAdminServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'pré-registro',
            'singulariza' => 'o pré-registro',
            'pluraliza' => 'pré-registros',
            'plural' => 'pre-registros',
            'busca' => 'pre-registros',
            'slug' => 'pre-registros',
            'mostra' => 'pre-registro'
        ];
    }

    private function tabelaCompleta($resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'ID',
            'CPF / CNPJ',
            'Nome',
            'Regional',
            'Atualizado em:',
            'Status',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        foreach($resultados as $resultado) 
        {
            $texto = $resultado->atendentePodeEditar() ? 'Editar' : 'Visualizar';
            $cor = $resultado->atendentePodeEditar() ? 'primary' : 'info';
            $acoes = '<a href="'.route('preregistro.view', $resultado->id).'" class="btn btn-sm btn-' . $cor . '">'. $texto .'</a> ';
            $textoUser = '<span class="rounded p-1 bg' . $resultado->getLabelStatus() . ' font-weight-bolder font-italic">' . $resultado->status . '</span>';
            $conteudo = [
                'corDaLinha' => '<tr class="table' . $resultado->getLabelStatus() . '">',
                $resultado->id,
                formataCpfCnpj($resultado->userExterno->cpf_cnpj),
                $resultado->userExterno->nome,
                isset($resultado->idregional) ? $resultado->regional->regional : 'Sem regional no momento',
                formataData($resultado->updated_at),
                isset($resultado->idusuario) ? $textoUser . '<small class="d-block">Atualizado por: <strong>'.$resultado->user->nome.'</strong></small>' : $textoUser,
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];

        $legenda = PreRegistro::getLegendaStatus();
        $tabela = $legenda . montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    private function validacaoFiltroAtivo($request, $user)
    {
        if($user->idregional == 14)
            $user->idregional = 1;
            
        return [
            'regional' => $request->filled('regional') ? $request->regional : $user->idregional,
            'status' => $request->filled('status') && in_array($request->status, PreRegistro::getStatus()) ? $request->status : 'Qualquer',
            'atendente' => $request->filled('atendente') ? $request->atendente : 'Todos',
        ];
    }

    private function filtro($request, MediadorServiceInterface $service, $user)
    {
        $filtro = '';
        $temFiltro = null;
        $this->variaveis['continuacao_titulo'] = 'em <strong>'.$user->regional->regional.'</strong>';

        if(\Route::is('preregistro.filtro'))
        {
            $temFiltro = true;
            $this->variaveis['continuacao_titulo'] = '<i>(filtro ativo)</i>';
        }

        $regionais = $service->getService('Regional')->all()->whereNotIn('idregional', [14])->sortBy('regional');
        $options = !isset($request->regional) ? 
        getFiltroOptions('Todas', 'Todas', true) : getFiltroOptions('Todas', 'Todas');

        foreach($regionais as $regional)
            $options .= isset($request->regional) && ($request->regional == $regional->idregional) ? 
            getFiltroOptions($regional->idregional, $regional->regional, true) : 
            getFiltroOptions($regional->idregional, $regional->regional);

        $filtro .= getFiltroCamposSelect('Seccional', 'regional', $options);

        $options = isset($request->status) && ($request->status == 'Qualquer') ? 
        getFiltroOptions('Qualquer', 'Qualquer', true) : getFiltroOptions('Qualquer', 'Qualquer');

        foreach(PreRegistro::getStatus() as $s)
            $options .= isset($request->status) && ($request->status == $s) ? 
            getFiltroOptions($s, $s, true) : getFiltroOptions($s, $s);

        $filtro .= getFiltroCamposSelect('Status', 'status', $options);

        // Enquanto não possui o UserService
        $atendentes = \App\User::select('idusuario', 'nome', 'idperfil')
            ->whereIn('idperfil', [8, 10, 11, 12, 13, 18, 21])
            ->orderBy('nome')
            ->get();
        $options = !isset($request->atendente) ? getFiltroOptions('Todos', 'Todos', true) : getFiltroOptions('Todos', 'Todos');
        foreach($atendentes as $atendente)
            $options .= isset($request->atendente) && ($request->atendente == $atendente->idusuario) ? 
            getFiltroOptions($atendente->idusuario, $atendente->nome, true) : 
            getFiltroOptions($atendente->idusuario, $atendente->nome);
        $filtro .= getFiltroCamposSelect('Atendentes', 'atendente', $options);

        $filtro = getFiltro(route('preregistro.filtro'), $filtro);
        $this->variaveis['filtro'] = $filtro;

        return $temFiltro;
    }

    private function getResultadosFiltro($dados)
    {
        if(isset($dados))
        {
            $regional = $dados['regional'];
            $status = $dados['status'];
            $atendente = $dados['atendente'];

            return PreRegistro::with(['userExterno' => function ($query) {
                $query->select('id', 'cpf_cnpj', 'nome');
            }, 'regional' => function ($query2) {
                $query2->select('idregional', 'regional');
            }, 'user' => function ($query3) {
                $query3->select('idusuario', 'nome');
            }])
            ->select('id', 'updated_at', 'status', 'user_externo_id', 'idregional', 'idusuario')
            ->when($regional != 'Todas', function ($query) use ($regional) {
                $query->where('idregional', $regional);
            })
            ->when($status != 'Qualquer', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($atendente != 'Todos', function ($query) use ($atendente) {
                $query->where('idusuario', $atendente);
            })->orderByRaw(
                'CASE 
                    WHEN status = "' . PreRegistro::STATUS_ANALISE_INICIAL . '" THEN 1
                    WHEN status = "' . PreRegistro::STATUS_ANALISE_CORRECAO . '" THEN 2
                    WHEN status = "' . PreRegistro::STATUS_CORRECAO . '" THEN 3
                    WHEN status = "' . PreRegistro::STATUS_CRIADO . '" THEN 4
                    WHEN status = "' . PreRegistro::STATUS_APROVADO . '" THEN 5
                    ELSE 6
                END'
            )
            ->orderByDesc('updated_at')
            ->paginate(25);
        }
    }

    public function getTiposAnexos($idPreRegistro)
    {
        $preRegistro = PreRegistro::findOrFail($idPreRegistro);

        // Atendente não pode editar um pré-registro com status diferente de analise inicial e analise da correção
        if(!$preRegistro->atendentePodeEditar() || ($preRegistro->anexos->count() == 0))
            return null;
            
        return $preRegistro->anexos->first()->getOpcoesPreRegistro();
    }

    public function listar($request, MediadorServiceInterface $service, $user)
    {
        $dados = $this->validacaoFiltroAtivo($request, $user);
        $resultados = $this->getResultadosFiltro($dados, $user);
        $this->variaveis['mostraFiltros'] = true;
    
        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados), 
            'temFiltro' => $this->filtro($request, $service, $user),
            'variaveis' => (object) $this->variaveis,
        ];
    }

    public function view($id)
    {
        $link = session()->has('url_pre_registro') ? session('url_pre_registro') : route('preregistro.index');
        $variaveis = $this->variaveis;
        $variaveis['btn_lista'] = '<a href="'.$link.'" class="btn btn-primary mr-1">Lista dos Pré-registros</a>';
        $resultado = PreRegistro::findOrFail($id);

        return [
            'resultado' => $resultado, 
            'variaveis' => (object) $variaveis,
            'abas' => PreRegistro::getMenu(),
            'codigos' => PreRegistro::getCodigosCampos($resultado->getAbasCampos()),
        ];
    }

    public function buscar($busca)
    {
        $numero = apenasNumeros($busca);
        if(strlen($numero) == 0)
            $numero = null;

        $resultados = PreRegistro::with(['userExterno' => function ($query) {
            $query->select('id', 'cpf_cnpj', 'nome');
        }, 'regional' => function ($query2) {
            $query2->select('idregional', 'regional');
        }, 'user' => function ($query3) {
            $query3->select('idusuario', 'nome');
        }])
        ->select('id', 'updated_at', 'status', 'user_externo_id', 'idregional', 'idusuario')
        ->whereHas('userExterno', function ($query) use ($numero, $busca){
            // Busca pelo cpf_cnpj se tiver numero, caso contrário busca pelo nome
            $query->when(isset($numero), function($query2) use ($numero){
                $query2->where('cpf_cnpj', 'LIKE','%'.$numero.'%');
            }, function ($query2) use ($busca) { 
                $query2->where('nome','LIKE','%'.$busca.'%');
            });
        })
        ->orWhere('id', $busca)
        ->paginate(10);

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($resultados), 
            'variaveis' => (object) $this->variaveis,
        ];
    }

    public function saveAjaxAdmin($request, $id, $user)
    {
        $preRegistro = PreRegistro::findOrFail($id);

        // Atendente não pode editar um pré-registro com status diferente de analise inicial e analise da correção
        if(!$preRegistro->atendentePodeEditar())
            throw new \Exception('Não autorizado a editar o pré-registro sendo elaborado, aguardando correção ou finalizado', 401);

        $campo = $request['campo'];
        $valor = $request['valor'];

        if($request['acao'] != 'editar')
        {
            $campo = $request['acao'] == 'justificar' ? 'justificativa' : 'confere_anexos';
            $valor = $request['acao'] == 'justificar' ? ['campo' => $request['campo'], 'valor' => $request['valor']] : $request['valor'];
        }
            
        $camposCanEdit = [
            'justificativa' => 'preRegistro',
            'confere_anexos' => 'preRegistro',
            'registro_secundario' => 'preRegistro',
            'registro' => 'pessoaJuridica.responsavelTecnico',
        ];

        $preRegistro->atualizarAjax($camposCanEdit[$campo], $campo, $valor, null);
        $preRegistro->update(['idusuario' => $user->idusuario]);
        event(new CrudEvent('pré-registro', 'fez a ação de "' . $request['acao'] . '" o campo "' . $request['campo'] . '", inserindo ou removendo valor', $preRegistro->id));

        return [
            'user' => $user->nome,
            'atualizacao' => $preRegistro->fresh()->updated_at->format('d\/m\/Y, \à\s H:i:s')
        ];
    }

    public function updateStatus($id, $user, $status)
    {
        $preRegistro = PreRegistro::findOrFail($id);

        if(in_array($preRegistro->status, [PreRegistro::STATUS_APROVADO, PreRegistro::STATUS_NEGADO]))
            throw new \Exception('Não permitido atualizar o status do pré-registro já finalizado (Aprovado ou Negado)', 401);
        
        $preRegistro->update(['idusuario' => $user->idusuario, 'status' => $status]);
        $preRegistro->setHistoricoStatus();
        $preRegistro->fresh();

        Mail::to($preRegistro->userExterno->email)->queue(new PreRegistroMail($preRegistro));
        
        if($preRegistro->status == PreRegistro::STATUS_NEGADO)
        {
            $preRegistro->excluirAnexos();
            event(new CrudEvent('pré-registro', 'atualizou status para ' . $status . ' e seus arquivos foram excluídos pelo sistema', $id));
        }
        else
            event(new CrudEvent('pré-registro', 'atualizou status para ' . $status, $id));

        return [
            'message' => '<i class="icon fa fa-check"></i>Pré-registro com a ID: ' . $id . ' foi atualizado para "' . $status . '" com sucesso', 
            'class' => 'alert-success'
        ];
    }
}