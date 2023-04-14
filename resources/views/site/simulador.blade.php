@extends('site.layout.app', ['title' => 'Simulador de Valores'])

@section('description')
  <meta name="description" content="Com informações detalhadas, o Simulador de Valores permite ao Representante Comercial calcular com precisão o valor de seu Registro Inicial." />
@endsection

@section('content')

<section id="pagina-cabecalho">
  <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
    <img src="{{ asset('img/banner-simulador.jpg') }}" />
    <div class="row position-absolute pagina-titulo">
      <div class="container text-center">
        <h1 class="branco text-uppercase">
          Simulador de Valores para Registro Inicial
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
                <select 
                  name="tipoPessoa" 
                  id="tipoPessoa" 
                  class="form-control {{ $errors->has('tipoPessoa') ? 'is-invalid' : '' }}"
                  required
                >
                  @foreach($dados['tipoPessoa'] as $key => $tipo)
                    <option value="{{ $key }}" {{ (Request::input('tipoPessoa') == $key) || (old('tipoPessoa') == $key) ? 'selected' : '' }}>{{ $tipo }}</option>
                  @endforeach
                </select>
                @if($errors->has('tipoPessoa'))
                  <div class="invalid-feedback">
                    {{ $errors->first('tipoPessoa') }}
                  </div>
                @endif
              </div>

              <div class="col-sm">
                <label for="dataInicio">Data de início das atividades *</label>
                <input
                  type="date"
                  name="dataInicio"
                  id="dataInicio" 
                  class="form-control {{ $errors->has('dataInicio') ? 'is-invalid' : '' }}"
                  value="{{ request()->filled('dataInicio') ? Request::input('dataInicio') : date('Y-m-d') }}"
                  autocomplete="off"
                  min="1900-01-01"
                  max="{{ date('Y-m-d') }}"
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

            <div class="form-row mt-2" id="simuladorAddons" style="{{ (Request::input('tipoPessoa') === '1') || (old('tipoPessoa') === '1') ? 'display: flex;' : '' }}">
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
                    class="form-control capitalSocial {{ $errors->has('capitalSocial') ? 'is-invalid' : '' }}"
                    value="{{ request()->filled('capitalSocial') ? Request::input('capitalSocial') : '1,00' }}"
                    maxlength="15"
                  />
                  @if($errors->has('capitalSocial'))
                  <div class="invalid-feedback">
                    {{ $errors->first('capitalSocial') }}
                  </div>
                  @endif
                </div>
              </div>

              <div class="col-sm-6 mb-2-576">
                <div class="form-check">
                  <input
                    type="checkbox"
                    name="filialCheck"
                    id="filialCheck"
                    class="form-check-input {{ $errors->has('filialCheck') ? 'is-invalid' : '' }}"
                    value="on"
                    {{ Request::input('filialCheck') == 'on' ? 'checked' : '' }}
                  />
                  @if($errors->has('filialCheck'))
                  <div class="invalid-feedback">
                    {{ $errors->first('filialCheck') }}
                  </div>
                  @endif
                  <label for="filialCheck">Filial</label>
                </div>

                <select 
                  name="filial" 
                  id="filial" 
                  class="form-control {{ $errors->has('filial') ? 'is-invalid' : '' }}" 
                  {{ Request::input('filialCheck') == 'on' ? '' : 'disabled' }}
                >
                  <option value="50" {{ Request::input('filial') == 50 ? 'selected' : '' }}></option>
                  @foreach($dados['cores'] as $key => $filial)
                  <option value="{{ $key }}" {{ Request::input('filial') == $key ? 'selected' : '' }}>{{ $filial }}</option>
                  @endforeach
                </select>
                @if($errors->has('filial'))
                <div class="invalid-feedback">
                  {{ $errors->first('filial') }}
                </div>
                @endif
              </div>

              <div class="col-6 mt-2">
                <div class="form-check">
                  <input
                    type="checkbox"
                    name="empresaIndividual"
                    id="empresaIndividual"
                    class="form-check-input {{ $errors->has('empresaIndividual') ? 'is-invalid' : '' }}"
                    value="on"
                    {{ Request::input('empresaIndividual') == 'on' ? 'checked' : '' }}
                  />
                  @if($errors->has('empresaIndividual'))
                  <div class="invalid-feedback">
                    {{ $errors->first('empresaIndividual') }}
                  </div>
                  @endif
                  <label for="empresaIndividual">Empresa Individual</label>
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
                  Simular {{ request()->filled('dataInicio') ? ' novamente' : '' }}
                </button>
                <div id="loadingSimulador"><img src="{{ asset('img/ajax-loader.gif') }}" alt="Loading"></div>
              </div>
            </div>

          </form>
        </div>

        <!-- RESULTADO ******************************************************************************************************************************** -->
        <div id="simuladorTxt">
        @if(isset($total) || isset($extrato) || isset($taxas))
          <div class="row nomargin mt-4">
            <h4 class="mb-1">Pessoa {{ $dados['tipoPessoa'][Request::input('tipoPessoa')] }} {{ Request::input('filial') && (Request::input('filial') !== '50') ? ' (' . $dados['cores'][Request::input('filial')] . ')' : '' }}</h4>
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
                <!-- <tr class="blank-row"><td colspan="2"></td></tr> -->
                {{--@foreach($taxas as $cobranca)--}}
                  <!-- <tr>
                    <td>{{--{{ utf8_encode($cobranca['TAX_DESCRICAO']) }}--}}</td>
                    <td><span class="nowrap">{{--{{ 'R$ ' . str_replace('.', ',', $cobranca['TAX_VALOR']) }}--}}</span></td>
                  </tr> -->
                {{--@endforeach--}}
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
                  <!-- <tr class="blank-row"><td colspan="2"></td></tr> -->
                  {{--@foreach($rtTaxas as $cobranca)--}}
                    <!-- <tr>
                      <td>{{--{{ utf8_encode($cobranca['TAX_DESCRICAO']) }}--}}</td>
                      <td><span class="nowrap">{{--{{ 'R$ ' . str_replace('.', ',', $cobranca['TAX_VALOR']) }}--}}</span></td>
                    </tr> -->
                  {{--@endforeach--}}
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
        @if(isset($texto))
          <hr>
          <div class="textoSimulador">
            {!! $texto !!}
          </div>
        @endif
        @if(request()->filled('tipoPessoa'))
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