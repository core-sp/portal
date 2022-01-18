<?php

namespace App\Services;

use App\Contracts\SuporteServiceInterface;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SuporteService implements SuporteServiceInterface {

    private $variaveis;

    public function __construct()
    {
        // listagem e buscar são os nomes das rotas
        $this->variaveis = [
            'mostra' => 'log_externo',
            'singular' => 'Log Externo',
            'singulariza' => 'o log externo',
        ];
    }

    private function getLog($data)
    {
        if($this->hasLog($data))
        {
            $log = Storage::disk('log_externo')->get($this->getPathLogFile($data));
            return $this->editarConteudoLog($log);
        }

        return null;
    }

    private function getLastModificationLog($data)
    {
        return $this->hasLog($data) ? Storage::disk('log_externo')->lastModified($this->getPathLogFile($data)) : null;
    }

    private function editarConteudoLog($log)
    {
        $array = explode(PHP_EOL, $log);
        foreach($array as $key => $value)
        {
            $tamanhoTextoRemover = strlen(substr_replace(\Str::before($value, ' - '), '', 0, 21));
            $array[$key] = substr_replace($value, '', 21, $tamanhoTextoRemover);
            $i = $key + 1;
            $array[$key] = substr_replace($array[$key], '#'.$i.' - ', 0, 0).PHP_EOL;
        }

        return $array;
    }

    private function getPathLogFile($data)
    {
        $data = Carbon::createFromFormat('Y-m-d', $data);
        $anoMes = $data->format('Y').'/'.$data->format('m').'/';
        $nomeArquivo = 'laravel-'.$data->format('Y').'-'.$data->format('m').'-'.$data->format('d').'.log';

        return $anoMes.$nomeArquivo;
    }

    private function hasLog($data)
    {
        $log = $this->getPathLogFile($data);
        return Storage::disk('log_externo')->exists($log);
    }

    public function logDoDia()
    {
        $log = $this->getLog(date('Y-m-d'));
        
        if(isset($log))
        {
            $callback = function() use($log) {
                $fh = fopen('php://output','w');
                foreach($log as $value)
                    fwrite($fh, $value);
                fclose($fh);
            };

            return $callback;
        }

        return null;
    }

    public function indexLog()
    {
        $infos = $this->getLastModificationLog(date('Y-m-d'));

        return $dados = [
            'info' => isset($infos) ? Carbon::parse($infos)->setTimezone('America/Sao_Paulo')->format('d/m/Y, \à\s H:i') : null,
            'variaveis' => (object) $this->variaveis
        ];
    }

    public function busca($request)
    {
        if(isset($request['data']))
            return ['resultado' => $this->hasLog($request['data']) ? $request['data'] : null];
        return null;
    }

    public function logPorData($data)
    {
        $log = $this->getLog($data);
        
        if(isset($log))
        {
            $callback = function() use($log) {
                $fh = fopen('php://output','w');
                foreach($log as $value)
                    fwrite($fh, $value);
                fclose($fh);
            };

            return $callback;
        }

        return null;
    }
}