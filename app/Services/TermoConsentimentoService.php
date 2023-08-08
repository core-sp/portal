<?php

namespace App\Services;

use App\TermoConsentimento;
use App\Contracts\TermoConsentimentoServiceInterface;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Storage;

class TermoConsentimentoService implements TermoConsentimentoServiceInterface {

    private $path_termos_servicos;

    public function __construct()
    {
        $this->path_termos_servicos = 'termos';
    }

    public function save($ip, $email)
    {
        if(isset($email))
        {
            $existe = TermoConsentimento::where('email', $email)->first();

            if(isset($existe))
                return [
                    'message' => 'E-mail já cadastrado para continuar recebendo nossos informativos.', 
                    'class' => 'alert-warning'
                ]; 

            $termo = TermoConsentimento::create([
                'ip' => $ip, 
                'email' => $email
            ]);
            event(new ExternoEvent($termo->message()));

            return null;
        }
    }

    public function caminhoFile()
    {
        if(Storage::disk('public')->exists($this->path_termos_servicos.'/CORE-SP_Termo_de_consentimento.pdf'))
            return Storage::disk('public')->path($this->path_termos_servicos.'/CORE-SP_Termo_de_consentimento.pdf');
        return null;
    }

    public function download()
    {
        $lista1 = TermoConsentimento::select('email', 'created_at')->whereNotNull('email')->get();
    
        if($lista1->isNotEmpty())
        {
            $headers = [
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Content-type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename=emails-termo_consentimento-'.date('Ymd').'.csv',
                'Expires' => '0',
                'Pragma' => 'public',
            ];
            $array = array();
            
            foreach($lista1 as $temp) 
                $array[] = $temp->attributesToArray();
        
            array_unshift($array, array_keys($array[0]));

            $callback = function() use($array) {
                $fh = fopen('php://output','w');
                fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));

                foreach($array as $linha) 
                    fputcsv($fh,$linha,';');

                fclose($fh);
            };
    
            return response()->stream($callback, 200, $headers);
        }

        return null;
    }
}