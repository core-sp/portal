@extends("certidoes.layout.certidao")

@section("content")

<h1 class="centro">Certidão de Regularidade</h1>
<p class="texto-certidao">
  <span class="tab">O <b>CORE-SP</b> certifica, atendendo ao requerimento do(a) interessado(a), para fins de documentar-se, que, revendo os assentamentos do Serviço de Registro, deles consta registrada como <b>{{ $dadosRepresentante["tipo_empresa"] }} - {{ $dadosRepresentante["nome"] }},</b> sob o nº <b>{{ $dadosRepresentante["registro_core"] }},</b> desde de <b>{{ $dadosRepresentante["data_inscricao"] }},</b> inscrita no CNPJ sob o nº <b>{{ $dadosRepresentante["cpf_cnpj"] }},</b> com sede na <b>{{ $endereco }},</b> tendo como Responsável Técnico o(a) sr.(a) <b>{{ $dadosRepresentante["resp_tecnico"] }},</b> registrado(a) sob o número <b>{{ $dadosRepresentante["resp_tecnico_registro_core"] }}.</b> A mencionada empresa pagou contribuições a este Conselho Regional até o mês de <b>{{ $data["mes"] }}</b> de <b>{{ $data["ano"] }}.</b>
</p>

@endsection