<?php

namespace App\Services;

use App\Contracts\PlantaoJuridicoBloqueioSubServiceInterface;
use App\PlantaoJuridico;
use App\PlantaoJuridicoBloqueio;
use Carbon\Carbon;
use App\Events\CrudEvent;

class PlantaoJuridicoBloqueioSubService implements PlantaoJuridicoBloqueioSubServiceInterface {

    private $variaveis;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'bloqueio plantão jurídico',
            'singulariza' => 'o bloqueio do plantão jurídico',
            'plural' => 'bloqueios dos plantões jurídicos',
            'pluraliza' => 'bloqueios plantão jurídico',
            'form' => 'plantao_juridico_bloqueio',
            'btn_criar' => '<a href="'.route('plantao.juridico.bloqueios.criar.view').'" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Novo Bloqueio</a>',
            'titulo_criar' => 'Criar bloqueio',
        ];
    }

    private function tabelaCompleta($user, $resultados)
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
        $userPodeEditar = $user->can('updateOther', $user);
        $userPodeExcluir = $user->can('delete', $user);
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

    public function listar($user)
    {
        $bloqueios = PlantaoJuridicoBloqueio::with('plantaoJuridico')->paginate(15);

        if($user->cannot('create', $user))
            unset($this->variaveis['btn_criar']);

        return [
            'tabela' => $this->tabelaCompleta($user, $bloqueios),
            'resultados' => $bloqueios,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function view($id = null)
    {
        if(isset($id))
        {
            $bloqueio = PlantaoJuridicoBloqueio::findOrFail($id);

            return $bloqueio->podeEditar() ? 
                ['resultado' => $bloqueio, 'variaveis' => (object) $this->variaveis] : 
                ['message' => '<i class="icon fa fa-ban"></i>O bloqueio não pode mais ser editado devido o período do plantão ter expirado',
                'class' => 'alert-danger'];
        }

        return [
            'plantoes' => PlantaoJuridico::with('regional')
            ->whereDate('dataFinal', '>', date('Y-m-d'))->get(),
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function save($user, $request, $id = null)
    {
        $dados = [
            'dataInicial' => $request->dataInicialBloqueio,
            'dataFinal' => $request->dataFinalBloqueio,
            'horarios' => implode(',', $request->horariosBloqueio),
            'idusuario' => $user->idusuario
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
}