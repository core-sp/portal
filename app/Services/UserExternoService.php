<?php

namespace App\Services;

use App\UserExterno;
use App\Contabil;
use App\Contracts\UserExternoServiceInterface;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\CadastroUserExternoMail;

class UserExternoService implements UserExternoServiceInterface {

    private function formatDados($dados)
    {
        $tipo_conta = $dados['tipo_conta'];
        $dados['password'] = Hash::make($dados['password']);
        $dados['aceite'] = true;

        unset($dados['tipo_conta']);
        unset($dados['password_confirmation']);

        if($tipo_conta == 'contabil'){
            $dados['cnpj'] = $dados['cpf_cnpj'];
            $dados['ativo'] = 0;
            unset($dados['cpf_cnpj']);
        }

        return $dados;
    }

    public function getDefinicoes($tipo)
    {
        return [
            'tipo' => $tipo,
            'campo' => $tipo == 'contabil' ? 'cnpj' : 'cpf_cnpj',
            'classe' => $tipo == 'contabil' ? Contabil::class : UserExterno::class,
            'rotulo' => $tipo == 'contabil' ? 'Contabilidade' : 'Usuário Externo',
            'tabela' => $tipo == 'contabil' ? 'contabeis' : 'users_externo',
        ];
    }

    public function save($dados)
    {
        $tipo_conta = $this->getDefinicoes($dados['tipo_conta']);
        $dados = $this->formatDados($dados);

        $emails = $tipo_conta['classe']::where('email', $dados['email'])
        ->where($tipo_conta['campo'], '!=', $dados[$tipo_conta['campo']])
        ->whereNotNull('ativo')
        ->whereNotNull('aceite')
        ->withTrashed()
        ->count();

        if($emails >= 2)
            return [
                'erro' => 'Este email já alcançou o limite de cadastro, por favor insira outro.',
                'class' => 'alert-danger'
            ];

        $externo = $tipo_conta['classe']::where($tipo_conta['campo'], $dados[$tipo_conta['campo']])
        ->where(function($query) {
            $query->where('ativo', 0)
            ->orWhereNull('ativo');
        })
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
            $externo = $tipo_conta['classe']::create($dados);

        Mail::to($externo->email)->queue(new CadastroUserExternoMail($tipo_conta['tipo'], $dados['verify_token']));
        event(new ExternoEvent('"' . formataCpfCnpj($externo[$tipo_conta['campo']]) . '" ("' . $externo->email . '") cadastrou-se na Área do Login Externo como '.$tipo_conta['rotulo'].'.'));
    }

    public function verificaEmail($token, $tipo)
    {
        $tipo_conta = $this->getDefinicoes($tipo);
        $externo = $tipo_conta['classe']::where('verify_token', $token)
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
        event(new ExternoEvent($tipo_conta['rotulo'].' ' . $externo->id . ' ("'. formataCpfCnpj($externo[$tipo_conta['campo']]) .'") verificou o email após o cadastro.'));
    }

    public function editDados($dados, $externo, $tipo)
    {
        $tipo_conta = $this->getDefinicoes($tipo);

        if(!isset($dados['nome']) && !isset($dados['email']))
            if(Hash::check($dados['password_atual'], $externo->password)) 
            {
                $externo->update(['password' => Hash::make($dados['password'])]);

                Mail::to($externo->email)->queue(new CadastroUserExternoMail($tipo, null, $externo));
                event(new ExternoEvent($tipo_conta['rotulo'].' ' . $externo->id . ' ("'. formataCpfCnpj($externo[$tipo_conta['campo']]) .'") alterou a senha com sucesso na Área Restrita após logon.'));
            }else
                return [
                    'message' => 'A senha atual digitada está incorreta!',
                    'class' => 'alert-danger',
                ];
        else
        {
            if($dados['email'] != $externo->email)
            {
                $emails = $tipo_conta['classe']::where('email', $dados['email'])
                ->whereNotNull('ativo')
                ->whereNotNull('aceite')
                ->withTrashed()
                ->count();
                if($emails >= 2)
                    return [
                        'message' => 'Este email já alcançou o limite de cadastro, por favor insira outro.',
                        'class' => 'alert-danger',
                    ];
            }

            $externo->update($dados);

            Mail::to($externo->email)->queue(new CadastroUserExternoMail($tipo, null, $externo));
            event(new ExternoEvent($tipo_conta['rotulo'].' ' . $externo->id . ' ("'. formataCpfCnpj($externo[$tipo_conta['campo']]) .'") alterou os dados com sucesso na Área Restrita após logon.'));
        }
    }

    public function findByCpfCnpj($tipo, $cpf_cnpj)
    {
        $cpf_cnpj = apenasNumeros($cpf_cnpj);
        $tipo_conta = $this->getDefinicoes($tipo);

        return $tipo_conta['classe']::where($tipo_conta['campo'], $cpf_cnpj)->first();
    }

    public function verificaSeAtivo($tipo, $cpf_cnpj)
    {
        $user_externo = $this->findByCpfCnpj($tipo, $cpf_cnpj);

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