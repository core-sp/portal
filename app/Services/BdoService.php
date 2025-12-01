<?php

namespace App\Services;

use App\Contracts\BdoServiceInterface;
use App\Services\BdoAdminService;
use App\Repositories\GerentiRepositoryInterface;
use App\BdoRepresentante;
use Illuminate\Support\Str;

class BdoService implements BdoServiceInterface {

    public function __construct()
    {

    }

    public function admin()
    {
        return new BdoAdminService();
    }

    public function temp_municipios()
    {
        return [
            'tag' => '<script type="application/json" id="municipiosJSON">aqui</script>',
            'json' => \Cache::remember('municipios', 86400, function () {
                $file = 'municipios-sp.json';
    
                if(!\Storage::disk('local')->exists($file)){
                    $client = new \GuzzleHttp\Client();
                    $response =  $client->request('GET', "https://servicodados.ibge.gov.br/api/v1/localidades/estados/35/municipios?orderBy=nome");
                    $conteudo = json_decode($response->getBody()->getContents());
                    $linha = [];
        
                    foreach($conteudo as $m){
                        $temp = str_replace(['Á', 'Ó', 'Í', 'É'], ['A', 'O', 'I', 'E'], mb_substr($m->nome, 0, 1));
                        isset($linha[$temp]) ? array_push($linha[$temp], $m->nome) : $linha[$temp] = [$m->nome];
                    }
                    \Storage::disk('local')->put($file, json_encode($linha, JSON_UNESCAPED_UNICODE));
                }
        
                return \Storage::disk('local')->get($file);
            })
        ];
    }

    public function viewPerfilRC($rep, GerentiRepositoryInterface $gerentiRepository = null)
    {
        $perfil = $rep->bdoPerfis()->whereIn('status->status_final', [
            '', BdoRepresentante::STATUS_ADMIN_FINAL, BdoRepresentante::STATUS_ACAO_ACEITO, BdoRepresentante::STATUS_ACAO_RECUSADO
        ])->orderBy('id', 'DESC')->first();

        if(is_null($gerentiRepository))
            return $perfil;

        $endereco = $gerentiRepository->gerentiEnderecos($rep->ass_id);
        $end = '';
        foreach($endereco as $key => $campo)
            switch ($key) {
                case 'Logradouro':
                case 'UF':
                    $end .= $campo;
                    break;
                case 'Complemento':
                    $end .= empty($campo) ? '' : ', ' . $campo;
                    break;
                case 'Bairro':
                    $end .= ' - ' . $campo . '. ';
                    break;
                case 'CEP':
                    $end .= 'CEP: ' . $campo . '. ';
                    break;
                case 'Cidade':
                    $end .= $campo . ' - ';
                    break;
            }
        $contatos = $gerentiRepository->gerentiContatos($rep->ass_id);
        $segmento = $gerentiRepository->gerentiGetSegmentosByAssId($rep->ass_id);
        $segmento = !empty($segmento) ? $segmento[0]["SEGMENTO"] : $segmento;
        $emails = array();
        $telefones = array();
        foreach($contatos as $contato){
            switch ($contato['CXP_TIPO']) {
                case 3:
                    array_push($emails, $contato['CXP_VALOR']);
                    break;
                case 5:
                    break;
                default:
                    array_push($telefones, $contato['CXP_VALOR']);
                    break;
            }
        }

        return [
            'perfil' => isset($perfil) && (json_decode($perfil->status)->status_final == BdoRepresentante::STATUS_ACAO_ACEITO) ? $perfil : null,
            'rep' => $rep, 
            'emails' => $emails, 
            'telefones' => $telefones, 
            'segmento' => $segmento, 
            'endereco' => $end,
            'seccional' => $gerentiRepository->gerentiDadosGerais($rep->tipoPessoa(), $rep->ass_id)["Regional"]
        ];
    }

    public function cadastrarPerfil($rep, $dados, GerentiRepositoryInterface $gerentiRepository)
    {
        $dados['regioes->seccional'] = $dados['regioes_seccional'];
        if(isset($dados['regioes_municipios']))
            $dados['regioes->municipios'] = $dados['regioes_municipios'];
        $dados['status'] = '{}';

        unset($dados['checkbox-tdu']);
        unset($dados['_token']);
        unset($dados['regioes_seccional']);
        unset($dados['regioes_municipios']);

        $bdo_perfil = $rep->bdoPerfis()->create($dados);

        $segmento = $gerentiRepository->gerentiGetSegmentosByAssId($rep->ass_id);
        $dados['segmento_gerenti'] = !empty($segmento) ? mb_strtoupper($segmento[0]["SEGMENTO"]) : mb_strtoupper($segmento);
        $dados['seccional_gerenti'] = mb_strtoupper($gerentiRepository->gerentiDadosGerais($rep->tipoPessoa(), $rep->ass_id)["Regional"]);
        $dados['em_dia'] = Str::contains(trim($gerentiRepository->gerentiStatus($rep->ass_id)), 'Em dia');

        $criado = $bdo_perfil->setores($dados);

        return $criado ? $bdo_perfil->fresh() : collect();
    }

    public function editarPerfil($rep, $dados)
    {
        $dados['regioes->municipios'] = isset($dados['regioes_municipios']) ? $dados['regioes_municipios'] : [];

        unset($dados['_method']);
        unset($dados['_token']);
        unset($dados['descricao']);
        unset($dados['regioes_seccional']);
        unset($dados['regioes_municipios']);

        $bdo_perfil = $rep->bdoPerfis()->where('status->status_final', BdoRepresentante::STATUS_ACAO_ACEITO)->orderBy('id', 'DESC')->first();
        
        if(isset($bdo_perfil))
            $bdo_perfil->update($dados);

        return $bdo_perfil->fresh();
    }

    public function buscarPerfisPublicos($dados, $regionais)
    {
        $regional_existe = isset($dados['regional']) && ($dados['regional'] != 'todas');

        if($regional_existe)
            $dados['regional'] = mb_strtoupper($regionais->where('idregional', $dados['regional'])->first()->regional);

        return BdoRepresentante::where('status->status_final', BdoRepresentante::STATUS_ACAO_ACEITO)
        ->when(isset($dados['segmento']), function($q) use($dados){
            return $q->where('segmento', mb_strtoupper($dados['segmento']));
        })
        ->when($regional_existe, function($q) use($dados){
            return $q->where('regioes->seccional', $dados['regional']);
        })
        ->when(isset($dados['municipio']) && ($dados['municipio'] != 'Qualquer'), function($q) use($dados){
            return $q->whereJsonContains('regioes->municipios', [$dados['municipio']]);
        })
        ->when(isset($dados['palavra-chave']), function($q) use($dados){
            return $q->where('descricao', 'LIKE', '%' . $dados['palavra-chave'] . '%');
        })
        ->paginate(10);
    }
}