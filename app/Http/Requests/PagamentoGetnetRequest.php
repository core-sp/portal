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
        $rep = auth()->guard('representante')->user();

        $this->merge([
            'amount' => apenasNumeros($this->amount),
            'amount_1' => apenasNumeros($this->amount_1),
            'amount_2' => apenasNumeros($this->amount_2),
            'cardholder_name_1' => mb_strtoupper($this->cardholder_name_1),
            'cardholder_name_2' => mb_strtoupper($this->cardholder_name_2),
            'card_number_1' => apenasNumeros($this->card_number_1),
            'card_number_2' => apenasNumeros($this->card_number_2),
            'document_number_1' => apenasNumeros($this->document_number_1),
            'document_number_2' => apenasNumeros($this->document_number_2),
            'cardholder_mobile' => $this->tipo_pag == 'debit' ? '11999999999' : '',
            'email' => $rep->email,
            'name' => $rep->nome,
            'document_type' => $rep->tipoPessoa() == 'PF' ? 'CPF' : 'CNPJ',
            'document_number' => $rep->cpf_cnpj,
            'device_id' => $rep->getSessionIdPagamento($this->boleto),
            'tipo_parcelas_1' => $this->parcelas_1 == 1 ? 'FULL' : 'INSTALL_NO_INTEREST',
            'tipo_parcelas_2' => $this->parcelas_2 == 1 ? 'FULL' : 'INSTALL_NO_INTEREST',
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
    }

    public function rules()
    {
        return [
            'boleto' => 'required',
            'amount' => 'required|regex:/^[0-9]{1,10}$/',
            'tipo_pag' => 'required|in:debit,credit,combined',
            'parcelas_1' => 'required|regex:/^[0-9]{1,2}$/',
            'expiration_1' => 'required|date_format:Y-m|after_or_equal:' . date('Y-m'),
            'security_code_1' => 'required|regex:/^[0-9]{3,4}$/',
            'document_number_1' => ['required', new CpfCnpj],
            'cardholder_name_1' => 'required|regex:/^[A-z\s]{5,26}$/',
            'card_number_1' => 'required|regex:/^[0-9]{13,19}$/',
            'cardholder_mobile' => '',
            'tipo_parcelas_1' => '',
            'amount_1' => 'required_if:tipo_pag,combined|regex:/^[0-9]{1,10}$/',
            // Combinado
            'amount_2' => 'required_if:tipo_pag,combined|regex:/^[0-9]{1,10}$/',
            'parcelas_2' => 'required_if:tipo_pag,combined|regex:/^[0-9]{1,2}$/',
            'expiration_2' => 'required_if:tipo_pag,combined|date_format:Y-m|after_or_equal:' . date('Y-m'),
            'security_code_2' => 'required_if:tipo_pag,combined|regex:/^[0-9]{3,4}$/',
            'document_number_2' => ['required_if:tipo_pag,combined', new CpfCnpj],
            'cardholder_name_2' => 'required_if:tipo_pag,combined|regex:/^[A-z\s]{5,26}$/',
            'card_number_2' => 'required_if:tipo_pag,combined|regex:/^[0-9]{13,19}$/|different:card_number_1',
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
            'parcelas_1.required' => 'Parcelas é obrigatória',
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
            'parcelas_2.required_if' => 'Parcelas do segundo cartão é obrigatória',
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
        ];
    }
}
