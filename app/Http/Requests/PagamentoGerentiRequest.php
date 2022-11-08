<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PagamentoGerentiRequest extends FormRequest
{
    private $regras;

    protected function prepareForValidation()
    {
        $this->regras = array();

        // Temporário, muitos dados do Gerenti
        $user = auth()->user();

        $this->merge([
            'valor' => apenasNumeros($this->amount),
            'amount_1' => $this->filled('amount_1') ? apenasNumeros($this->amount_1) : null,
            'amount_2' => $this->filled('amount_2') ? apenasNumeros($this->amount_2) : null,
            'parcelas_1' => $this->tipo_pag == 'debit_3ds' ? '1' : $this->parcelas_1,
            'checkoutIframe' => $this->filled('checkoutIframe') ? $this->checkoutIframe : false,
        ]);

        if(($this->tipo_pag == 'combined') && (isset($this->amount_1) && isset($this->amount_2)))
            ($this->amount_1 + $this->amount_2) != $this->valor ? $this->merge(['amount_soma' => '0']) : $this->merge(['amount_soma' => $this->valor]);
        
        if($this->checkoutIframe)
        {
            // via gerenti
            $this->merge([
                'first_name' => 'João',
                'last_name' => 'da Silva',
                'document_type' => strlen($user->cpf_cnpj) == 11 ? 'CPF' : 'CNPJ',
                'document_number' => $user->cpf_cnpj,
                'email' => $user->email,
                'phone_number' => '1134562356',
                'address_street' => 'Rua Alexandre Dumas',
                'address_street_number' => '1711',
                'address_complementary' => '',
                'address_neighborhood' => 'Chacara Santo Antonio',
                'address_city' => 'São Paulo',
                'address_state' => 'SP',
                'address_zipcode' => '04717004',
                'country' => 'BR',
                'items' => json_encode([
                    "name" => "nome do serviço pago",
                    "description" => "descrição do serviço pago",
                    "value" => substr_replace($this->valor, '.', strlen($this->valor) - 2, 0), 
                    "quantity" => 1,
                    "sku" => ""
                ]),
                'dynamic_mcc' => '1999',
                'soft_descriptor' => 'LOJA*TESTE*COMPRA-123',
                'our_number' => '000001946598',
                'document_number_boleto' => '170500000019763',
            ]);

            $this->regras = $this->regrasCheckoutIframe();
        }
    }

    public function rules()
    {
        $regras = [
            'boleto' => 'required',
            'valor' => 'required|regex:/^[0-9]{1,10}$/',
            'tipo_pag' => 'required|in:debit_3ds,credit_3ds,credit' . !$this->checkoutIframe ? '' : ',combined',
            'parcelas_1' => 'required|regex:/^[0-9]{1,2}$/',
            'amount_1' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{1,10}$/',
            'amount_2' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{1,10}$/',
            'parcelas_2' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{1,2}$/',
            'amount_soma' => 'nullable|same:valor',
            'checkoutIframe' => 'boolean',
        ];

        return array_merge($regras, $this->regras);
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

    private function regrasCheckoutIframe()
    {
        return [
            'first_name' => '',
            'last_name' => '',
            'document_type' => '',
            'document_number' => '',
            'email' => '',
            'phone_number' => '',
            'address_street' => '',
            'address_street_number' => '',
            'address_complementary' => '',
            'address_neighborhood' => '',
            'address_city' => '',
            'address_state' => '',
            'address_zipcode' => '',
            'country' => '',
            'items' => '',
            'dynamic_mcc' => '',
            'soft_descriptor' => '',
            'our_number' => '',
            'document_number_boleto' => '',
        ];
    }
}
