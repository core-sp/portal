<?php

namespace App\Services;

use App\Contracts\SuporteServiceInterface;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SuporteService implements SuporteServiceInterface {

    private $variaveisLog;
    private $variaveisErros;

    public function __construct()
    {
        $this->variaveisLog = [
            'mostra' => 'log_externo',
            'singular' => 'Log Externo',
            'singulariza' => 'o log externo',
        ];

        $this->variaveisErros = [
            'mostra' => 'suporte_erros',
            'singular' => 'Tabela de Erros',
            'singulariza' => 'os erros',
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
            // Ocultar o IP
            $tamanhoTextoRemover = strlen(substr_replace(\Str::before($value, ' - '), '', 0, 21));
            $array[$key] = substr_replace($value, '', 21, $tamanhoTextoRemover);
            $i = $key + 1;
            // Inserir número da linha no texto
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

    private function getLogForBrowser($log)
    {
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

    private function hasLog($data)
    {
        $log = $this->getPathLogFile($data);
        return Storage::disk('log_externo')->exists($log);
    }

    private function getFileErros()
    {
        // Criar arquivo no servidor para tabelar os erros
        // \Storage::disk('local')->put('tabela-erros.txt');
    }

    public function indexLog()
    {
        $infos = $this->getLastModificationLog(date('Y-m-d'));

        return $dados = [
            'info' => isset($infos) ? Carbon::parse($infos)->setTimezone('America/Sao_Paulo')->format('d/m/Y, \à\s H:i') : null,
            'variaveis' => (object) $this->variaveisLog
        ];
    }

    public function logBusca($request)
    {        
        if(isset($request->data))
            $dados['resultado'] = $this->hasLog($request->data) ? $request->data : null;
        elseif(isset($request->texto))
        {
            $dias = [
                Carbon::today()->format('Y-m-d'),
                Carbon::today()->subDays(1)->format('Y-m-d'),
                Carbon::today()->subDays(2)->format('Y-m-d'),
            ];
            foreach($dias as $dia)
            {
                $log = $this->getLog($dia);
                $dados['resultado'][$dia] = isset($log) ? preg_grep('/'.preg_quote($request->texto, '/').'/i', $log) : null;
            }
        }

        return $dados;
    }

    public function logPorData($data)
    {
        $log = $this->getLog($data);
        return $this->getLogForBrowser($log);
    }

    public function indexErros()
    {
        return $dados = [
            'erros' => $this->getFileErros(),
            'variaveis' => (object) $this->variaveisErros
        ];
    }
}