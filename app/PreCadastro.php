<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreCadastro extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    // Status de pré-cadastro
    const STATUS_APROVADO = 'Aprovado';
    const STATUS_RECUSADO = 'Recusado';
    const STATUS_PEDENTE = 'Pendente';

    // Tipos de pré-cadastro
    const TIPO_PRE_CADASTRO_PF_AUTONOMA = 'Pessoa Física Autônoma';
    const TIPO_PRE_CADASTRO_PF_RT = 'Pessoa Física Responsável Técnico';
    const TIPO_PRE_CADASTRO_PJ_INDIVIDUAL = 'Pessoa Jurídica - Firma Individual';
    const TIPO_PRE_CADASTRO_PJ_VARIADO = 'Pessoa Jurídica - LTDA ou EIRELI ou Unipessoal LTDA';

    // Estado civil de PF
    const ESTADO_CIVIL_SOLTEIRO = 'Solteiro';
    const ESTADO_CIVIL_CASADO = 'Casado';

    // Sexo de PF
    const SEXO_MASCULINO = 'Masculino';
    const SEXO_FEMININO = 'Feminino';

    // Tipo de documento de indetificação de PF
    const TIPO_DOCUMENTO_RG = 'RG';
    const TIPO_DOCUMENTO_CNH = 'CNH';

    // Ramos de atividade de PJ
    const RAMO_REPRESENTACAO = 'Representação';
    const RAMO_INTERMEDIACAO_AGENCIAMENTO = 'Intermediação e Agenciamento';
    const RAMO_DISTRIBUICAO = 'Distribuição';

    public static function tipoPreCadastro() 
    {
        return [
            PreCadastro::TIPO_PRE_CADASTRO_PF_AUTONOMA,
            PreCadastro::TIPO_PRE_CADASTRO_PF_RT,
            PreCadastro::TIPO_PRE_CADASTRO_PJ_INDIVIDUAL,
            PreCadastro::TIPO_PRE_CADASTRO_PJ_VARIADO
        ];
    }

    public static function statusPreCadastro() 
    {
        return [
            PreCadastro::STATUS_APROVADO,
            PreCadastro::STATUS_RECUSADO,
            PreCadastro::STATUS_PEDENTE
        ];
    }

    public static function estadoCivil() 
    {
        return [
            PreCadastro::ESTADO_CIVIL_SOLTEIRO,
            PreCadastro::ESTADO_CIVIL_CASADO
        ];
    }

    public static function sexo() 
    {
        return [
            PreCadastro::SEXO_MASCULINO,
            PreCadastro::SEXO_FEMININO
        ];
    }

    public static function tipoDocumento() 
    {
        return [
            PreCadastro::TIPO_DOCUMENTO_RG,
            PreCadastro::TIPO_DOCUMENTO_CNH
        ];
    }

    public static function ramoAtividade() 
    {
        return [
            PreCadastro::RAMO_REPRESENTACAO,
            PreCadastro::RAMO_INTERMEDIACAO_AGENCIAMENTO,
            PreCadastro::RAMO_DISTRIBUICAO
        ];
    }
}