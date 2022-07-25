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
        <h4 class="pt-1 pb-1">Solicitação de Registro</h4>
        <div class="linha-lg-mini mb-2"></div>
            <div class="list-group w-100">
                <div class="d-block mt-2 mb-3">
                    <p>Algum texto explicando sobre o formulário</p>
                    <br>
                    @if(isset($gerenti))
                    <p>Você já possui registro ativo no Core-SP: <strong>{{ formataRegistro($gerenti) }}</strong></p>
                    @else
                        @if(isset($resultado->id))
                        <h4>Status: {!! $resultado->getLabelStatusUser() !!}</h4>
                            @if($resultado->status == $resultado::STATUS_NEGADO)
                            <p>
                                <strong>Justificativa:</strong>
                                {{ $resultado->getJustificativaNegado() }}
                            </p>
                            @endif
                        @endif
                    <form action="{{ route('externo.inserir.preregistro.view') }}" autocomplete="off">
                        <div class="form-check mt-3">
                            <input type="checkbox"
                                name="checkPreRegistro"
                                class="form-check-input"
                                required
                                {{ isset($resultado->status) && ($resultado->status != $resultado::STATUS_CRIADO) ? 'checked' : '' }}
                            /> 
                            <label for="termo" class="text-justify">
                                Estou ciente que iniciarei o processo de solicitação de registro para ser <strong>REPRESENTANTE COMERCIAL</strong>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-link {{ isset($resultado) ? 'btn-secondary' : 'btn-success' }} link-nostyle branco mt-3">
                            @if(!isset($resultado))
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