<?php

namespace App\Repositories;

use App\PreCadastro;

class PreCadastroRepository {

    public function getToTable()
    {
        return PreCadastro::orderBy('id','DESC')
            ->paginate(10);
    }

    public function getById($id)
    {
        return PreCadastro::findOrFail($id);
    }

    public function store($request)
    {
        return PreCadastro::create([
            'nome' => $request->nome,
            'cpf'=> $request->cpf,
            'tipoDocumento' => $request->tipoDocumento,
            'numeroDocumento' => $request->numeroDocumento,
            'orgaoEmissorDocumento' => $request->orgaoEmissor,
            'dataEmissaoDocumento' => formataDataUTC($request->dataExpedicao),
            'dataNascimento' => formataDataUTC($request->dataNascimento),
            'estadoCivil' => $request->estadoCivil,
            'sexo' => $request->sexo,
            'naturalizado' => $request->naturalizado,
            'nacionalidade' => $request->nacionalidade,
            'nomeMae' => $request->nomeMae,
            'nomePai' => $request->nomePai,
            'email' => $request->email,
            'celular' => $request->celular,
            'telefoneFixo' => $request->telefoneFixo,
            'segmento' => $request->segmento,
            'cep' => $request->cep,
            'bairro' => $request->bairro,
            'logradouro' => $request->logradouro,
            'numero' => $request->numero,
            'complemento' => $request->complemento,
            'estado' => $request->estado,
            'municipio' => $request->municipio,
            'tipo' => PreCadastro::TIPO_PRE_CADASTRO_PF_AUTONOMA,
            'status' => PreCadastro::STATUS_PENDENTE
        ]);
    }

    public function updateStatus($id, $status, $motivo = null) 
    {
        $preCadastro = $this->getById($id);

        $preCadastro->status = $status;

        if($motivo != null) {
            $preCadastro->motivo = $motivo;
        }

        $preCadastro->update();

        return $preCadastro;
    }
}