<?php

namespace App\Services;

use App\UserExterno;
use App\Contracts\UserExternoServiceInterface;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\CadastroUserExternoMail;

class UserExternoService implements UserExternoServiceInterface {

    public function save($dados)
    {
        $dados['password'] = Hash::make($dados['password']);
        $dados['aceite'] = true;
        unset($dados['password_confirmation']);
        $emails = UserExterno::where('email', $dados['email'])->where('cpf_cnpj', '!=', $dados['cpf_cnpj'])->withTrashed()->count();

        if($emails >= 2)
            return [
                'erro' => 'Este email já alcançou o limite de cadastro, por favor insira outro.',
                'class' => 'alert-danger'
            ];

        $externo = UserExterno::where('cpf_cnpj', $dados['cpf_cnpj'])
            ->where('ativo', 0)
            ->withTrashed()
            ->first();
        
        $dados['verify_token'] = str_random(32);

        if(isset($externo))
        {
            if($externo->podeAtivar())
                return [
                    'erro' => 'Esta conta já solicitou o cadastro. Verifique seu email para ativar. Caso não tenha mais acesso ao e-mail, aguarde 24h para se recadastrar',
                    'class' => 'alert-danger'
                ];
            if($externo->trashed())
                $externo->restore();
            $externo->update($dados);
        }
        else
            $externo = UserExterno::create($dados);

        Mail::to($externo->email)->queue(new CadastroUserExternoMail($dados['verify_token']));
        event(new ExternoEvent('"' . $externo->cpf_cnpj . '" ("' . $externo->email . '") cadastrou-se na Área do Login Externo.'));
    }

    public function verificaEmail($token)
    {
        $externo = UserExterno::where('verify_token', $token)
            ->where('ativo', 0)
            ->first();
        if(!isset($externo) || !$externo->podeAtivar())
            return [
                'message' => 'Falha na verificação. Caso e-mail já tenha sido verificado, basta logar na área restrita do Login Externo, caso contrário, por favor refazer cadastro no Login Externo.',
                'class' => 'alert-danger'
            ];
        $externo->update([
            'ativo' => 1,
            'verify_token' => null
        ]);
        event(new ExternoEvent('User Externo ' . $externo->id . ' ("'. $externo->cpf_cnpj .'") verificou o email após o cadastro.'));
    }

    public function editDados($dados, $externo)
    {
        if(!isset($dados['nome']) && !isset($dados['email']))
            if(Hash::check($dados['password_atual'], $externo->password)) 
            {
                $externo->update(['password' => Hash::make($dados['password'])]);

                Mail::to($externo->email)->queue(new CadastroUserExternoMail(null, $externo));
                event(new ExternoEvent('Usuário Externo ' . $externo->id . ' ("'. $externo->cpf_cnpj .'") alterou a senha com sucesso na Área Restrita após logon.'));
            }else
                return [
                    'message' => 'A senha atual digitada está incorreta!',
                    'class' => 'alert-danger',
                ];
        else
        {
            if($dados['email'] != $externo->email)
            {
                $emails = UserExterno::where('email', $dados['email'])->withTrashed()->count();
                if($emails >= 2)
                    return [
                        'message' => 'Este email já alcançou o limite de cadastro, por favor insira outro.',
                        'class' => 'alert-danger',
                    ];
            }
            $externo->update([
                'nome' => mb_strtoupper($dados['nome'], 'UTF-8'),
                'email' => $dados['email']
            ]);

            Mail::to($externo->email)->queue(new CadastroUserExternoMail(null, $externo));
            event(new ExternoEvent('Usuário Externo ' . $externo->id . ' ("'. $externo->cpf_cnpj .'") alterou os dados com sucesso na Área Restrita após logon.'));
        }
    }

    public function findByCpfCnpj($cpf_cnpj)
    {
        $cpf_cnpj = apenasNumeros($cpf_cnpj);

        return UserExterno::where('cpf_cnpj', $cpf_cnpj)->first();
    }

    public function verificaSeAtivo($cpf_cnpj)
    {
        $user_externo = $this->findByCpfCnpj($cpf_cnpj);

        if(isset($user_externo))
            if($user_externo->ativo == 0)
                return [
                    'message' => 'Por favor, acesse o email informado no momento do cadastro para verificar sua conta. Caso tenha passado de 24h, se recadastre.',
                    'class' => 'alert-warning',
                    'cpf_cnpj' => $cpf_cnpj
                ];
            else
                return [];
        return [
            'message' => 'Senha incorreta e/ou CPF/CNPJ não encontrado.',
            'class' => 'alert-danger',
            'cpf_cnpj' => $cpf_cnpj
        ];
    }
}