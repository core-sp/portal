<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;

class PagamentoGetnetRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        // Tipos de parcelas: "FULL", "INSTALL_NO_INTEREST", "INSTALL_WITH_INTEREST"
        // 'cardholder_mobile' quando debito e visa
        
        // Temporário, muitos dados do Gerenti
        $user = auth()->user();

        if(url()->previous() != route('pagamento.gerenti', $this->boleto))
        {
            $this->replace([]);
            return;
        }

        $this->merge([
            'amount' => apenasNumeros($this->amount),
            'amount_1' => $this->filled('amount_1') ? apenasNumeros($this->amount_1) : null,
            'amount_2' => $this->filled('amount_2') ? apenasNumeros($this->amount_2) : null,
            'cardholder_name_1' => mb_strtoupper($this->cardholder_name_1),
            'cardholder_name_2' => $this->filled('cardholder_name_2') ? mb_strtoupper($this->cardholder_name_2) : null,
            'card_number_1' => apenasNumeros($this->card_number_1),
            'card_number_2' => $this->filled('card_number_2') ? apenasNumeros($this->card_number_2) : null,
            'document_number_1' => apenasNumeros($this->document_number_1),
            'document_number_2' => $this->filled('document_number_2') ? apenasNumeros($this->document_number_2) : null,
            'cardholder_mobile' => '',
            'email' => $user->email,
            'name' => $user->nome,
            'document_type' => $user->tipoPessoa() == 'PF' ? 'CPF' : 'CNPJ',
            'document_number' => $user->cpf_cnpj,
            'device_id' => $user->getSessionIdPagamento($this->boleto),
            'parcelas_1' => $this->tipo_pag == 'debit_3ds' ? '1' : $this->parcelas_1,
            'tipo_parcelas_1' => $this->parcelas_1 == 1 ? 'FULL' : 'INSTALL_NO_INTEREST',
            'order_id' => '6d2e4380-d8a3-4ccb-9138-c289182818a3',
            'customer_id' => 'customer_21081826',
            'first_name' => 'Teste',
            'last_name' => 'do Teste',
            'phone_number' => '(11) 95632-45693',
            'sm_identification_code' => '9058344',
            'sm_document_number' => '60.746.179/0001-52',
            'sm_address' => 'Torre Negra 44',
            'sm_city' => 'Cidade',
            'sm_state' => 'SP',
            'sm_postal_code' => '90520000',
            'soft_descriptor' => 'COR*SPREPRESEN',
            'dynamic_mcc' => '1799',
            'sales_tax' => '0',
            'ba_street' => 'Av. Brasil',
            'ba_number' => '1000',
            'ba_complement' => '',
            'ba_district' => 'São Geraldo',
            'ba_city' => 'São Paulo',
            'ba_state' => 'SP',
            'ba_country' => 'Brasil',
            'ba_postal_code' => '90230060',
        ]);

        if($this->filled('parcelas_2'))
            $this->merge(['tipo_parcelas_2' => $this->parcelas_2 == 1 ? 'FULL' : 'INSTALL_NO_INTEREST']);
        else
            $this->merge(['tipo_parcelas_2' => null]);

        if(($this->tipo_pag == 'combined') && (isset($this->amount_1) && isset($this->amount_2)))
            ($this->amount_1 + $this->amount_2) != $this->amount ? $this->merge(['amount_soma' => '0']) : $this->merge(['amount_soma' => $this->amount]);
    }

    public function rules()
    {
        return [
            'boleto' => 'required',
            'amount' => 'required|regex:/^[0-9]{1,10}$/',
            'tipo_pag' => 'required|in:debit_3ds,credit,credit_3ds,combined',
            'parcelas_1' => 'required|regex:/^[0-9]{1,2}$/',
            'expiration_1' => 'required|date_format:Y-m|after_or_equal:' . date('Y-m'),
            'security_code_1' => 'required|regex:/^[0-9]{3,4}$/',
            'document_number_1' => ['required', new CpfCnpj],
            'cardholder_name_1' => 'required|regex:/^[A-z\s]{5,26}$/',
            'card_number_1' => 'required|regex:/^[0-9]{13,19}$/',
            'cardholder_mobile' => '',
            'tipo_parcelas_1' => '',
            // Combinado
            'amount_1' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{1,10}$/',
            'amount_2' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{1,10}$/',
            'parcelas_2' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{1,2}$/',
            'expiration_2' => 'required_if:tipo_pag,combined|nullable|date_format:Y-m|after_or_equal:' . date('Y-m'),
            'security_code_2' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{3,4}$/',
            'document_number_2' => ['required_if:tipo_pag,combined', new CpfCnpj, 'nullable'],
            'cardholder_name_2' => 'required_if:tipo_pag,combined|nullable|regex:/^[A-z\s]{5,26}$/',
            'card_number_2' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{13,19}$/|different:card_number_1',
            'amount_soma' => 'nullable|same:amount',
            'tipo_parcelas_2' => '',
            // ++++++++++++++
            'order_id' => '',
            'customer_id' => '',
            'first_name' => '',
            'last_name' => '',
            'name' => '',
            'email' => '',
            'document_type' => '',
            'document_number' => '',
            'phone_number' => '',
            'device_id' => '',
            'sm_identification_code' => '',
            'sm_document_number' => '',
            'sm_address' => '',
            'sm_city' => '',
            'sm_state' => '',
            'sm_postal_code' => '',
            'soft_descriptor' => '',
            'dynamic_mcc' => '',
            'sales_tax' => '',
            'ba_street' => '',
            'ba_number' => '',
            'ba_complement' => '',
            'ba_district' => '',
            'ba_city' => '',
            'ba_state' => '',
            'ba_country' => '',
            'ba_postal_code' => '',
        ];
    }

    public function messages()
    {
        return [
            'boleto.required' => 'ID do boleto é obrigatório',
            'amount.required' => 'Valor total do boleto é obrigatório',
            'amount.regex' => 'Formato do valor total do boleto é inválido',
            'tipo_pag.required' => 'Forma de pagamento é obrigatória',
            'tipo_pag.in' => 'Tipo de forma de pagamento inválida',
            'parcelas_1.required' => 'Quantidade de parcelas é obrigatória',
            'parcelas_1.regex' => 'Valor das parcelas é inválido',
            'expiration_1.required' => 'Data de expiração é obrigatória',
            'expiration_1.date_format' => 'Formato da data de expiração é inválido',
            'expiration_1.after_or_equal' => 'Data de expiração deve ser igual ou após a data de hoje',
            'security_code_1.required' => 'CVV / CVC é obrigatório',
            'security_code_1.regex' => 'Formato do CVV / CVC é inválido',
            'document_number_1.required' => 'Número do documento é obrigatório',
            'cardholder_name_1.required' => 'Nome do titular do cartão é obrigatório',
            'cardholder_name_1.regex' => 'Formato do nome do titular do cartão é inválido',
            'card_number_1.required' => 'Número do cartão é obrigatório',
            'card_number_1.regex' => 'Formato do número do cartão é inválido',
            // Combinado
            'amount_1.required_if' => 'Valor parcial do primeiro cartão é obrigatório',
            'amount_1.regex' => 'Formato do valor parcial do primeiro cartão é inválido',
            'amount_2.required_if' => 'Valor parcial do segundo cartão é obrigatório',
            'amount_2.regex' => 'Formato do valor parcial do segundo cartão é inválido',
            'parcelas_2.required_if' => 'Quantidade de parcelas do segundo cartão é obrigatória',
            'parcelas_2.regex' => 'Valor das parcelas do segundo cartão é inválido',
            'expiration_2.required_if' => 'Data de expiração do segundo cartão é obrigatória',
            'expiration_2.date_format' => 'Formato da data de expiração do segundo cartão é inválido',
            'expiration_2.after_or_equal' => 'Data de expiração do segundo cartão deve ser igual ou após a data de hoje',
            'security_code_2.required_if' => 'CVV / CVC do segundo cartão é obrigatório',
            'security_code_2.regex' => 'Formato do CVV / CVC do segundo cartão é inválido',
            'document_number_2.required_if' => 'Número do documento do segundo cartão é obrigatório',
            'cardholder_name_2.required_if' => 'Nome do titular do segundo cartão é obrigatório',
            'cardholder_name_2.regex' => 'Formato do nome do titular do segundo cartão é inválido',
            'card_number_2.required_if' => 'Número do segundo cartão é obrigatório',
            'card_number_2.regex' => 'Formato do número do segundo cartão é inválido',
            'card_number_2.different' => 'Número do segundo cartão deve ser diferente do primeiro cartão',
            'amount_soma.same' => 'A soma dos dois valores dos cartões está diferente do valor total',
        ];
    }
}
