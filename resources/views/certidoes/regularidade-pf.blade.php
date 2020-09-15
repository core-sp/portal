@extends("certidoes.layout.certidao")

@section("content")

<h1 class="centro">Certidão de Regularidade</h1>
<p class="texto-certidao">
  <span class="tab">O <b>CORE-SP</b> certifica, atendendo ao requerimento do interessado, para fins de documentar-se, que revendo os assentamentos do Serviço de Registro, consta registrado(a), como pessoa natural, o(a) Sr(a). <b>{{ $dadosRepresentante["nome"] }},</b> sob o nº <b>{{ $dadosRepresentante["registro_core"] }},</b> desde <b>{{ $dadosRepresentante["data_inscricao"] }},</b> inscrito(a) no CPF/MF sob o nº <b>{{ $dadosRepresentante["cpf_cnpj"] }},</b> residente na <b>{{ $endereco }}.</b> O(A) referido(a) Representante Comercial pagou contribuições a este Conselho Regional até o mês de <b>{{ $data["mes"] }}</b> de <b>{{ $data["ano"] }}.</b>
</p>

@endsection