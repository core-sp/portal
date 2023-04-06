<?php

namespace App\Services;

use App\Contracts\AgendamentoBloqueioSubServiceInterface;
use App\Contracts\MediadorServiceInterface;
use App\AgendamentoBloqueio;
use App\Events\CrudEvent;

class AgendamentoBloqueioSubService implements AgendamentoBloqueioSubServiceInterface {

    private $variaveis;
    private $renameSede;

    public function __construct()
    {
        $this->variaveis = [
            'singular' => 'bloqueio',
            'singulariza' => 'o bloqueio',
            'plural' => 'bloqueios de agendamento',
            'pluraliza' => 'bloqueios',
            'form' => 'agendamentobloqueio',
            'cancelar' => 'agendamentos/bloqueios',
            'titulo_criar' => 'Cadastrar novo bloqueio',
            'btn_criar' => '<a href="'.route('agendamentobloqueios.criar').'" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Novo Bloqueio</a>',
            'busca' => 'agendamentos/bloqueios',
        ];

        $this->renameSede = 'São Paulo - Avenida Brigadeiro Luís Antônio';
    }

    private function tabelaCompleta($user, $resultados)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'Código',
            'Regional',
            'Duração',
            'Horas bloqueadas/qtd de agend. alterada',
            'Qtd. de agend. por horário',
            'Ações',
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userPodeEditar = $user->can('updateOther', $user);
        $userPodeExcluir = $user->can('delete', $user);
        foreach($resultados as $resultado) 
        {
            $acoes = '';
            $duracao = 'Início: '.onlyDate($resultado->diainicio).'<br />';
            $duracao .= 'Término: '.$resultado->getMsgDiaTermino();
            if($userPodeEditar) 
                $acoes .= '<a href="'.route('agendamentobloqueios.edit', $resultado->idagendamentobloqueio).'" class="btn btn-sm btn-primary">Editar</a> ';
            if($userPodeExcluir) {
                $acoes .= '<form method="POST" action="'.route('agendamentobloqueios.delete', $resultado->idagendamentobloqueio).'" class="d-inline-block">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Cancelar" onclick="return confirm(\'Tem certeza que deseja cancelar o bloqueio?\')" />';
                $acoes .= '</form>';
            }
            $conteudo = [
                $resultado->idagendamentobloqueio,
                $resultado->regional->regional,
                $duracao,
                $resultado->horarios,
                $resultado->qtd_atendentes,
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];

        // Monta e retorna tabela        
        $tabela = montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    public function listar($user)
    {
        $resultados = AgendamentoBloqueio::with('regional')
            ->orderBy('idagendamentobloqueio', 'DESC')
            ->where('diatermino', '>=', date('Y-m-d'))
            ->orWhereNull('diatermino')
            ->paginate(10);

        if($user->cannot('create', $user))
            unset($this->variaveis['btn_criar']);
        
        return [
            'tabela' => $this->tabelaCompleta($user, $resultados),
            'resultados' => $resultados,
            'variaveis' => (object) $this->variaveis,
        ];
    }

    public function view($id = null, MediadorServiceInterface $service = null)
    {
        if(isset($service) && !isset($id))
        {
            $regionais = $service->getService('Regional')->all()->whereNotIn('idregional', [14]);
            $regionais->find(1)->regional = $this->renameSede;
    
            return [
                'variaveis' => (object) $this->variaveis,
                'regionais' => $regionais->sortBy('regional'),
            ];
        }
        
        $bloqueio = AgendamentoBloqueio::findOrFail($id);

        if($bloqueio->idregional == 1)
            $bloqueio->regional->regional = $this->renameSede;

        return [
            'variaveis' => (object) $this->variaveis,
            'resultado' => $bloqueio,
        ];
    }

    public function save($user, $dados, MediadorServiceInterface $service, $id = null)
    {
        $dados['idusuario'] = $user->idusuario;
        $dados['horarios'] = $dados['idregional'] != 'Todas' ? implode(',', $dados['horarios']) : null;

        if(isset($id))
        {
            unset($dados['idregional']);
            $bloqueio = AgendamentoBloqueio::findOrFail($id);
            $bloqueio->update($dados);

            event(new CrudEvent('bloqueio de agendamento', 'editou', $id));
            return null;
        }

        if($dados['idregional'] == 'Todas')
        {
            $regionais = $service->getService('Regional')->all()->whereNotIn('idregional', [14]);
            foreach($regionais as $regional)
            {
                $dados['idregional'] = $regional->idregional;
                $dados['horarios'] = $regional->horariosage;
                $bloqueio = AgendamentoBloqueio::create($dados);
                event(new CrudEvent('bloqueio de agendamento', 'criou', $bloqueio->idagendamentobloqueio));
            }

            return null;
        }

        $bloqueio = AgendamentoBloqueio::create($dados);
        event(new CrudEvent('bloqueio de agendamento', 'criou', $bloqueio->idagendamentobloqueio));
        return null;
    }

    public function delete($id)
    {
        return AgendamentoBloqueio::findOrFail($id)->delete() ? event(new CrudEvent('bloqueio de agendamento', 'cancelou', $id)) : null;
    }

    public function buscar($user, $busca)
    {
        $resultados = AgendamentoBloqueio::with('regional')
            ->whereHas('regional', function($q) use($busca){
                $q->where('regional', 'LIKE', '%'.$busca.'%');
            })->paginate(10);

        $this->variaveis['slug'] = 'agendamentos/bloqueios';

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($user, $resultados), 
            'variaveis' => (object) $this->variaveis
        ];
    }
}