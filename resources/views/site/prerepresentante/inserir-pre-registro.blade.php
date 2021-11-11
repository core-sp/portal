@extends('site.prerepresentante.app')

@section('content-prerepresentante')

@if(Session::has('message'))
    <div class="d-block w-100">
        <p class="alert {{ Session::get('class') }}">{{ Session::get('message') }}</p>
    </div>
@endif

<div class="representante-content w-100">
        
    <!-- Nav tabs -->
    <ul class="nav nav-tabs nav-justified" role="tablist">
        <li class="nav-item text-primary">
            <a class="nav-link active" data-toggle="tab" href="#parte1">
                Parte 1
            </a>
        </li>
        <li class="nav-item text-primary">
            <a class="nav-link" data-toggle="tab" href="#parte2">
                Parte 2
            </a>
        </li>
        <li class="nav-item text-primary">
            <a class="nav-link" data-toggle="tab" href="#parte3">
                Parte 3
            </a>
        </li>
    </ul>

    <form action="{{-- route('prerepresentante.editar') --}}" method="POST" class="cadastroRepresentante">
        @csrf

        <!-- Tab panes -->
        <div class="tab-content">
            <div id="parte1" class="container tab-pane active"><br>
            
                <p class="bold">
                    Prentendo ser:
                </p>
                <div class="form-check-inline">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" value="PF" name="tipo">Pessoa Física
                    </label>
                </div>
                <div class="form-check-inline">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" value="RT" name="tipo">Responsável Técnico
                    </label>
                </div>
                <div class="form-check-inline disabled">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" value="PJ" name="tipo" disabled>Pessoa Jurídica
                    </label>
                </div>

            </div>
            <div id="parte2" class="container tab-pane fade"><br>
                <h3>
                    Menu 1
                </h3>
                <p>
                    Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                </p>
            </div>
            <div id="parte3" class="container tab-pane fade"><br>
                <h3>
                    Menu 2
                </h3>
                <p>
                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.
                </p>
            </div>
        </div>

    </form>

</div>

@endsection