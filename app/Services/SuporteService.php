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

    private function getLinesFile($file)
    {
        $f = fopen($file, 'r');
        while(($line = fgets($f)) !== false)
        {
            yield $line;
        }
        fclose($f);
    }

    private function getLastModificationLog($data, $tipo)
    {
        return $this->hasLog($data, $tipo) ? Storage::disk('log_' . $tipo)->lastModified($this->getPathLogFile($data, $tipo)) : null;
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
        foreach([self::ERROS, self::INTERNO, self::EXTERNO] as $tipo)
        {
            $infos[$tipo] = $this->getLastModificationLog(date('Y-m-d'), $tipo);

            $infos[$tipo] = isset($infos[$tipo]) ? Carbon::parse($infos[$tipo])->setTimezone('America/Sao_Paulo')->format('d/m/Y, \à\s H:i') : null;

            $size[$tipo] = isset($infos[$tipo]) ? 
            number_format(Storage::disk('log_'.$tipo)->size($this->getPathLogFile(date('Y-m-d'), $tipo)) / 1024, 2).' KB' : null;
        }

        return [
            'info' => $infos,
            'size' => $size,
            'variaveis' => (object) $this->variaveisLog
        ];
    }

    public function logBusca($request)
    {  
        $dados['resultado'] = null;

        if(isset($request['data']))
        {
            if($this->hasLog($request['data'], $request['tipo']))
            {
                $size = Storage::disk('log_'.$request['tipo'])->size($this->getPathLogFile($request['data'], $request['tipo']));
                $size = number_format($size / 1024, 2) . ' KB';
                $dados['resultado'] = $request['data'] . ';' . $size;
            }

            return $dados;
        }

        if(isset($request['mes']) || isset($request['ano']))
        {
            $array = array();
            $diretorio = isset($request['mes']) ? $this->getPathsLogsMonth($request['mes']) : $request['ano'];
            $all = Storage::disk('log_'.$request['tipo'])->allFiles($diretorio);

            foreach($all as $key => $file)
            {
                $size = Storage::disk('log_'.$request['tipo'])->size($file);
                $size = number_format($size / 1024, 2) . ' KB';
                $path = Storage::disk('log_'.$request['tipo'])->path($file);
                $retorno = $this->getLinesFile($path);

                foreach($retorno as $line)
                {
                    if(strpos($line, $request['texto']) !== false)
                    {
                        array_push($array, str_replace('.log', '', substr($file, 16)) . ';' . $size);
                        break;
                    }
                }
            }
            
            if(count($array) > 0)
                $dados['resultado'] = $array;
            unset($array);

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
        $log = $this->hasLog($data, $tipo);
        return isset($log) ? response()->file(Storage::disk('log_'.$tipo)->path($this->getPathLogFile($data, $tipo)), $headers) : null;
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