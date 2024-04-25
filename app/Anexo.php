<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class Anexo extends Model
{
    protected $table = 'anexos';
    protected $guarded = [];
    protected $touches = ['preRegistro'];

    const TOTAL_PF_PRE_REGISTRO = 10;
    const TOTAL_PJ_PRE_REGISTRO = 15;
    const PATH_PRE_REGISTRO = 'userExterno/pre_registros';

    private static function criarNomeFile($extensao)
    {
        return (string) Str::uuid() . '.' . $extensao;
    }

    private static function criarNomeFilesZip($cont = 1, $extensao)
    {
        return (string) Carbon::now()->timestamp . '_' . $cont . '.' . $extensao;
    }

    private static function confereDiretorio($id, $zipar = false)
    {
        $pathStorage = config('app.env') == "testing" ? storage_path('framework/testing/disks/local/') : storage_path('app/');
        $path_id = self::PATH_PRE_REGISTRO . '/' . $id;
        $path_absoluto = $pathStorage . $path_id . '/';
        $temp = 'temp/' . $id;

        if(!file_exists($pathStorage . $path_id))
            Storage::makeDirectory($path_id);
        if($zipar && !file_exists($pathStorage . $temp))
            Storage::makeDirectory($temp);

        return [
            'pathStorage' => $pathStorage,
            'pathAbsoluto' => $path_absoluto,
            'path' => $path_id,
            'temp' => $temp,
        ];
    }

    private static function salvar_files_bd_storage($files, $id)
    {
        $zipar = count($files) > 1;
        $dados = self::confereDiretorio($id, $zipar);

        if(count($files) == 1)
        {
            $nome = self::criarNomeFile($files[0]->extension());
            $anexo = $files[0]->storeAs($dados['path'], $nome, 'local');
            return [
                'path' => $anexo,
                'nome_original' => $files[0]->getClientOriginalName(),
                'tamanho_bytes' => Storage::size($anexo),
                'extensao' => $files[0]->extension(),
            ];
        }

        $cont = 1;
        foreach($files as $key => $file)
        {
            $nome = self::criarNomeFilesZip($cont++, $file->extension());
            $file->storeAs($dados['temp'], $nome, 'local');
        }

        return self::ziparFilesPreRegistro($dados, $files, $id);
    }

    private static function ziparFilesPreRegistro($dados, $files, $id)
    {
        $extensao = 'zip';
        $nomeZip = self::criarNomeFile($extensao);
        $caminho_atual = $dados['path'] . '/' . $nomeZip;

        $final = shell_exec('cd ' . $dados['pathStorage'] . $dados['temp'] . ' ; zip -r ' . $dados['pathAbsoluto'] . $nomeZip . ' .');
        Storage::deleteDirectory($dados['temp']);

        $finalArray = count(array_filter(explode(PHP_EOL, $final)));

        if($finalArray != count($files))
        {
            Storage::delete($caminho_atual);
            throw new \Exception('Erro ao comprimir os arquivos do pré-registro, pois não possui a mesma quantidade do que foi enviado - Erro shell: ' . $final, 500);
        }

        return [
            'path' => $caminho_atual,
            'nome_original' => $nomeZip,
            'tamanho_bytes' => Storage::size($caminho_atual),
            'extensao' => $extensao,
        ];
    }

    protected static function criarFinal($campo, $valor, $pr)
    {
        $resultado = null;
        $anexos = $pr->anexos();
        $valido = self::armazenar($anexos->count(), $valor, $pr->id, $pr->userExterno->isPessoaFisica());
        if(isset($valido))
        {
            $resultado = $anexos->create($valido);
            $pr->touch();
        }else
            $resultado = $valido;
        
        return $resultado;
    }

    public static function tiposDocsAtendentePreRegistro()
    {
        return [
            'boleto',
        ];
    }

    public static function camposPreRegistro()
    {
        return [
            'path',
        ];
    }

    public function preRegistro()
    {
        return $this->belongsTo('App\PreRegistro');
    }

    public function excluirDiretorioPreRegistro()
    {
        $diretorio = self::PATH_PRE_REGISTRO . '/' . $this->preRegistro->id;
        if(Storage::disk('local')->exists($diretorio))
            return Storage::deleteDirectory($diretorio);
        return true;
    }

    public static function armazenar($total, $valor, $id, $pf = true)
    {
        $totalAnexo = $pf ? self::TOTAL_PF_PRE_REGISTRO : self::TOTAL_PJ_PRE_REGISTRO;

        if($total < $totalAnexo)
            return self::salvar_files_bd_storage($valor, $id);

        return null;
    }

    public static function armazenarDoc($id, $file, $tipo_doc)
    {
        $doc = self::where('tipo', $tipo_doc)->where('pre_registro_id', $id)->first();
        if(isset($doc))
        {
            Storage::disk('local')->delete($doc->path);
            $doc->delete();
        }

        $nome = self::criarNomeFile($file->extension());
        $anexo = $file->storeAs(self::PATH_PRE_REGISTRO . '/' . $id, $nome, 'local');

        return [
            'path' => $anexo,
            'nome_original' => $file->getClientOriginalName(),
            'tamanho_bytes' => Storage::size($anexo),
            'extensao' => $file->extension(),
            'tipo' => $tipo_doc,
        ];
    }

    private static function getAceitosPreRegistro()
    {
        return [
            'Comprovante de identidade',
            'CPF',
            'Comprovante de Residência',
            'Certidão de quitação eleitoral',
            'Cerificado de reservista ou dispensa',
            'Comprovante de inscrição CNPJ',
            'Contrato Social',
            'Declaração Termo de indicação RT ou Procuração'
        ];
    }

    private function getAceitosPF()
    {
        $tipos = self::getAceitosPreRegistro();

        if($this->preRegistro->pessoaFisica->nacionalidade != 'BRASILEIRA')
            unset($tipos[3]);

        if(($this->preRegistro->pessoaFisica->sexo != 'M') || (($this->preRegistro->pessoaFisica->sexo == 'M') && $this->preRegistro->pessoaFisica->maisDe45Anos()))
            unset($tipos[4]);

        unset($tipos[5]);
        unset($tipos[6]);
        unset($tipos[7]);        

        return $tipos;
    }

    private function getAceitosPJ()
    {
        $tipos = self::getAceitosPreRegistro();

        // por não saber via sistema se os sócios são do sexo masculino ou não
        unset($tipos[3]);
        unset($tipos[4]);

        return $tipos;
    }

    public function getObrigatoriosPreRegistro()
    {
        return $this->preRegistro->userExterno->isPessoaFisica() ? $this->getAceitosPF() : $this->getAceitosPJ();
    }

    public function getOpcoesPreRegistro()
    {
        if($this->preRegistro->userExterno->isPessoaFisica())
            return $this->getAceitosPF();

        return self::getAceitosPreRegistro();
    }

    public function anexadoPeloAtendente()
    {
        return isset($this->tipo) && in_array($this->tipo, self::tiposDocsAtendentePreRegistro());
    }
}
