<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PagamentoGerentiRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        // Tipos de parcelas: "FULL", "INSTALL_NO_INTEREST", "INSTALL_WITH_INTEREST"

        // Temporário, muitos dados do Gerenti
        $rep = auth()->guard('representante')->user();

        $this->merge([
            'valor' => apenasNumeros($this->amount),
            'amount_1' => $this->filled('amount_1') ? apenasNumeros($this->amount_1) : null,
            'amount_2' => $this->filled('amount_2') ? apenasNumeros($this->amount_2) : null,
            'parcelas_1' => $this->tipo_pag == 'debit' ? '1' : $this->parcelas_1,
        ]);

        if(($this->tipo_pag == 'combined') && (isset($this->amount_1) && isset($this->amount_2)))
            ($this->amount_1 + $this->amount_2) != $this->valor ? $this->merge(['amount_soma' => '0']) : $this->merge(['amount_soma' => $this->valor]);
    }

    public function rules()
    {
        return [
            'boleto' => 'required',
            'valor' => 'required|regex:/^[0-9]{1,10}$/',
            'tipo_pag' => 'required|in:debit,credit,combined',
            'parcelas_1' => 'required|regex:/^[0-9]{1,2}$/',
            'amount_1' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{1,10}$/',
            'amount_2' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{1,10}$/',
            'parcelas_2' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{1,2}$/',
            'amount_soma' => 'nullable|same:valor',
        ];
    }

    public function messages()
    {
        return [
            'boleto.required' => 'ID do boleto é obrigatório',
            'valor.required' => 'Valor total do boleto é obrigatório',
            'valor.regex' => 'Formato do valor total do boleto é inválido',
            'tipo_pag.required' => 'Forma de pagamento é obrigatória',
            'tipo_pag.in' => 'Tipo de forma de pagamento inválida',
            'parcelas_1.required' => 'Quantidade de parcelas é obrigatória',
            'parcelas_1.regex' => 'Valor das parcelas é inválido',
            'amount_1.required_if' => 'Valor parcial do primeiro cartão é obrigatório',
            'amount_1.regex' => 'Formato do valor parcial do primeiro cartão é inválido',
            'amount_2.required_if' => 'Valor parcial do segundo cartão é obrigatório',
            'amount_2.regex' => 'Formato do valor parcial do segundo cartão é inválido',
            'parcelas_2.required_if' => 'Quantidade de parcelas do segundo cartão é obrigatória',
            'parcelas_2.regex' => 'Valor das parcelas do segundo cartão é inválido',
            'amount_soma.same' => 'A soma dos dois valores dos cartões está diferente do valor total',
        ];
    }
}
