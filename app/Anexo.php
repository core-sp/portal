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

    private static function ziparFilesPreRegistro($files, $id)
    {
        $pathStorage = config('app.env') == "testing" ? storage_path('framework/testing/disks/local/') : storage_path('app/');
        $path = $pathStorage . Anexo::PATH_PRE_REGISTRO . '/' . $id . '/';
        $nomeZip = (string) Str::uuid() . '.zip';

        if(!file_exists($pathStorage . Anexo::PATH_PRE_REGISTRO . '/' . $id))
            Storage::makeDirectory(Anexo::PATH_PRE_REGISTRO . '/' . $id);
        if(!file_exists($pathStorage . 'temp/' . $id))
            Storage::makeDirectory('temp/' . $id);
            
        $nomeFiles = '';
        $nomesTemp = '';
        $cont = 1;
        foreach($files as $key => $file)
        {
            $nome = (string) Carbon::now()->timestamp . '_' . $cont . '.' . $file->extension();
            $file->storeAs('temp/' . $id, $nome, 'local');
            $cont++;
        }
        $final = shell_exec('cd ' . $pathStorage . 'temp/' . $id . ' ; zip -r ' . $path . $nomeZip . ' .');
        Storage::deleteDirectory('temp/' . $id);

        $finalArray = isset($final) ? explode(PHP_EOL, $final) : array();
        foreach($finalArray as $key => $fim)
            if($fim == '')
                unset($finalArray[$key]);
    
        if(count($finalArray) != count($files))
        {
            Storage::delete(Anexo::PATH_PRE_REGISTRO . '/' . $id . '/' . $nomeZip);
            throw new \Exception('Erro ao comprimir os arquivos do pré-registro, pois não possui a mesma quantidade do que foi enviado - Erro shell: ' . $final, 500);
        }

        return [
            'path' => Anexo::PATH_PRE_REGISTRO . '/' . $id . '/' . $nomeZip,
            'nome_original' => $nomeZip,
            'tamanho_bytes' => Storage::size(Anexo::PATH_PRE_REGISTRO . '/' . $id . '/' . $nomeZip),
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

    public function excluirDiretorioPreRegistro()
    {
        $diretorio = Anexo::PATH_PRE_REGISTRO . '/';
        if(Storage::disk('local')->exists($diretorio . $this->preRegistro->id))
            return Storage::deleteDirectory($diretorio . $this->preRegistro->id);
        return true;
    }

    public static function armazenar($total, $valor, $id, $pf = true)
    {
        $totalAnexo = $pf ? Anexo::TOTAL_PF_PRE_REGISTRO : Anexo::TOTAL_PJ_PRE_REGISTRO;

        if($total < $totalAnexo)
        {
            if(count($valor) > 1)
                return Anexo::ziparFilesPreRegistro($valor, $id);
            $nome = (string) Str::uuid() . '.' . $valor[0]->extension();
            $anexo = $valor[0]->storeAs(Anexo::PATH_PRE_REGISTRO . '/' . $id, $nome, 'local');
            return [
                'path' => $anexo,
                'nome_original' => $valor[0]->getClientOriginalName(),
                'tamanho_bytes' => Storage::size($anexo),
                'extensao' => $valor[0]->extension(),
            ];
        }

        return null;
    }

    public static function armazenarDoc($id, $file, $tipo_doc)
    {
        $doc = self::where('nome_original', $tipo_doc . '_aprovado_' . $id)->first();
        if(isset($doc))
        {
            Storage::disk('local')->delete($doc->path);
            $doc->delete();
        }

        $nome = (string) Str::uuid() . '.' . $file->extension();
        $anexo = $file->storeAs(self::PATH_PRE_REGISTRO . '/' . $id, $nome, 'local');

        return [
            'path' => $anexo,
            'nome_original' => $tipo_doc . '_aprovado_' . $id,
            'tamanho_bytes' => Storage::size($anexo),
            'extensao' => $file->extension(),
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

    public function anexadoPeloAtendente()
    {
        $nomes_doc = [
            'boleto_aprovado_' . $this->pre_registro_id,
        ];

        return in_array($this->nome_original, $nomes_doc);
    }
}
