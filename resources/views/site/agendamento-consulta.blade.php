@extends('site.layout.app', ['title' => 'Consulta de Agendamentos'])

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative">
    <img src="{{ asset('img/banner-interno-agendamento.png') }}" />
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
            <h2 class="stronger">Consulte os dados de seu agendamento no CORE-SP</h2>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    @if(Session::has('message'))
    <div class="container">
      <div class="row mt-2 mb-2">
        <div class="col nopadding">
          <div class="alert {{ Session::get('class') }}">
            {!! Session::get('message') !!}
          </div>
        </div>
      </div>
    </div>
    @endif
    <div class="row mb-4">
      <div class="col">
        <div class="conteudo-txt">
          <p>Insira o protocolo para conferir as informações de seu agendamento.</p>  
        </div>
        <div class="mt-2">
          <form method="GET" action="{{ route('agendamentosite.consulta') }}" class="consultaAgendamento">
            <div class="form-row">
              <div class="col-md-6">
                <label for="protocolo">Protocolo</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">AGE-</span>
                  </div>
                  <input type="text"
                    class="form-control text-uppercase {{ $errors->has('protocolo') ? 'is-invalid' : '' }}"
                    name="protocolo"
                    id="protocolo"
                    minlength="6"
                    maxlength="6"
                    size="6"
                    pattern="[A-Za-z0-9]{6}" title="Somente letras não acentuadas, números e 6 caracteres"
                    required
                    placeholder="XXXXXX" 
                  />
                  @if($errors->has('protocolo'))
                  <div class="invalid-feedback">
                    {{ $errors->first('protocolo') }}
                  </div>
                  @endif
                </div> 
                <div class="float-left mt-3">
                  <button type="submit" class="btn btn-primary">Consultar</button>
                </div>
              </div>
            </div>
          </form>
        </div>
	  </div>
    </div>
  </div>
@if(request()->query('protocolo'))
  <div class="container">
  @if(isset($resultado))
    <div class="row mt-4 mb-2">
      <div class="col mt-2" data-clarity-mask="True">
        <strong>Agendamento encontrado!</strong><br /><br/>
        <strong>Protocolo:</strong> {{ $resultado->protocolo }}<br />
        <strong>Nome:</strong> {{ $resultado->nome }}<br />
        <strong>Dia:</strong> {{ onlyDate($resultado->dia) }}<br />
        <strong>Horário:</strong> {{ $resultado->hora }}<br />
        <strong>Cidade:</strong> {{ $resultado->regional->regional }}<br />
        <strong>Endereço:</strong> {{ $resultado->regional->endereco }}, {{ $resultado->regional->numero }} - {{ $resultado->regional->complemento }}<br />
        <strong>Serviço:</strong> {{ $resultado->tiposervico }}<br /><br />
        <br />
        @if($resultado->status == 'Cancelado')
        <p><strong>Agendamento cancelado</strong></p>
        @endif
      </div>
    </div>
    @if($resultado->isAfter() && !isset($resultado->status))
    <p>Para cancelar o agendamento, confirme o CPF abaixo e clique em Cancelar:</p>
    <div class="row">
      <div class="col-md-6">
        <form method="POST" class="mt-2">
          @csrf
          @method('PUT')
          <div class="input-group">
            <input type="text"
              class="form-control cpfInput {{ $errors->has('cpf') ? 'is-invalid' : '' }}"  
              name="cpf"
              id="cpf"
              required
              placeholder="000.000.000-00"
            />
            @if($errors->has('cpf'))
            <div class="invalid-feedback">
              {{ $errors->first('cpf') }}
            </div>
            @endif
          </div>
          <div class="float-left mt-3">
            <button type="submit" class="btn btn-danger">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
    @endif
  @else
    <div class="row mt-4 mb-2">
      <div class="col">
      <p>Nenhum agendamento encontrado!</p>
      </div>
    </div>
  @endif
  </div>
@endif
</section>

@endsection