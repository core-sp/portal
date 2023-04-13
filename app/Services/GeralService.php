<?php

namespace App\Services;

use App\Contracts\GeralServiceInterface;
use App\HomeImagem;
use App\Newsletter;
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

    public function newsletter($dados)
    {
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

    public function newsletterAdmin($download = true)
    {
        if(!$download)
            return Newsletter::count();
        
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=newsletter-'.date('Ymd').'.csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ];
        $lista = Newsletter::select('email','nome','celular','created_at')->get();
        $lista = $lista->toArray();
        array_unshift($lista, array_keys($lista[0]));
        $callback = function() use($lista) {
            $fh = fopen('php://output','w');
            fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));
            foreach($lista as $linha) {
                fputcsv($fh,$linha,';');
            }
            fclose($fh);
        };
        event(new CrudEvent('newsletter', 'realizou download', '---'));

        return [
            'arquivo' => $callback,
            'headers' => $headers,
        ];
    }
}