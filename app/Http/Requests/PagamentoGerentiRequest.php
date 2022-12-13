<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class PagamentoGerentiRequest extends FormRequest
{
    private $service;
    private $regras;
    private $tiposPagamento;

    public function __construct(MediadorServiceInterface $service) 
    {
        $this->service = $service->getService('Pagamento');
    }

    protected function prepareForValidation()
    {
        $this->regras = array();

        $user = auth()->user();

        $this->merge([
            'valor' => apenasNumeros($this->amount),
            'amount_1' => $this->filled('amount_1') ? apenasNumeros($this->amount_1) : null,
            'amount_2' => $this->filled('amount_2') ? apenasNumeros($this->amount_2) : null,
            'parcelas_1' => $this->tipo_pag == 'debit_3ds' ? '1' : $this->parcelas_1,
            'checkoutIframe' => $this->filled('checkoutIframe') ? $this->checkoutIframe : false,
        ]);

        $this->tiposPagamento = $this->checkoutIframe ? $this->service->getTiposPagamentoCheckout() : $this->service->getTiposPagamento();

        if(($this->tipo_pag == 'combined') && (isset($this->amount_1) && isset($this->amount_2)))
            ($this->amount_1 + $this->amount_2) != $this->valor ? $this->merge(['amount_soma' => '0']) : $this->merge(['amount_soma' => $this->valor]);
        
        if($this->checkoutIframe)
        {
            $this->merge([
                'first_name' => substr($user->nome, 0, strpos($user->nome, ' ')),
                'last_name' => substr($user->nome, strpos($user->nome, ' ') + 1),
                'document_type' => strlen(apenasNumeros($user->cpf_cnpj)) == 11 ? 'CPF' : 'CNPJ',
                'document_number' => apenasNumeros($user->cpf_cnpj),
                'email' => $user->email,
                'phone_number' => '',
                'address_street' => '',
                'address_street_number' => '',
                'address_complementary' => '',
                'address_neighborhood' => '',
                'address_city' => '',
                'address_state' => '',
                'address_zipcode' => '',
                'country' => 'BR',
                'items' => json_encode([
                    "name" => "nome do serviço pago em teste",
                    "description" => "descrição do serviço pago em teste",
                    "value" => substr_replace($this->valor, '.', strlen($this->valor) - 2, 0), 
                    "quantity" => 1,
                    "sku" => ""
                ]),
                'our_number' => '',
                'document_number_cobranca' => '',
            ]);

            $this->regras = $this->regrasCheckoutIframe();
        }
    }

    public function rules()
    {
        $regras = [
            'cobranca' => 'required',
            'valor' => 'required|regex:/^[0-9]{1,10}$/',
            'tipo_pag' => 'required|in:' . implode(',', array_keys($this->tiposPagamento)),
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
            'cobranca.required' => 'ID da cobrança é obrigatória',
            'valor.required' => 'Valor total da cobrança é obrigatório',
            'valor.regex' => 'Formato do valor total da cobrança é inválido',
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
            'document_number_cobranca' => '',
        ];
    }
}
