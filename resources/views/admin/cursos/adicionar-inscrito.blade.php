@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Adicionar inscrito em {{ $curso->tipo }}: {{ $curso->tema }}</h1>
      </div>
    </div>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
        <div class="card card-info">
          <div class="card-header">
            <div class="card-title">
              Preencha as informações para adicionar o inscrito
            </div>
          </div>
          <form role="form" method="POST">
            @csrf
            <input type="hidden" name="idcurso" value="{{ $curso->idcurso }}" />
            <div class="card-body">
              <div class="form-row">
                <div class="col">
                  <label for="nome">Nome</label>
                  <input type="text" name="nome" class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}" placeholder="Nome" />
                  @if($errors->has('nome'))
                  <div class="invalid-feedback">
                    {{ $errors->first('nome') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="email">Email</label>
                  <input type="text" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="Email" />
                  @if($errors->has('email'))
                  <div class="invalid-feedback">
                    {{ $errors->first('email') }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="form-row mt-2">
                <div class="col">
                  <label for="telefone">Telefone</label>
                  <input type="text" name="telefone" class="form-control {{ $errors->has('telefone') ? 'is-invalid' : '' }}" placeholder="Nome" />
                  @if($errors->has('telefone'))
                  <div class="invalid-feedback">
                    {{ $errors->first('telefone') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="cpf">CPF</label>
                  <input type="text" name="cpf" class="form-control {{ $errors->has('cpf') ? 'is-invalid' : '' }}" placeholder="CPF" />
                  @if($errors->has('cpf'))
                  <div class="invalid-feedback">
                    {{ $errors->first('cpf') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="registrocore">Registro no CORE</label>
                  <input type="text"
                    name="registrocore"
                    class="form-control {{ $errors->has('registrocore') ? 'is-invalid' : '' }}"
                    placeholder="Nº do registro no CORE (opcional)" />
                    @if($errors->has('registrocore'))
                  <div class="invalid-feedback">
                    {{ $errors->first('registrocore') }}
                  </div>
                  @endif
                </div>
              </div>
            </div>
            <div class="card-footer float-right">
              <a href="/admin/cursos/inscritos/{{ $curso->idcurso }}" class="btn btn-default">Cancelar</a>
              <button type="submit" class="btn btn-primary ml-1">Publicar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection