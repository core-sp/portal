<?php

namespace App;

use App\Mail\RepresentanteResetPasswordMail;
use App\Notifications\RepresentanteResetPasswordNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticable;
use Illuminate\Support\Facades\Mail;
use App\Connections\FirebirdConnection;
use App\Traits\GerentiProcedures;

class Representante extends Authenticable
{
    use Notifiable, SoftDeletes, GerentiProcedures;

    protected $guard = 'representante';

    protected $fillable = ['cpf_cnpj', 'registro_core', 'ass_id', 'nome', 'email', 'password', 'verify_token', 'aceite', 'ativo'];

    protected $hidden = ['password', 'remember_token'];

    public function sendPasswordResetNotification($token)
    {
        $body = emailResetRepresentante($token);

        // $this->notify(new RepresentanteResetPasswordNotification($token)); - FALLBACK
        Mail::to($this->email)->send(new RepresentanteResetPasswordMail($token, $body));
    }

    public function enderecos()
    {
        $enderecos = $this->gerentiEnderecos($this->ass_id);

        return utf8_converter($enderecos);
    }

    public function contatos()
    {
        return $this->gerentiContatos($this->ass_id);
    }

    public function tipoPessoa()
    {
        return strlen($this->cpf_cnpj) === 14 ? 'PF' : 'PJ';
    }

    public function dadosGerais()
    {
        if($this->tipoPessoa() === 'PF') {
            $dados = $this->gerentiDadosGeraisPF($this->ass_id);
            return $this->arrangeDgPf($dados);
        } else {
            $dados = $this->gerentiDadosGeraisPJ($this->ass_id);
            return $this->arrangeDgPj($dados);
        }
    }

    protected function arrangeDgPf($dados)
    {
        $dados = formataDataGerentiRecursive($dados);
        $rg = ['RG' => $dados['identidade'] . '<span class="light">, expedido em</span> ' . $dados['expedicao'] . '<span class="light">, por</span> ' . $dados['emissor']];
        unset($dados['expedicao'], $dados['emissor'], $dados['identidade']);
        $novoDados = $rg + $dados;
        $toUtf = utf8_converter($novoDados);

        return $toUtf;
    }

    protected function arrangeDgPj($dados)
    {
        $dados = formataDataGerentiRecursive($dados);
        $dados = utf8_converter($dados);

        if(!empty($dados['Responsável Técnico'])) {
            $rtArray = explode('-', $dados['Responsável Técnico']);
            $rt = ['Responsável técnico' => $rtArray[1] . ' (' . $rtArray[0] . ')'];

            unset($dados['Responsável Técnico']);

            $novosDados = $rt + $dados;
        } else {
            $novosDados = $dados;
        }

        return $novosDados;
    }

    public function getCpfCnpjAttribute($value)
    {
        if(strlen($value) === 11) {
            return substr($value, 0, 3) . '.' . substr($value, 3, 3) . '.' . substr($value, 6, 3) . '-' . substr($value, 9, 2);
        } elseif(strlen($value) === 14) {
            return substr($value, 0, 2) . '.' . substr($value, 2, 3) . '.' . substr($value, 5, 3) . '/' . substr($value, 8, 4) . '-' . substr($value, 12, 2);
        } else {
            return 'Indefinido';
        }
    }

    public function getRegistroCoreAttribute($value)
    {
        return substr_replace($value, '/', -4, 0);
    }

    public function cobrancas()
    {
        $values = $this->gerentiBolestosLista($this->ass_id);
        $values = utf8_converter($values);
        
        $anuidades = [];
        $outros = [];

        foreach($values as $value) {
            if (strpos($value['DESCRICAO'], 'Anuidade') !== false) {
                array_push($anuidades, $value);
            } else {
                array_push($outros, $value);
            }
        }
        
        $result = [
            'anuidades' => $anuidades,
            'outros' => $outros
        ];

        return $result;
    }

    public function cobrancasById($ass_id)
    {
        $values = $this->gerentiBolestosLista($ass_id);
        $values = utf8_converter($values);
        
        $anuidades = [];
        $outros = [];

        foreach($values as $value) {
            if (strpos($value['DESCRICAO'], 'Anuidade') !== false) {
                array_push($anuidades, $value);
            } else {
                array_push($outros, $value);
            }
        }
        
        $result = [
            'anuidades' => $anuidades,
            'outros' => $outros
        ];

        return $result;
    }

    public function solicitacoesEnderecos()
    {
        return RepresentanteEndereco::where('ass_id', '=', $this->ass_id)->where('status', '!=', 'Enviado')->orderBy('created_at', 'DESC')->get();
    }

    public function status()
    {
        return $this->gerentiStatus($this->ass_id);
    }

    public function boletoAnuidade()
    {
        $cpfCnpj = preg_replace('/[^0-9]+/', '', $this->cpf_cnpj);
        if(isset($this->gerentiAnuidadeVigente($cpfCnpj)[0]['NOSSONUMERO']))
            return $this->gerentiAnuidadeVigente($cpfCnpj)[0]['NOSSONUMERO'];
        else
            return null;
    }

    // Método para formatar os dados de endereço do GERENTI para emissão de Certidão
    public function enderecoFormatado() 
    {
        $enderecoGerenti = $this->enderecos();

        $enderecoFormatado = $enderecoGerenti["Logradouro"];
        
        if(isset($enderecoGerenti["Complemento"])) {
            $enderecoFormatado .= ", " . $enderecoGerenti["Logradouro"];
        }

        $enderecoFormatado .= ", " . $enderecoGerenti["Bairro"];
        $enderecoFormatado .= " - " . $enderecoGerenti["Cidade"] . "/" . $enderecoGerenti["UF"];
        $enderecoFormatado .= " - CEP: " . $enderecoGerenti["CEP"];

        return $enderecoFormatado;
    }
}
