<?php

namespace App\Services;

use App\Contracts\BdoServiceInterface;
use App\Services\BdoAdminService;
use App\BdoRepresentante;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

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

    public function viewPerfilRC($rep, $home = true)
    {
        // Bdo - Home
        $perfil = $rep->bdoPerfis()->whereIn('status->status_final', [
            '', BdoRepresentante::STATUS_ADMIN_FINAL, BdoRepresentante::STATUS_ACAO_ACEITO, BdoRepresentante::STATUS_ACAO_RECUSADO
        ])->orderBy('id', 'DESC')->first();

        if($home)
            return $perfil;

        return [
            'perfil' => isset($perfil) && (json_decode($perfil->status)->status_final == BdoRepresentante::STATUS_ACAO_ACEITO) ? $perfil : null,
            'rep' => $rep, 
        ];
    }

    public function cadastrarPerfil($rep, $dados)
    {
        $dados['status'] = '{}';

        $bdo_perfil = $rep->bdoPerfis()->create(Arr::where($dados, function ($value, $key) {
            return !Str::contains($key, '_gerenti');
        }));

        // **** Falta incluir registro de termo na tabela de termos_consentimentos

        $criado = $bdo_perfil->setores($dados);

        return $criado ? $bdo_perfil->fresh() : collect();
    }

    public function editarPerfil($rep, $dados)
    {
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