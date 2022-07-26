<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Anexo extends Model
{
    protected $table = 'anexos';
    protected $guarded = [];
    protected $touches = ['preRegistro'];

    const TOTAL_PF_PRE_REGISTRO = 10;
    const TOTAL_PJ_PRE_REGISTRO = 15;
    const PATH_PRE_REGISTRO = 'userExterno/pre_registros';

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
            $nome = (string) Str::uuid() . '.' . $valor->extension();
            return $valor->storeAs(Anexo::PATH_PRE_REGISTRO, $nome, 'local');
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
