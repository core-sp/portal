<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\CpfCnpj;

class PagamentoGetnetRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        // Temporário, muitos dados do Gerenti
        $rep = auth()->guard('representante')->user();

        $this->merge([
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
            'amount' => 'required|max:12',
            'tipo_pag' => 'required|in:debit,credit,combined',
            'parcelas_1' => 'required|min:1|numeric',
            'expiration_1' => 'required|date_format:Y-m|after_or_equal:now',
            'security_code_1' => 'required|min:3|max:4|numeric',
            'document_number_1' => ['required', new CpfCnpj],
            'cardholder_name_1' => 'required|max:26',
            'card_number_1' => 'required|numeric|min:13|max:19',
            'cardholder_mobile' => '',
            'tipo_parcelas_1' => '',
            'amount_1' => 'required_if:tipo_pag,combined|max:12',
            // Combinado
            'amount_2' => 'required_if:tipo_pag,combined|max:12',
            'parcelas_2' => 'required_if:tipo_pag,combined|min:1|numeric',
            'expiration_2' => 'required_if:tipo_pag,combined|date_format:Y-m|after_or_equal:now',
            'security_code_2' => 'required_if:tipo_pag,combined|min:3|max:4|numeric',
            'document_number_2' => ['required_if:tipo_pag,combined', new CpfCnpj],
            'cardholder_name_2' => 'required_if:tipo_pag,combined|max:26',
            'card_number_2' => 'required_if:tipo_pag,combined|numeric|min:13|max:19',
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
            'required' => 'Campo obrigatório',
            'required_if' => 'Campo obrigatório',
        ];
    }
}
