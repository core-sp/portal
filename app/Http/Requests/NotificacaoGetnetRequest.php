<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificacaoGetnetRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'payment_type' => mb_strtolower($this->payment_type),
            'status' => mb_strtoupper($this->status),
        ]);

        $this->filled('number_installments') ? 
        $this->merge(['tipo_parcelas' => $this->number_installments == 1 ? 'FULL' : null]) : $this->merge(['tipo_parcelas' => 'FULL']);

        if($this->payment_type == 'debit')
            $this->merge(['number_installments' => '1']);
    }

    public function rules()
    {
        return [
            'payment_type' => '',
            'customer_id' => '',
            'order_id' => '',
            'payment_id' => '',
            'amount' => '',
            'status' => '',
            'number_installments' => '',
            'terminal_nsu' => '',
            'authorization_code' => '',
            'acquirer_transaction_id' => '',
            'authorization_timestamp' => '',
            'brand' => '',
            'description_detail' => '',
            'error_code' => '',
            'tipo_parcelas' => '',
        ];
    }
}
