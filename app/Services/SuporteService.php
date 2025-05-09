<?php

namespace App\Services;

use App\Contracts\SuporteServiceInterface;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\SuporteIp;
use App\Events\CrudEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\InternoSuporteMail;
use Illuminate\Support\LazyCollection;
use App\Suporte;

class SuporteService implements SuporteServiceInterface {

    private $variaveisLog;
    private $variaveisErros;
    private $nomeFileErros;

    const INTEGRO = "/.hash.json";

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

        $this->variaveisIps = [
            'mostra' => 'suporte_ips',
            'singular' => 'Tabela de IPs bloqueados e liberados',
            'singulariza' => 'os ips',
        ];

        $this->nomeFileErros = 'suporte-tabela-erros.txt';
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

        return $tipo == Suporte::ERROS ? $nomeArquivo : $anoMes.$nomeArquivo;
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
        foreach([Suporte::ERROS, Suporte::INTERNO, Suporte::EXTERNO] as $tipo)
        {
            $infos[$tipo] = $this->getLastModificationLog(date('Y-m-d'), $tipo);

            $infos[$tipo] = isset($infos[$tipo]) ? Carbon::parse($infos[$tipo])->setTimezone('America/Sao_Paulo')->format('d/m/Y, \à\s H:i') : null;

            $size[$tipo] = isset($infos[$tipo]) ? 
            number_format(Storage::disk('log_'.$tipo)->size($this->getPathLogFile(date('Y-m-d'), $tipo)) / 1024, 2).' KB' : null;
        }

        return [
            'info' => $infos,
            'size' => $size,
            'variaveis' => (object) $this->variaveisLog,
            'tipos' => Suporte::tipos(),
            'tipos_textos' => Suporte::tiposTextos(),
            'filtros' => Suporte::filtros(),
            'tabelaRelatorio' => Suporte::camposTabelaRelatorio(),
            'suporte' => new Suporte(),
        ];
    }

    public function logBusca($request)
    {  
        $totalFinal = 0;
        $dados['resultado'] = null;
        $dados['totalFinal'] = $totalFinal;

        if(isset($request['data']))
        {
            if($this->hasLog($request['data'], $request['tipo']))
            {
                $size = Storage::disk('log_'.$request['tipo'])->size($this->getPathLogFile($request['data'], $request['tipo']));
                $size = number_format($size / 1024, 2, ',', '.') . ' KB';
                $dados['resultado'] = $request['data'] . ';' . $size;
            }

            return $dados;
        }

        if(isset($request['mes']) || isset($request['ano']))
        {
            $array = array();
            $diretorio = isset($request['mes']) ? $this->getPathsLogsMonth($request['mes']) : $request['ano'];
            $all = Storage::disk('log_'.$request['tipo'])->allFiles($diretorio);
            $com_total_linhas = (isset($request['n_linhas']) && ($request['n_linhas'] == 'on')) || isset($request['relatorio']);
            $distintos = (isset($request['distintos']) && ($request['distintos'] == 'on')) || isset($request['relatorio']);
            $array_unique = array();

            foreach($all as $key => $file)
            {
                if($file == ($diretorio . self::INTEGRO))
                    continue;

                $total = 0;
                $size = Storage::disk('log_'.$request['tipo'])->size($file);
                $size = number_format($size / 1024, 2, ',', '.') . ' KB';
                $path = Storage::disk('log_'.$request['tipo'])->path($file);

                $f = fopen($path, 'r');
                while(($line = fgets($f)) !== false)
                {
                    if(stripos($line, $request['texto']) !== false)
                    {
                        if($distintos)
                        {
                            // verificação de registros de log antes de inserir ip.
                            $separador = stripos($line, '.INFO: [IP: ') !== false ? '] - ' : '.INFO: ';
                            $pos = stripos($line, $separador) + strlen($separador);
                            // remove parte inicial do log envolvendo data e ip.
                            $txt_inicio = trim(substr($line, $pos));
                            // remove parte final do log após o texto buscado.
                            $pos_final = strlen($request['texto']) > strlen($txt_inicio) ? strlen($txt_inicio) : strlen($request['texto']);
                            $txt = substr($txt_inicio, 0, stripos($txt_inicio, $request['texto']) + $pos_final);
                            if(!in_array($txt, $array_unique))
                            {
                                array_push($array_unique, $txt);
                                $total++;
                                isset($request['relatorio']) ? $request['relatorio']['distintos']++ : null;
                            }
                        }

                        if((!$distintos && $com_total_linhas) || isset($request['relatorio']))
                            isset($request['relatorio']) ? $request['relatorio']['geral']++ : $total++;

                        if(!$com_total_linhas && !$distintos)
                        {
                            array_push($array, str_replace('.log', '', substr($file, 16)) . ';' . $size);
                            break;
                        }
                    }
                }
                fclose($f);
                unset($f);
                
                if(($distintos && !$com_total_linhas) && ($total > 0) && !isset($request['relatorio']))
                    array_push($array, str_replace('.log', '', substr($file, 16)) . ';' . $size);

                if($com_total_linhas && ($total > 0) && !isset($request['relatorio']))
                    array_push($array, str_replace('.log', '', substr($file, 16)) . ';' . $size . ';' . $total);
                $totalFinal += $total;
            }

            if(isset($array[0]))
                $dados['resultado'] = $array;
            unset($array);
            $dados['totalFinal'] = $totalFinal;

            return isset($request['relatorio']) ? $request['relatorio'] : $dados;
        }
    }

    public function logPorData($data, $tipo)
    {
        $conteudo = null;
        if(!in_array($tipo, [Suporte::ERROS, Suporte::INTERNO, Suporte::EXTERNO]))
            throw new \Exception('Tipo de log não existente', 500);

        $log = $this->hasLog($data, $tipo);
        if(isset($log) && $log)
        {
            $conteudo = '';
            $path = Storage::disk('log_'.$tipo)->path($this->getPathLogFile($data, $tipo));
            $logs = LazyCollection::make(function() use($path){
                $handle = fopen($path, 'r');
                while(($line = fgets($handle)) !== false)
                    yield $line;

                fclose($handle);
            });
        
            foreach($logs as $line)
                $conteudo .= $line;
        }
    
        return isset($conteudo) ? $conteudo : null;
    }

    public function verificaHashLog($data, $tipo)
    {
        $log = Storage::disk('log_'.$tipo)->path($this->getPathLogFile($data, $tipo));
        $data = Carbon::createFromFormat('Y-m-d', $data);
        $hash_file = $data->year . self::INTEGRO;

        if(Storage::disk('log_' . $tipo)->exists($hash_file))
            $json = json_decode(Storage::disk('log_'.$tipo)->get($hash_file), true);

        return isset($json[$data->format('Y-m-d')]) ? hash_file('sha256', $log) == $json[$data->format('Y-m-d')] : 'Hash ainda não foi criado!';
    }

    public function relatorios($dados, $acao = null)
    {
        $suporte = new Suporte();

        if(isset($acao))
        {
            if(!in_array($acao, ['remover', 'visualizar', 'exportar-csv']))
                throw new \Exception('Ação não existe.', 404);

            $final = null;
            switch ($acao) {
                case 'remover':
                    $suporte->removerRelatorioPorNome($dados);
                    break;
                case 'visualizar':
                    $final = $suporte->getRelatorioPorNome($dados);
                    break;
                case 'exportar-csv':
                    $final['final'] = $suporte->exportarCsv($dados);
                    $final['headers'] = [
                        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                        'Content-type' => 'text/csv; charset=UTF-8',
                        'Content-Disposition' => 'attachment; filename='.$dados.'-'.date('Ymd').'.csv',
                        'Expires' => '0',
                    ];
                    break;
            }

            return $final;
        }

        $suporte->conferePodeCriar();
        $textos = Suporte::textosFiltros();

        $data = 'relat_' . $dados['relat_data'];
        $texto = $textos[$dados['relat_opcoes'] . '_' . $dados['relat_tipo']];

        $dados_final = [
            'tipo' => $dados['relat_tipo'],
            $dados['relat_data'] => $dados[$data],
            'texto' => $texto,
            'relatorio' => ['distintos' => 0, 'geral' => 0],
        ];

        $dados_final = $this->logBusca($dados_final);
        $dados_final['tipo'] = $dados['relat_tipo'];
        $dados_final['data'] = $dados[$data];
        $dados_final['opcoes'] = $dados['relat_opcoes'];

        return Suporte::criarRelatorio($dados_final);
    }

    public function relatorioFinal()
    {
        $suporte = new Suporte();
        return $suporte->getRelatorioFinalHTML();
    }

    public function filtros()
    {
        return Suporte::filtros();
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

    public function ipsBloqueados($ip = null)
    {
        return !isset($ip) ? SuporteIp::select('ip')->where('status', SuporteIp::BLOQUEADO)->get() : SuporteIp::where('status', SuporteIp::BLOQUEADO)->where('ip', $ip)->first();
    }

    public function ips()
    {
        return [
            'ips' => SuporteIp::where('status', SuporteIp::BLOQUEADO)
            ->orWhere('status', SuporteIp::LIBERADO)
            ->orderBy('status', 'DESC')
            ->orderBy('updated_at', 'DESC')
            ->paginate(15),
            'variaveis' => (object) $this->variaveisIps
        ];
    }

    public function bloquearIp($ip)
    {
        $registro = SuporteIp::where('ip', $ip)->first();

        if(!isset($registro))
            return SuporteIp::create(['ip' => $ip])->fresh();

        if(!$registro->isUpdateTentativa())
            return $registro;

        $registro = $registro->updateTentativa();
        if($registro->isBloqueado())
        {
            $texto = "[IP: " . $ip . "] - [Rotina Portal - Bloqueio de IP] - IP BLOQUEADO por segurança devido a alcançar o limite de ";
            $texto .= SuporteIp::TOTAL_TENTATIVAS . " tentativas de login.";
            \Log::channel('interno')->info($texto);
            \Log::channel('externo')->info($texto);
            $users = \App\User::where('idperfil', 1)->get();
            foreach($users as $user)
                Mail::to($user->email)->queue(new InternoSuporteMail($ip, $registro->status));
        }

        return $registro;
    }

    public function liberarIp($ip, $user = null)
    {
        $registro = SuporteIp::where('ip', $ip)->first();

        if(!isset($registro))
            return false;

        if(!isset($user) && $registro->isDesbloqueado())
            return $registro->delete();

        if(isset($user) && $registro->isBloqueado())
        {
            $registro->delete();
            event(new CrudEvent('desbloqueio de IP', 'realizou', $ip));
            $texto = "[IP: " . $ip . "] - IP DESBLOQUEADO por " . $user->nome . " (administrador do Portal) após análise.";
            \Log::channel('interno')->info($texto);
            \Log::channel('externo')->info($texto);
            $users = \App\User::where('idperfil', 1)->get();
            foreach($users as $userMail)
                Mail::to($userMail->email)->queue(new InternoSuporteMail($ip, SuporteIp::DESBLOQUEADO, $user));
            
            return true;
        }
        
        return false;
    }

    public function sobreStorage()
    {
        $comando_dir = 'du -s ';
        $comandos = [
            'img' => $comando_dir . public_path('imagens'),
            'arq' => $comando_dir . public_path('arquivos'),
            'arq_rep' => $comando_dir . \Storage::disk('local')->path('representantes'),
            'logs' => $comando_dir . storage_path('logs'),
            'termos' => $comando_dir . \Storage::disk('public')->path('termos'),
            'hd' => 'df | grep ^/dev/sda1',
        ];

        foreach($comandos as $chave => $comando)
        {
            $shell = shell_exec($comando);
            if(is_null($shell) || ($shell === false))
                throw new \Exception('Erro ao verificar o storage com o comando { ' . $comando . ' }.', 500);

            $shell_txt = explode(PHP_EOL, $shell, 1)[0];
            $reg = $chave == 'hd' ? '/[\s]+/' : '/\t/';
            $valor = preg_split($reg, $shell_txt);
            $num = $chave == 'hd' ? 1 : 0;
            $comandos[$chave] = round((int) $valor[$num] / 1024, 2);

            if($chave == 'hd')
                $comandos['disponivel'] = round((int) $valor[3] / 1024, 2);
        }

        return [
            'total' => str_replace('.', ',', (string) $comandos['hd']),
            'label' => 'em MB',
            'labels' => ['Arquivos e Imagens', 'Arquivos dos Representantes', 'Logs', 'Termos', 'Espaço Disponível'],
            'dados' => [$comandos['arq'] + $comandos['img'], $comandos['arq_rep'], $comandos['logs'], $comandos['termos'], $comandos['disponivel']],
            'cores' => ['rgb(255, 99, 132)', 'rgb(54, 162, 235)', 'rgb(255, 205, 86)', 'rgb(115, 209, 78)', 'rgb(126, 115, 226)'],
        ];
    }
}