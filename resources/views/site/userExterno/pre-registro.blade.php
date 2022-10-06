@extends('site.userExterno.app')

@section('content-user-externo')

@if(Session::has('message'))
<div class="d-block w-100 alert alert-dismissible {{ Session::get('class') }}">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    {!! Session::get('message') !!}
</div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Solicitação de registro para ser <strong>Representante Comercial</strong></h4>
        <div class="linha-lg-mini mb-2"></div>
            <div class="list-group w-100">
                <div class="d-block mt-2 mb-3">
                    <p>Algum texto explicando sobre o formulário</p>
                    <br>
                    @if(isset($gerenti))
                    <p>Você já possui registro ativo no Core-SP: <strong>{{ formataRegistro($gerenti) }}</strong></p>
                    @else
                    <hr />
                        @foreach(auth()->guard('user_externo')->user()->load('preRegistros')->preRegistros as $preRegistro)
                            @if(in_array($preRegistro->status, [$preRegistro::STATUS_NEGADO, $preRegistro::STATUS_APROVADO]))
                                <p><strong>ID da solicitação:</strong> {{ $preRegistro->id }}</p>
                                <p><strong>Solicitado em:</strong> {{ onlyDate($preRegistro->created_at) }} <strong>e encerrado em:</strong> {{ onlyDate($preRegistro->updated_at) }}</p>
                                <p>
                                    <strong>Status:</strong> <span class="badge badge{{ $preRegistro->getLabelStatus($preRegistro->status) }}">{{ $preRegistro->status }}</span>
                                    {{ $preRegistro->status == $preRegistro::STATUS_NEGADO ? '- ' . $preRegistro->getJustificativaNegado() : '' }}
                                </p>
                                <hr />
                            @endif
                        @endforeach

                    @endif

                    @if(isset($resultado->id))
                        <h5>ID da solicitação: {{ $resultado->id }}</h5>
                        <h5>Solicitado em: {{ onlyDate($resultado->created_at) }}</h5>
                        <h4>Status: {!! $resultado->getLabelStatusUser() !!}</h4>
                    @endif

                    @if(!isset($gerenti) && !auth()->guard('user_externo')->user()->preRegistroAprovado())
                        <form action="{{ route('externo.inserir.preregistro.view') }}" autocomplete="off">
                            <div class="form-check mt-3">
                                <input type="checkbox"
                                    id="checkPreRegistro"
                                    name="checkPreRegistro"
                                    class="form-check-input"
                                    required
                                    {{ isset($resultado->status) && ($resultado->status != $resultado::STATUS_CRIADO) ? 'checked' : '' }}
                                />
                                <label for="checkPreRegistro" class="text-justify font-weight-light">
                                    Estou ciente que iniciarei o processo de solicitação de registro para ser <strong>REPRESENTANTE COMERCIAL</strong>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-link {{ isset($resultado) ? 'btn-secondary' : 'btn-success' }} link-nostyle branco mt-3">
                                @if(!isset($resultado->id))
                                    Iniciar a solicitação do registro
                                @elseif($resultado->status == $resultado::STATUS_CRIADO)
                                    Continuar a solicitação do registro
                                @else
                                    {{ $resultado->userPodeCorrigir() ? 'Corrigir' : 'Visualizar' }} a solicitação do registro
                                @endif
                            </button>
                        </form>
                    @endif

                </div>      
            </div>
    </div>
</div>

@endsection