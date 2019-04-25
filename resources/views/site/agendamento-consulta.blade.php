@extends('site.layout.app', ['title' => 'Consulta de Agendamentos'])

@section('content')

@php
  use App\Http\Controllers\Helper;
@endphp

<section id="pagina-cabecalho" class="mt-1">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/cursos.png') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Agendamento
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-licitacao">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-3 align-self-center">
            <h4 class="stronger">Consulte os dados de seu agendamento no CORE-SP</h4>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mb-4">
      <div class="col">
        <div class="conteudo-txt">
          <p>Insira o protocolo para conferir as informações de seu agendamento.</p>  
        </div>
        <div class="mt-2">
          <form method="GET" action="/agendamento-consulta/busca" class="consultaAgendamento">
            <div class="form-row">
              <div class="col-md-6">
                <label for="protocolo">Protocolo</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">AGE-</span>
                  </div>
                  <input type="text"
                    class="form-control protocoloInput {{ $errors->has('protocolo') ? 'is-invalid' : '' }}"
                    name="protocolo"
                    id="protocolo"
                    minlength="6"
                    size="6"
                    placeholder="XXXXXX" />
                  <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">Consultar</button>
                  </div>
                </div> 
              </div>
            </div>
          </form>
        </div>
	  </div>
    </div>
  </div>
  @if(isset($busca))
  <div class="container-fluid">
    <div class="row">
      <div class="linha-cinza"></div>
    </div>
  </div>
  <div class="container">
    @if(isset($resultado))
    <div class="row mt-4 mb-2">
      <div class="col mt-2">
        <strong>Agendamento encontrado!</strong><br /><br/>
        <strong>Protocolo:</strong> {{ $resultado->protocolo }}<br />
        <strong>Dia:</strong> {{ Helper::onlyDate($resultado->dia) }}<br />
        <strong>Horário:</strong> {{ $resultado->hora }}<br />
        <strong>Cidade:</strong> {{ $resultado->regional->regional }}<br />
        <strong>Endereço:</strong> {{ $resultado->regional->endereco }}, {{ $resultado->regional->numero }} - {{ $resultado->regional->complemento }}<br />
        <strong>Serviço:</strong> {{ $resultado->tiposervico }}<br /><br />
        --<br />
        @if($resultado->status != 'Cancelado')
        Para cancelar o agendamento, confirme o CPF abaixo e clique em Cancelar:
        @else
        <strong>Agendamento cancelado pelo usuário</strong>
        @endif
      </div>
    </div>
    @if($resultado->status != 'Cancelado')
    <div class="row">
      <div class="col-md-6">
        <form method="POST" class="mt-2">
        @csrf
        {{ method_field('PUT') }}
        <input type="hidden" value="{{ $resultado->idagendamento }}" name="idagendamento" />
        <div class="input-group">
            <input type="text"
            class="form-control cpfInput {{ $errors->has('protocolo') ? 'is-invalid' : '' }}"  
            name="cpf"
            id="cpf"
            minlength="10"
            placeholder="000.000.000-00"
            required
            />
            <div class="input-group-append">
            <button type="submit" class="btn btn-danger">Cancelar</button>
            </div>
        </div>
        </form>
      </div>
    </div>
    @endif
    @else
    Nenhum agendamento encontrado!
    @endif
  </div>
  @endif
</section>

@endsection