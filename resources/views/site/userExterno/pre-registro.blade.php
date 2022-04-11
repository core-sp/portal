@extends('site.userExterno.app')

@section('content-user-externo')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{!! Session::get('message') !!}</p>
    </div>
@endif

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light">
        <h4 class="pt-1 pb-1">Pré-registro</h4>
        <div class="linha-lg-mini mb-2"></div>
            <div class="list-group w-100">
                <div class="d-block mt-2 mb-3">
                    <p>Ao iniciar o preenchimento do formulário, você poderá salvar os dados sem precisar enviar de imediato para o atendimento do CORE-SP.</p>
                    <p>Em cada aba do formulário terá o botão <button class="btn btn-sm btn-primary">Salvar</button> para armazenar seus dados para futuras alterações.</p>
                    <p>E após o término, você terá na última aba do formulário o botão <button class="btn btn-sm btn-success">Enviar</button> para o atendimento fazer a análise.</p>
                    <p>Aqui mesmo o resultado será atualizado e seus dados estarão disponíveis para as correções, se forem necessárias.</p>
                    <p>As informações sobre os documentos exigidos, podem ser conferidas <a href="/registro-inicial">aqui</a>.</p>

                    <a href="{{ route('externo.inserir.preregistro.view') }}" class="btn btn-secondary link-nostyle branco mt-3">
                        {{ isset($resultado->id) ? 'Continuar' : 'Iniciar' }} o pré-registro
                    </a>
                </div>      
            </div>
    </div>
</div>

@endsection