<?php

namespace App\Services;

use App\Contracts\GeralServiceInterface;
use App\HomeImagem;
use App\Newsletter;
use App\Simulador;
use App\Events\CrudEvent;
use App\Events\ExternoEvent;

class GeralService implements GeralServiceInterface {

    public function carrossel($array = null)
    {
        $resultado = HomeImagem::select('idimagem','funcao','ordem','url','url_mobile','link','target')
            ->orderBy('ordem','ASC')
            ->get();
        $variaveis = [
            'singular' => 'banner',
            'singulariza' => 'o banner',
            'form' => 'bannerprincipal'
        ];

        if(isset($array))
        {
            $chunk = HomeImagem::validacao($array);
            for($cont = 1; $cont <= HomeImagem::TOTAL; $cont++)
            {
                $indice = $cont - 1;
                $banner = $resultado->where('ordem', $cont)
                ->where('funcao','bannerprincipal')->first();
                $banner->update([
                    'url' => $chunk[$indice][0],
                    'url_mobile' => $chunk[$indice][1],
                    'link' => $chunk[$indice][2],
                    'target' => $chunk[$indice][3]
                ]);
                event(new CrudEvent('banner principal', 'editou', $banner->idimagem));
            }
        }
        
        return [
            'resultado' => $resultado,
            'variaveis' => (object) $variaveis,
        ];
    }

    public function consultaSituacao($dados_gerenti)
    {
        if(isset($dados_gerenti) && count($dados_gerenti) === 1)
        {
            $dados_gerenti = utf8_converter($dados_gerenti[0]);
            $situacao = $dados_gerenti['SITUACAO'];
            $badge = '';
    
            switch ($situacao) {
                case 'Ativo':
                    $badge = '<span class="badge badge-success">'.$situacao.'</span>';
                break;
                case 'Cancelado':
                    $badge = '<span class="badge badge-danger">'.$situacao.'</span>';
                break;
                default:
                    $badge = '<span class="badge badge-secondary">'.$situacao.'</span>';
                break;
            }
    
            if($situacao === 'Não encontrado')
                return array();
    
            return [
                'nome' => $dados_gerenti['NOME'],
                'registro' => substr_replace($dados_gerenti['REGISTRONUM'], '/', -4, 0),
                'badge_situacao' => $badge,
            ];
        }

        return null;
    }

    public function anuidadeVigente($dados_gerenti)
    {
        if(isset($dados_gerenti[0]['NOSSONUMERO']))
            return [
                'nossonumero' => $dados_gerenti[0]['NOSSONUMERO']
            ];
        return [
            'notFound' => true
        ];
    }

    public function newsletter($dados = null, $download = false)
    {
        if(!isset($dados))
        {
            if(!$download)
                return Newsletter::count();
            $lista = Newsletter::getLista();
            isset($lista['arquivo']) ? event(new CrudEvent('newsletter', 'realizou download', '---')) : null;
            return $lista;
        }

        $newsletter = Newsletter::create([
            'nome' => mb_convert_case(mb_strtolower($dados['nome']), MB_CASE_TITLE),
            'email' => $dados['email'],
            'celular' => apenasNumeros($dados['celular'])
        ]);
        $termo = $newsletter->termos()->create([
            'ip' => $dados['ip']
        ]);
    
        $string = "*".$newsletter->nome."* (".$newsletter->email.")";
        $string .= " *registrou-se* na newsletter e ".$termo->message();
        event(new ExternoEvent($string));

        return "Muito obrigado por inscrever-se em nossa newsletter!";
    }

    public function simulador($validated = null, $gerenti = null)
    {
        $simulador = new Simulador();
        $dados = [
            'cores' => $simulador::listaCores(),
            'tipoPessoa' => $simulador::tipoPessoa(),
        ];

        if(!isset($validated))
        {
            $simulador = null;
            return $dados;
        }

        $resultado = $simulador->getSimulacao($validated, $gerenti);
        $resultado['dados'] = $dados;
        $ei = isset($validated['empresaIndividual']) ? $validated['empresaIndividual'] : '';
        $resultado['texto'] = $simulador->getTexto($validated['tipoPessoa'], $ei);
        $simulador = null;

        return $resultado;
    }
}