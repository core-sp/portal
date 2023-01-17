<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Contracts\MediadorServiceInterface;

class PagamentoGetnetRequest extends FormRequest
{
    private $service;
    private $regraEci;
    private $tiposPagamento;
    private $user;

    public function __construct(MediadorServiceInterface $service) 
    {
        $this->service = $service;
    }

    protected function prepareForValidation()
    {        
        $this->tiposPagamento = $this->service->getService('Pagamento')->getTiposPagamento();
        
        $this->user = auth()->user();

        if(!$this->filled('cobranca'))
            $this->merge(['cobranca' => '0']);

        if(url()->previous() != route('pagamento.gerenti', $this->cobranca))
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
            'card_number_1' => $this->filled('card_number_1') ? apenasNumeros($this->card_number_1) : null,
            'card_number_2' => $this->filled('card_number_2') ? apenasNumeros($this->card_number_2) : null,
            'document_number_1' => apenasNumeros($this->user->cpf_cnpj),
            'document_number_2' => $this->filled('card_number_2') ? apenasNumeros($this->user->cpf_cnpj) : null,
            'email' => $this->user->email,
            'name' => $this->user->nome,
            'document_type' => $this->user->tipoPessoa() == 'PF' ? 'CPF' : 'CNPJ',
            'document_number' => apenasNumeros($this->user->cpf_cnpj),
            'device_id' => $this->user->getSessionIdPagamento($this->cobranca),
            'parcelas_1' => $this->tipo_pag == 'debit_3ds' ? '1' : $this->parcelas_1,
            'tipo_parcelas_1' => $this->parcelas_1 == 1 ? 'FULL' : 'INSTALL_NO_INTEREST',
            'order_id' => $this->cobranca,
            'customer_id' => $this->user->getCustomerId(),
            'first_name' => substr($this->user->nome, 0, strpos($this->user->nome, ' ')),
            'last_name' => substr($this->user->nome, strpos($this->user->nome, ' ') + 1),
            'phone_number' => '',
            'ba_street' => '',
            'ba_number' => '',
            'ba_complement' => '',
            'ba_district' => '',
            'ba_city' => '',
            'ba_state' => '',
            'ba_country' => 'Brasil',
            'ba_postal_code' => '',
            'sm_identification_code' => '',
            'sm_document_number' => '',
            'sm_address' => '',
            'sm_city' => '',
            'sm_state' => '',
            'sm_postal_code' => '',
            'soft_descriptor' => '',
            'dynamic_mcc' => '',
            'sales_tax' => '0',
            'brand' => $this->filled('brand') ? strtolower($this->brand) : '',
            'tdsver' => $this->filled('tdsver') ? strtolower($this->tdsver) : '',
        ]);

        if(strpos($this->tdsver, '2.') === 0)
            $this->regraEci = $this->brand == 'mastercard' ? '01,02' : '05,06';
        else
            $this->regraEci = $this->brand == 'mastercard' ? '02' : '05';

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
            'cobranca' => 'required',
            'amount' => 'required|regex:/^[0-9]{1,10}$/',
            'tipo_pag' => 'required|in:' . implode(',', array_keys($this->tiposPagamento)),
            'parcelas_1' => 'required|regex:/^[0-9]{1,2}$/',
            'expiration_1' => 'required|date_format:m/Y|after_or_equal:' . date('m/Y'),
            'security_code_1' => 'required|regex:/^[0-9]{3,4}$/',
            'document_number_1' => '',
            'cardholder_name_1' => 'required|regex:/^[A-z\s]{5,26}$/',
            'card_number_1' => 'required_if:tipo_pag,credit,combined|nullable|regex:/^[0-9]{13,19}$/',
            'tipo_parcelas_1' => '',
            // Combinado
            'amount_1' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{1,10}$/',
            'amount_2' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{1,10}$/',
            'parcelas_2' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{1,2}$/',
            'expiration_2' => 'required_if:tipo_pag,combined|nullable|date_format:m/Y|after_or_equal:' . date('m/Y'),
            'security_code_2' => 'required_if:tipo_pag,combined|nullable|regex:/^[0-9]{3,4}$/',
            'document_number_2' => '',
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
            'number_token' => 'required_if:tipo_pag,credit_3ds,debit_3ds',
            'ucaf' => 'required_if:tipo_pag,credit_3ds,debit_3ds',
            'eci' => 'required_if:tipo_pag,credit_3ds,debit_3ds|in:'.$this->regraEci,
            'xid' => 'required_if:tipo_pag,credit_3ds,debit_3ds',
            'tdsver' => '',
            'tdsdsxid' => '',
            'authorization' => 'required_if:tipo_pag,credit_3ds,debit_3ds',
            'brand' => 'required_if:tipo_pag,credit_3ds,debit_3ds|nullable|in:visa,mastercard,elo,amex',
        ];
    }

    public function messages()
    {
        $regexNome =  ' Não pode conter letra acentuada, pontuação e deve possuir entre 5 a 26 caracteres';

        return [
            'cobranca.required' => 'ID da cobrança é obrigatória',
            'amount.required' => 'Valor total da cobrança é obrigatório',
            'amount.regex' => 'Formato do valor total da cobrança é inválido',
            'tipo_pag.required' => 'Forma de pagamento é obrigatória',
            'tipo_pag.in' => 'Tipo de forma de pagamento inválida',
            'parcelas_1.required' => 'Quantidade de parcelas é obrigatória',
            'parcelas_1.regex' => 'Valor das parcelas é inválido',
            'expiration_1.required' => 'Data de expiração é obrigatória',
            'expiration_1.date_format' => 'Formato da data de expiração é inválido',
            'expiration_1.after_or_equal' => 'Data de expiração deve ser igual ou após a data de hoje',
            'security_code_1.required' => 'CVV / CVC é obrigatório',
            'security_code_1.regex' => 'Formato do CVV / CVC é inválido',
            'cardholder_name_1.required' => 'Nome do titular do cartão é obrigatório',
            'cardholder_name_1.regex' => 'Formato do nome do titular do cartão é inválido.' . $regexNome,
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
            'cardholder_name_2.required_if' => 'Nome do titular do segundo cartão é obrigatório',
            'cardholder_name_2.regex' => 'Formato do nome do titular do segundo cartão é inválido.' . $regexNome,
            'card_number_2.required_if' => 'Número do segundo cartão é obrigatório',
            'card_number_2.regex' => 'Formato do número do segundo cartão é inválido',
            'card_number_2.different' => 'Número do segundo cartão deve ser diferente do primeiro cartão',
            // ++++++++++++++
            'amount_soma.same' => 'A soma dos dois valores dos cartões está diferente do valor total',
            'number_token.required_if' => 'Faltou dados da prestadora para autenticação 3DS',
            'eci.required_if' => 'Faltou dados da prestadora para autenticação 3DS',
            'xid.required_if' => 'Faltou dados da prestadora para autenticação 3DS',
            'ucaf.required_if' => 'Faltou dados da prestadora para autenticação 3DS',
            'authorization.required_if' => 'Faltou dados da prestadora para autenticação 3DS',
            'brand.required_if' => 'Faltou dados da prestadora para autenticação 3DS',
            'brand.in' => 'Bandeira do cartão não é aceita',
            'eci.in' => 'Não autorizado a realizar a autenticação.',
        ];
    }

    public function validated()
    {
        $dados = $this->service->getService('Gerenti')->getEnderecoContatoPagamento($this->user);
        $this->merge([
            'phone_number' => $dados['phone_number'],
            'ba_street' => $dados['street'],
            'ba_number' => $dados['street_number'],
            'ba_complement' => $dados['complementary'],
            'ba_district' => $dados['neighborhood'],
            'ba_city' => $dados['city'],
            'ba_state' => $dados['state'],
            'ba_postal_code' => $dados['zipcode'],
        ]);

        return $this->all();
    }
}