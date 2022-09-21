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
                'erro' => 'Este email já está cadastrado em duas contas, por favor insira outro.',
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
                    'erro' => 'Esta conta já solicitou o cadastro. Verifique seu email para ativar.',
                    'class' => 'alert-danger'
                ];
            if($externo->trashed())
                $externo->restore();
            $externo->update($dados);
        }
        else
            $externo = UserExterno::create($dados);

        $body = '<strong>Cadastro no Login Externo do Portal Core-SP realizado com sucesso!</strong>';
        $body .= '<br /><br />';
        $body .= 'Você deve ativar sua conta em até 24h, caso contrário deve se recadastrar.';
        $body .= '<br /><br />';
        $body .= 'Para concluir o processo, basta clicar <a href="'. route('externo.verifica-email', $dados['verify_token']) .'">NESTE LINK</a>.';

        Mail::to($externo->email)->queue(new CadastroUserExternoMail($body));
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
                event(new ExternoEvent('Usuário Externo ' . $externo->id . ' ("'. $externo->cpf_cnpj .'") alterou a senha com sucesso na Área Restrita.'));
            }else
                return [
                    'message' => 'A senha atual digitada está incorreta!',
                    'class' => 'alert-danger',
                ];
        else
        {
            $externo->update([
                'nome' => mb_strtoupper($dados['nome'], 'UTF-8'),
                'email' => $dados['email']
            ]);
            event(new ExternoEvent('Usuário Externo ' . $externo->id . ' ("'. $externo->cpf_cnpj .'") alterou os dados com sucesso na Área Restrita.'));
        }
    }

    public function verificaSeAtivo($cpf_cnpj)
    {
        $cpf_cnpj = apenasNumeros($cpf_cnpj);
        $user_externo = UserExterno::where('cpf_cnpj', $cpf_cnpj)->first();

        if(isset($user_externo))
            if($user_externo->ativo == 0)
                return [
                    'message' => 'Por favor, acesse o email informado no momento do cadastro para verificar sua conta.',
                    'class' => 'alert-warning',
                    'cpf_cnpj' => $cpf_cnpj
                ];
            else
                return [];
        return [
            'message' => 'CPF/CNPJ não encontrado.',
            'class' => 'alert-danger',
            'cpf_cnpj' => $cpf_cnpj
        ];
    }
}