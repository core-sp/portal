@extends('site.representante.app')

@section('content-representante')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{{ Session::get('message') }}</p>
    </div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Contatos</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p>Se necessário, adicione um novo contato, para melhor comunicação com o Core-SP.</p>
        <div class="d-block mt-2 mb-3">
            <a href="{{ route('representante.inserir-ou-alterar-contato.view') }}" class="btn btn-primary link-nostyle branco">Inserir contato</a>
        </div>
        <h5 class="mt-3 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;CONTATOS CADASTRADOS</h5>
            <div class="contatos-table">
                @forelse (Auth::guard('representante')->user()->contatos() as $contato)
                <div class="contato-single {{ $loop->last ? '' : 'b-dashed' }}">
                    <p class="pb-0">
                        {{ gerentiTiposContatos()[$contato['CXP_TIPO']] }}:&nbsp;<strong>{{ $contato['CXP_VALOR'] }}</strong>
                        <small class="light">{{ $contato['CXP_STATUS'] === 1 ? '(Ativo)' : '(Inativo)' }}</small>
                    </p>
                    <div class="contato-btns">
                        {{-- <form action="{{ route('representante.inserir-ou-alterar-contato.view') }}" method="GET" class="d-inline">
                            @csrf
                            <input type="hidden" name="tipo" value="{{ $contato['CXP_TIPO'] }}">
                            <input type="hidden" name="id" value="{{ $contato['CXP_CNT_ID'] }}">
                            <input type="hidden" name="conteudo" value="{{ $contato['CXP_VALOR'] }}">
                            <input type="submit" value="Editar" class="btn btn-sm btn-info" />
                        </form> --}}
                        <form action="{{ route('representante.deletar-contato') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="id" value="{{ $contato['CXP_CNT_ID'] }}" />
                            <input type="hidden" name="status" value="{{ $contato['CXP_STATUS'] === 1 ? '0' : '1' }}">
                            <input type="submit" value="{{ $contato['CXP_STATUS'] === 1 ? 'Desativar' : 'Ativar' }}" class="btn btn-sm {{ $contato['CXP_STATUS'] === 1 ? 'btn-danger' : 'btn-success' }}" />
                        </form>
                    </div>
                </div>
            @empty
                <div class="contatos-table space-single">
                    <p class="light pb-0">Nenhum contato cadastrado.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection