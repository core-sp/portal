@extends("certidoes.layout.certidao")

@section("content")

<h1 class="centro">Certidão de Parcelamento</h1>
<p class="texto-certidao">
  <span class="tab">O <b>CORE-SP</b> certifica, atendendo ao requerimento do interessado, para fins de documentar-se, que revendo os assentamentos do Serviço de Registro, consta registrado(a), como pessoa natural, o(a) Sr(a). <b>{{ $dadosRepresentante["nome"] }},</b> sob o nº <b>{{ $dadosRepresentante["registro_core"] }},</b> desde <b>{{ $dadosRepresentante["data_inscricao"] }},</b> inscrito(a) no CPF/MF sob o nº <b>{{ $dadosRepresentante["cpf_cnpj"] }},</b> residente na <b>{{ $endereco }}.</b> O(A) referido(a) Representante Comercial firmou Acordo de Parcelamento referente à(s) anuidade(s) de <b>{{ $dadosParcelamento["parcelamento_ano_inicio"] }},</b> e <b>{{ $dadosParcelamento["parcelamento_ano_fim"] }},</b> em <b>{{ $dadosParcelamento["numero_parcelas"] }}</b> parcelas fixas e mensais, efetuando o primeiro pagamento em <b>{{ $dadosParcelamento["data_primeiro_pagamento"] }},</b> tendo quitado a anuidade de <b>{{ $dadosParcelamento["ano_quitado"] }}.</b>
</p>

@endsection