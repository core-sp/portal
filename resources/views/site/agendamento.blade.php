@extends('layout.app', ['title' => 'Inscrição'])

@section('content')

@php
use App\Http\Controllers\Helpers\AgendamentoControllerHelper;
$horas = AgendamentoControllerHelper::horas();
$servicos = AgendamentoControllerHelper::servicos();
$pessoas = AgendamentoControllerHelper::pessoas();
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
            <h4 class="stronger">Marque seu atendimento no CORE-SP</h4>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mb-4">
      <div class="col conteudo-txt">
		<p>Agende seu atendimento presencial no CORE-SP, com até um mês de antecedência.</p>
        <div class="mt-2">
          <form method="POST" class="inscricaoCurso">
            @csrf
            <h5>Informações de contato</h5>
            <div class="form-row mt-2">
              <div class="col">
                <label for="nome">Nome</label>
                <input type="text"
				  class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
				  name="nome"
				  placeholder="Nome" />
				  @if($errors->has('nome'))
				    <div class="invalid-feedback">
					  {{ $errors->first('nome') }}
					</div>
				  @endif
              </div>
              <div class="col">
                <label for="cpf">CPF</label>
                <input type="text"
                  class="form-control {{ $errors->has('cpf') ? 'is-invalid' : '' }}"
                  name="cpf"
                  placeholder="CPF"
                  />
                  @if($errors->has('cpf'))
				    <div class="invalid-feedback">
					  {{ $errors->first('cpf') }}
					</div>
				  @endif
              </div>
            </div>
            <div class="form-row mt-2">
              <div class="col">
                <label for="email">E-mail</label>
                <input type="text"
                  class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                  name="email"
                  placeholder="E-mail"
                  />
                  @if($errors->has('email'))
				    <div class="invalid-feedback">
					  {{ $errors->first('email') }}
					</div>
				  @endif
              </div>
              <div class="col">
                <label for="celular">Celular</label>
                <input type="text"
                  class="form-control {{ $errors->has('celular') ? 'is-invalid' : '' }}"
                  name="celular"
                  placeholder="Celular"
                  />
                  @if($errors->has('celular'))
				    <div class="invalid-feedback">
					  {{ $errors->first('celular') }}
					</div>
				  @endif
              </div>
            </div>
            <h5 class="mt-2">Informações de agendamento</h5>
            <div class="form-row mt-2">
              <div class="col">
                <label for="dia">Dia</label>
                <input type="text" 
                  class="form-control"
                  id="datepicker"
                  name="dia"
                  placeholder="dd/mm/aaaaa"
                  />
                @if($errors->has('dia'))
                <div class="invalid-feedback">
                  {{ $errors->first('dia') }}
                </div>
                @endif
              </div>
              <div class="col">
                <label for="hora">Horários disponíveis</label>
                <select name="hora" id="hora" class="form-control">
                  @foreach($horas as $hora)
                    <option value="{{ $hora }}">{{ $hora }}</option>
                  @endforeach 
                </select>
                @if($errors->has('hora'))
                <div class="invalid-feedback">
                  {{ $errors->first('hora') }}
                </div>
                @endif
              </div>
              <div class="col">
                <label for="idregional">Regional</label>
                <select name="idregional" class="form-control">
                  @foreach($regionais as $regional)
                    <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                  @endforeach 
                </select>
                @if($errors->has('regional'))
                <div class="invalid-feedback">
                  {{ $errors->first('regional') }}
                </div>
                @endif
              </div>
            </div>
            <div class="form-row mt-2">
              <div class="col">
                <label for="servico">Tipo de Serviço</label>
                <select name="servico" class="form-control">
                  @foreach($servicos as $servico)
                    <option value="{{ $servico }}">{{ $servico }}</option>
                  @endforeach 
                </select>
                @if($errors->has('servico'))
                <div class="invalid-feedback">
                  {{ $errors->first('servico') }}
                </div>
                @endif
              </div>
              <div class="col">
                <label for="pessoa">Para:</label>
                <select name="pessoa" class="form-control">
                  @foreach($pessoas as $pessoa)
                    <option value="{{ $pessoa }}">{{ $pessoa }}</option>
                  @endforeach 
                </select>
                @if($errors->has('pessoa'))
                <div class="invalid-feedback">
                  {{ $errors->first('pessoa') }}
                </div>
                @endif
              </div>
            </div>
          </form>
        </div>
	  </div>
    </div>
  </div>
</section>

@endsection