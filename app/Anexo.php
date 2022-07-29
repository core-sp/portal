<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anexo extends Model
{
    use SoftDeletes;

    protected $table = 'anexos';
    protected $guarded = [];
    protected $touches = ['preRegistro'];

    const TOTAL_PF_PRE_REGISTRO = 10;
    const TOTAL_PJ_PRE_REGISTRO = 15;
    const PATH_PRE_REGISTRO = 'userExterno/pre_registros';

    private static function ziparFilesPreRegistro($files)
    {
        $path = storage_path('app/') . Anexo::PATH_PRE_REGISTRO . '/';
        $nomeZip = (string) Str::uuid() . '.zip';

        if(!file_exists(storage_path('app/') . Anexo::PATH_PRE_REGISTRO))
            Storage::makeDirectory(Anexo::PATH_PRE_REGISTRO);
        if(!file_exists(storage_path('app/temp')))
            Storage::makeDirectory('temp');

        $nomeFiles = '';
        $nomesTemp = '';
        foreach($files as $key => $file)
        {
            $nome = (string) Str::uuid() . '.' . $file->extension();
            $file->storeAs('temp', $nome, 'local');
            $nomeFiles .= $nome . ' ';
            $nomesTemp .= 'temp/' . $nome . ' ';
        }
        $final = shell_exec('cd ' . storage_path('app/temp') . ' ; zip ' . $path . $nomeZip . ' ' . $nomeFiles);

        $final = explode(PHP_EOL, $final);
        foreach($final as $key => $fim)
            if($fim == '')
                unset($final[$key]);
                
        $array = explode(' ', $nomesTemp);
        Storage::delete($array);
    
        if(count($final) != count($files))
        {
            Storage::delete(Anexo::PATH_PRE_REGISTRO . '/' . $nomeZip);
            throw new \Exception('Erro ao comprimir os arquivos do pré-registro, pois não possui a mesma quantidade do que foi enviado', 500);
        }

        return [
            'path' => Anexo::PATH_PRE_REGISTRO . '/' . $nomeZip,
            'nome_original' => $nomeZip,
            'tamanho_bytes' => Storage::size(Anexo::PATH_PRE_REGISTRO . '/' . $nomeZip),
            'extensao' => 'zip',
        ];
    }

    public static function camposPreRegistro()
    {
        return [
            'a1' => 'path',
        ];
    }

    public function preRegistro()
    {
        return $this->belongsTo('App\PreRegistro');
    }

    public static function armazenar($total, $valor, $pf = true)
    {
        $totalAnexo = $pf ? Anexo::TOTAL_PF_PRE_REGISTRO : Anexo::TOTAL_PJ_PRE_REGISTRO;

        if($total < $totalAnexo)
        {
            if(count($valor) > 1)
                return Anexo::ziparFilesPreRegistro($valor);
            $nome = (string) Str::uuid() . '.' . $valor[0]->extension();
            $anexo = $valor[0]->storeAs(Anexo::PATH_PRE_REGISTRO, $nome, 'local');
            return [
                'path' => $anexo,
                'nome_original' => $valor[0]->getClientOriginalName(),
                'tamanho_bytes' => Storage::size($anexo),
                'extensao' => $valor[0]->extension(),
            ];
        }

        return null;
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

    private function getAceitosPF($preRegistro, $tipos)
    {
        if($preRegistro->pessoaFisica->nacionalidade != 'BRASILEIRA')
            unset($tipos[3]);

        if(($preRegistro->pessoaFisica->sexo != 'M') || (($preRegistro->pessoaFisica->sexo == 'M') && $preRegistro->pessoaFisica->maisDe45Anos()))
            unset($tipos[4]);

        unset($tipos[5]);
        unset($tipos[6]);
        unset($tipos[7]);        

        return $tipos;
    }

    public function getObrigatoriosPreRegistro()
    {
        $tipos = Anexo::getAceitosPreRegistro();
        $preRegistro = $this->preRegistro;

        if($preRegistro->userExterno->isPessoaFisica())
            $tipos = $this->getAceitosPF($preRegistro, $tipos);
        else
        {
            // por não saber via sistema se os sócios são do sexo masculino ou não
            unset($tipos[3]);
            unset($tipos[4]);
        }

        return $tipos;
    }

    public function getOpcoesPreRegistro()
    {
        $tipos = Anexo::getAceitosPreRegistro();
        $preRegistro = $this->preRegistro;

        if($preRegistro->userExterno->isPessoaFisica())
            $tipos = $this->getAceitosPF($preRegistro, $tipos);

        return $tipos;
    }
}
