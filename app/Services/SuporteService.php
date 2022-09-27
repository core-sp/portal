<?php

namespace App\Services;

use App\Contracts\SuporteServiceInterface;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SuporteService implements SuporteServiceInterface {

    private $variaveisLog;
    private $variaveisErros;
    private $nomeFileErros;

    public function __construct()
    {
        $this->variaveisLog = [
            'mostra' => 'log_externo',
            'singular' => 'Logs',
            'singulariza' => 'o log',
        ];

        $this->variaveisErros = [
            'mostra' => 'suporte_erros',
            'singular' => 'Tabela de Erros',
            'singulariza' => 'os erros',
        ];

        $this->nomeFileErros = 'suporte-tabela-erros.txt';
    }

    const ERROS = 'erros';
    const INTERNO = 'interno';
    const EXTERNO = 'externo';

    private function getLog($data, $tipo)
    {
        if($this->hasLog($data, $tipo))
        {
            $log = Storage::disk('log_' . $tipo)->get($this->getPathLogFile($data, $tipo));
            return $this->editarConteudoLog($log, $tipo);
        }

        return null;
    }

    private function getLastModificationLog($data, $tipo)
    {
        return $this->hasLog($data, $tipo) ? Storage::disk('log_' . $tipo)->lastModified($this->getPathLogFile($data, $tipo)) : null;
    }

    private function editarConteudoLog($log, $tipo)
    {
        $array = explode(PHP_EOL, $log);
        unset($log);

        array_unshift($array, 'LOG ' . strtoupper($tipo).PHP_EOL, '==================================================================================='.PHP_EOL);
        foreach($array as $key => $value)
        {
            if($key > 1)
            {
                $i = $key - 1;
                $array[$key] = substr_replace($array[$key], '#'.$i.' - ', 0, 0).PHP_EOL;
            }
        }

        return $array;
    }

    private function getPathLogFile($data, $tipo)
    {
        $data = Carbon::createFromFormat('Y-m-d', $data);
        $anoMes = $data->format('Y').'/'.$data->format('m').'/';
        $nomeArquivo = 'laravel-'.$data->format('Y-m-d').'.log';

        return $tipo == self::ERROS ? $nomeArquivo : $anoMes.$nomeArquivo;
    }

    private function getPathsLogsMonth($data)
    {
        $data = Carbon::createFromFormat('Y-m', $data);
        $anoMes = $data->format('Y').'/'.$data->format('m').'/';

        return $anoMes;
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

    private function hasLog($data, $tipo)
    {
        $log = $this->getPathLogFile($data, $tipo);
        return Storage::disk('log_' . $tipo)->exists($log);
    }

    private function editarConteudoErros($erros)
    {
        return explode(PHP_EOL, $erros);
    }

    public function indexLog()
    {
        $infos[self::ERROS] = $this->getLastModificationLog(date('Y-m-d'), self::ERROS);
        $infos[self::INTERNO] = $this->getLastModificationLog(date('Y-m-d'), self::INTERNO);
        $infos[self::EXTERNO] = $this->getLastModificationLog(date('Y-m-d'), self::EXTERNO);

        $infos[self::ERROS] = isset($infos[self::ERROS]) ? Carbon::parse($infos[self::ERROS])->setTimezone('America/Sao_Paulo')->format('d/m/Y, \à\s H:i') : null;
        $infos[self::INTERNO] = isset($infos[self::INTERNO]) ? Carbon::parse($infos[self::INTERNO])->setTimezone('America/Sao_Paulo')->format('d/m/Y, \à\s H:i') : null;
        $infos[self::EXTERNO] = isset($infos[self::EXTERNO]) ? Carbon::parse($infos[self::EXTERNO])->setTimezone('America/Sao_Paulo')->format('d/m/Y, \à\s H:i') : null;

        return [
            'info' => $infos,
            'variaveis' => (object) $this->variaveisLog
        ];
    }

    public function logBusca($request)
    {        
        if(isset($request['data']))
        {
            $dados['resultado'] = $this->hasLog($request['data'], $request['tipo']) ? $request['data'] : null;
            return $dados;
        }

        if(isset($request['mes']) || isset($request['ano']))
        {
            $array = array();
            $diretorio = isset($request['mes']) ? $this->getPathsLogsMonth($request['mes']) : $request['ano'];

            $all = Storage::disk('log_'.$request['tipo'])->allFiles($diretorio);
            foreach($all as $key => $file)
            {
                $conteudo = Storage::disk('log_'.$request['tipo'])->get($file);
                $log = $this->editarConteudoLog($conteudo, $request['tipo']);
                unset($conteudo);

                $resultado = preg_grep('/'.preg_quote($request['texto'], '/').'/i', $log);
                if(count($resultado) > 0)
                    array_push($array, str_replace('.log', '', substr($file, 16)));

                unset($log);
                unset($resultado);
            }
            
            $dados['resultado'] = count($array) > 0 ? $array : null;
            return $dados;
        }
    }

    public function logPorData($data, $tipo)
    {
        if(!in_array($tipo, [self::ERROS, self::INTERNO, self::EXTERNO]))
            throw new \Exception('Tipo de log não existente', 500);

        $headers = [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="laravel-'.$data.'.log"'
        ];
        $log = $this->getLog($data, $tipo);
        return isset($log) ? response()->stream($this->getLogForBrowser($log), 200, $headers) : null;
    }

    public function indexErros()
    {
        $erros = Storage::disk('local')->exists($this->nomeFileErros) ? $this->editarConteudoErros(Storage::disk('local')->get($this->nomeFileErros)) : null;
        return $dados = [
            'erros' => $erros,
            'variaveis' => (object) $this->variaveisErros
        ];
    }

    public function uploadFileErros($file)
    {
        $file->storeAs('', $this->nomeFileErros, 'local');
    }

    public function getFileErros()
    {
        return Storage::disk('local')->exists($this->nomeFileErros) ? Storage::disk('local')->path($this->nomeFileErros) : null;
    }
}