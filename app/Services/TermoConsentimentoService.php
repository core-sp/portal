<?php

namespace App\Services;

use App\TermoConsentimento;
use App\Contracts\TermoConsentimentoServiceInterface;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\File;

class TermoConsentimentoService implements TermoConsentimentoServiceInterface {

    private function create($ip, $object)
    {
        return TermoConsentimento::create([
            'ip' => $ip,
            'email' => class_basename($object) == 'stdClass' ? $object->email : null,
            'idrepresentante' => class_basename($object) == 'Representante' ? $object->id : null,
            'idnewsletter' => class_basename($object) == 'Newsletter' ? $object->idnewsletter : null,
            'idagendamento' => class_basename($object) == 'Agendamento' ? $object->idagendamento : null,
            'idbdo' => class_basename($object) == 'BdoOportunidade' ? $object->idoportunidade : null
        ]);
    }

    private function message($id)
    {
        return 'foi criado um novo registro no termo de consentimento, com a id: '.$id;
    }

    public function save($ip, $object)
    {
        if(class_basename($object) == 'stdClass')
        {
            $existe = TermoConsentimento::where('email', $object->email)->first();

            if(isset($existe))
                return [
                    'message' => 'E-mail já cadastrado para continuar recebendo nossos informativos.', 
                    'class' => 'alert-warning'
                ]; 

            $termo = $this->create($ip, $object);
            event(new ExternoEvent($this->message($termo->id)));

            return $termo;
        }
        
        $termo = $this->create($ip, $object);
        return $this->message($termo->id);
    }

    public function caminhoFile()
    {
        $pdf = 'arquivos/CORE-SP_Termo_de_consentimento.pdf';

        return File::exists($pdf) ? $pdf : null;
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