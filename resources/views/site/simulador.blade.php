@extends('site.layout.app', ['title' => 'Simulador de Valores'])

@section('description')
  <meta name="description" content="Com informações detalhadas, o Simulador de Valores permite ao Representante Comercial calcular com precisão o valor de seu Registro Inicial." />
@endsection

@section('content')

@php
    use \App\Http\Controllers\Helpers\SimuladorControllerHelper;
@endphp

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-simulador.jpg') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Simulador de Valores para Registro inicial
        </h1>
      </div>
    </div>
  </div>
</section>

<section id="pagina-busca">
  <div class="container">
    <div class="row" id="conteudo-principal">
      <div class="col">
        <div class="row nomargin">
          <div class="flex-one pr-4 align-self-center">
            <h2 class="stronger">Representante já pode consultar, com mais facilidade, valores para registro inicial!</h2>
          </div>
          <div class="align-self-center">
            <a href="/" class="btn-voltar">Voltar</a>
          </div>
        </div>
      </div>
    </div>
    <div class="linha-lg"></div>
    <div class="row mt-2" id="conteudo-principal">
      <div class="col-lg-8">
        <div class="row nomargin">
          <form method="post" class="w-100 simulador">
            @csrf
            <div class="form-row">
              <div class="col-sm mb-2-576">
                <label for="tipoPessoa">Tipo de Pessoa</label>
                <select name="tipoPessoa" id="tipoPessoa" class="form-control">
                  @foreach(SimuladorControllerHelper::tipoPessoa() as $key => $tipo)
                    <option value="{{ $key }}" {{ Request::input('tipoPessoa') == $key || old('tipoPessoa') == $key ? 'selected' : '' }}>{{ $tipo }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-sm">
                <label for="dataInicio">Data de início das atividades *</label>
                <input
                  type="text"
                  name="dataInicio"
                  id="dataInicio" 
                  class="form-control dataInput {{ $errors->has('dataInicio') ? 'is-invalid' : '' }}"
                  @if(Request::input('dataInicio'))
                    value="{{ Request::input('dataInicio') }}"
                  @elseif($errors->has('dataInicio'))
                    value=""
                  @else
                    value="{{ date('d\/m\/Y') }}"
                  @endif
                  placeholder="dd/mm/aaaa"
                  autocomplete="off"
                  readonly
                  required
                />
                @if($errors->has('dataInicio'))
                  <div class="invalid-feedback">
                    {{ $errors->first('dataInicio') }}
                  </div>
                @endif
              </div>
            </div>
            <div class="form-row mt-2" id="simuladorAddons" style="{{ Request::input('tipoPessoa') === '1' || old('tipoPessoa') === '1' ? 'display: flex;' : '' }}">
              <div class="col-sm-6 mb-2-576">
                <label for="capitalSocial">Capital Social</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                      <span class="input-group-text">R$</span>
                  </div>
                  <input
                    type="text"
                    id="capitalSocial"
                    name="capitalSocial"
                    class="form-control capitalSocial"
                    value="{{ Request::input('capitalSocial') ? Request::input('capitalSocial') : '1,00' }}"
                    maxlength="15"
                  />
                </div>
              </div>
              <div class="col-sm-6 mb-2-576">
                <div class="form-check">
                  <input
                    type="checkbox"
                    name="filialCheck"
                    id="filialCheck"
                    class="form-check-input"
                    {{ Request::input('filialCheck') == 'on' ? 'checked' : '' }}
                  />
                  <label for="form-check-label" for="filialCheck">Filial</label>
                </div>
                <select name="filial" id="filial" class="form-control" {{ Request::input('filialCheck') == 'on' ? '' : 'disabled' }}>
                  <option value="50" {{ Request::input('filial') == 50 ? 'selected' : '' }}></option>
                  @foreach(SimuladorControllerHelper::listaCores() as $key => $filial)
                    <option value="{{ $key }}" {{ Request::input('filial') == $key ? 'selected' : '' }}>{{ $filial }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-6 mt-2">
                <div class="form-check">
                  <input
                    type="checkbox"
                    name="empresaIndividual"
                    id="empresaIndividual"
                    class="form-check-input"
                    {{ Request::input('empresaIndividual') == 'on' ? 'checked' : '' }}
                  />
                  <label for="form-check-label" for="empresaIndividual">Empresa Individual</label>
                </div>
              </div>
            </div>
            <div class="form-row mt-2">
              <div class="col">
                <button
                  type="submit"
                  class="btn btn-primary"
                  id="submitSimulador"
                  onClick="gtag('event', 'calcular', {
                    'event_category': 'simulador',
                    'event_label': 'Simulador de Valores'
                  });"
                >
                  Simular {{ Request::input('dataInicio') ? ' novamente' : '' }}
                </button>
                <div id="loadingSimulador"><img src="{{ asset('img/ajax-loader.gif') }}" alt="Loading"></div>
              </div>
            </div>
          </form>
        </div>
        <div id="simuladorTxt">
        @if(isset($total) || isset($extrato) || isset($taxas))
          <div class="row nomargin mt-4">
            <h4 class="mb-1">Pessoa {{ SimuladorControllerHelper::tipoPessoa()[Request::input('tipoPessoa')] }} {{ Request::input('filial') && Request::input('filial') !== '50' ? ' (' . SimuladorControllerHelper::listaCores()[Request::input('filial')] . ')' : '' }}</h4>
            <table class="table table-sm table-hover mb-0 tableSimulador">
              <thead>
                <tr>
                  <th class="border-3">Descrição</th>
                  <th class="border-3">Valor</th>
                </tr>
              </thead>
              <tbody>
                @foreach($extrato as $cobranca)
                  <tr>
                    <td>{{ $cobranca['DESCRICAO'] }}</td>
                    <td><span class="nowrap">{{ 'R$ ' . str_replace('.', ',', $cobranca['VALOR_TOTAL']) }}</span></td>
                  </tr>
                @endforeach
                <tr class="blank-row"><td colspan="2"></td></tr>
                @foreach($taxas as $cobranca)
                  <tr>
                    <td>{{ utf8_encode($cobranca['TAX_DESCRICAO']) }}</td>
                    <td><span class="nowrap">{{ 'R$ ' . str_replace('.', ',', $cobranca['TAX_VALOR']) }}</span></td>
                  </tr>
                @endforeach
                <tr class="blank-row"><td colspan="2"></td></tr>
                <tr>
                  <td class="text-right pt-2"><strong>Total:</strong></td>
                  <td class="pt-2"><span class="nowrap">R$ {{ $total }}</span></td>
                </tr>
              </tbody>
            </table>
            @if(isset($rt))
              <h4 class="mb-1">Pessoa Física RT</h4>
              <table class="table table-sm table-hover mb-0 tableSimulador">
                <thead>
                  <th class="border-3">Descrição</th>
                  <th class="border-3">Valor</th>
                </thead>
                <tbody>
                  @foreach($rt as $cobranca)
                    <tr>
                      <td>{{ $cobranca['DESCRICAO'] }}</td>
                      <td><span class="nowrap">{{ 'R$ ' . str_replace('.', ',', $cobranca['VALOR_TOTAL']) }}</span></td>
                    </tr>
                  @endforeach
                  <tr class="blank-row"><td colspan="2"></td></tr>
                  @foreach($rtTaxas as $cobranca)
                    <tr>
                      <td>{{ utf8_encode($cobranca['TAX_DESCRICAO']) }}</td>
                      <td><span class="nowrap">{{ 'R$ ' . str_replace('.', ',', $cobranca['TAX_VALOR']) }}</span></td>
                    </tr>
                  @endforeach
                  <tr class="blank-row"><td colspan="2"></td></tr>
                  <tr>
                    <td class="text-right pt-2"><strong>Total:</strong></td>
                    <td class="pt-2"><span class="nowrap">R$ {{ str_replace('.', ',', $rtTotal) }}</span></td>
                  </tr>
                </tbody>
              </table>
              <h4 class="mt-2"><span class="light">Total geral:</span> R$ {{ $totalGeral  }}</h4>
            @endif
          </div>
          <hr>
          <div class="row nomargin">
            <small><i>* Os valores calculados são de acordo com as informações preenchidas</i></small>
          </div>
          @endif
          @if(request('tipoPessoa') === '2')
            <hr>
            <div class="textoSimulador">
              {!! SimuladorControllerHelper::textoPessoaFisica() !!}
            </div>
          @elseif(request('tipoPessoa') === '5')
            <hr>
            <div class="textoSimulador">
              {!! SimuladorControllerHelper::textoPessoaFisicaRt() !!}
            </div>
          @elseif(request('tipoPessoa') === '1' && request('empresaIndividual') !== 'on')
            <hr>
            <div class="textoSimulador">
              {!! SimuladorControllerHelper::textoPessoaJuridica() !!}
            </div>
          @elseif(request('tipoPessoa') === '1' && request('empresaIndividual') === 'on')
            <hr>
            <div class="textoSimulador">
              {!! SimuladorControllerHelper::textoPessoaJuridicaEmpresaIndividual() !!}
            </div>
          @endif
          @if(request('tipoPessoa') !== null)
            <hr>
            <p class="light">Simulação emitida em: <strong>{{ date('d\/m\/Y') }}</strong></p>
          @endif
        </div>
      </div>
      <div class="col-lg-4">
        @include('site.inc.content-sidebar')
      </div>
    </div>
  </div>
</section>

@endsection