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

        $externo = UserExterno::where('cpf_cnpj', $dados['cpf_cnpj'])
            ->where('ativo', 0)
            ->withTrashed()
            ->first();

        if(isset($externo))
            $externo->restore() ? $externo->update($dados) : abort(500);
        else
            $externo = UserExterno::create($dados);

        $body = '<strong>Cadastro no Login Externo do Portal Core-SP realizado com sucesso!</strong>';
        $body .= '<br /><br />';
        $body .= 'Para concluir o processo, basta clicar <a href="'. route('externo.verifica-email', $dados['verify_token']) .'">NESTE LINK</a>.';

        Mail::to($externo->email)->queue(new CadastroUserExternoMail($body));
        event(new ExternoEvent('"' . $externo->cpf_cnpj . '" ("' . $externo->email . '") cadastrou-se na Área do Login Externo.'));
    }

    public function verificaEmail($token)
    {
        $externo = UserExterno::where('verify_token', $token)->first();
        if(!isset($externo))
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

    public function editDados($dados)
    {
        $user_externo = auth()->guard('user_externo')->user();

        if(!isset($dados['nome']) && !isset($dados['email']))
            if(Hash::check($dados['password_atual'], $user_externo->password)) 
            {
                $user_externo->update(['password' => Hash::make($dados['password'])]);
                event(new ExternoEvent('Usuário Externo ' . $user_externo->id . ' ("'. $user_externo->cpf_cnpj .'") alterou a senha com sucesso na Área Restrita.'));
            }else
                return [
                    'message' => 'A senha atual digitada está incorreta!',
                    'class' => 'alert-danger',
                ];
        else
        {
            $user_externo->update([
                'nome' => strtoupper($dados['nome']),
                'email' => $dados['email']
            ]);
            event(new ExternoEvent('Usuário Externo ' . $user_externo->id . ' ("'. $user_externo->cpf_cnpj .'") alterou os dados com sucesso na Área Restrita.'));
        }
    }
}