@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helpers\BdoEmpresaControllerHelper;
$segmentos = BdoEmpresaControllerHelper::segmentos();
$capitais = BdoEmpresaControllerHelper::capitalSocial();
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Nova Empresa</h1>
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
              Preencha as informações para adicionar uma nova empresa
            </div>
          </div>
          <form role="form" method="POST">
            @csrf
            <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
            <div class="card-body">
              <div class="form-row">
                <div class="col">
                  <label for="segmento">Segmento</label>
                  <select name="segmento" class="form-control" id="segmento">
                    @foreach($segmentos as $segmento)
                    <option value="{{ $segmento }}">{{ $segmento }}</option>
                    @endforeach
                  </select>
                  @if($errors->has('segmento'))
                  <div class="invalid-feedback">
                    {{ $errors->first('segmento') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="cnpj">CNPJ</label>
                  <input type="text" class="form-control {{ $errors->has('cnpj') ? 'is-invalid' : '' }}" placeholder="CNPJ" name="cnpj" id="cnpj" maxlength="191" />
                  @if($errors->has('cnpj'))
                  <div class="invalid-feedback">
                    {{ $errors->first('cnpj') }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="form-row mt-2">
                <div class="col">
                  <label for="razaosocial">Razão Social</label>
                  <input type="text" class="form-control {{ $errors->has('razaosocial') ? 'is-invalid' : '' }}" name="razaosocial" id="razaosocial" placeholder="Razão Social">
                  @if($errors->has('razaosocial'))
                  <div class="invalid-feedback">
                    {{ $errors->first('razaosocial') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="capitalsocial">Capital Social</label>
                  <select name="capitalsocial" class="form-control" id="capitalsocial">
                    @foreach($capitais as $capital)
                    <option value="{{ $capital }}">{{ $capital }}</option>
                    @endforeach
                  </select>
                  @if($errors->has('capital'))
                  <div class="invalid-feedback">
                    {{ $errors->first('capital') }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="form-group mt-2">
                <label for="descricao">Descrição</label>
                <textarea class="form-control" name="descricao" id="descricao" rows="5" placeholder="Descrição da empresa"></textarea>
              </div>
              <h5 class="mt-4 mb-2">Informações da empresa</h5>
              <div class="form-row">
                <div class="col">
                  <label for="endereco">Endereço</label>
                  <input type="text" class="form-control {{ $errors->has('endereco') ? 'is-invalid' : '' }}" name="endereco" id="endereco" placeholder="Endereço">
                  @if($errors->has('endereco'))
                  <div class="invalid-feedback">
                    {{ $errors->first('endereco') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="site">Site</label>
                  <input type="text" class="form-control {{ $errors->has('site') ? 'is-invalid' : '' }}" name="site" id="site" placeholder="Site">
                  @if($errors->has('site'))
                  <div class="invalid-feedback">
                    {{ $errors->first('site') }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="form-row mt-2">
                <div class="col">
                  <label for="email">Email</label>
                  <input type="text" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" id="email" placeholder="Email da empresa">
                  @if($errors->has('email'))
                  <div class="invalid-feedback">
                    {{ $errors->first('email') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="telefone">Telefone</label>
                  <input type="text" class="form-control {{ $errors->has('telefone') ? 'is-invalid' : '' }}" name="telefone" id="telefone" placeholder="Telefone da empresa">
                  @if($errors->has('telefone'))
                  <div class="invalid-feedback">
                    {{ $errors->first('telefone') }}
                  </div>
                  @endif
                </div>
              </div>
              <h5 class="mt-4 mb-2">Contato principal da empresa</h5>
              <div class="form-row">
                <div class="col">
                  <label for="contatonome">Nome</label>
                  <input type="text" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="contatonome" id="contatonome" placeholder="Nome">
                  @if($errors->has('contatonome'))
                  <div class="invalid-feedback">
                    {{ $errors->first('contatonome') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="contatotelefone">Telefone</label>
                  <input type="text" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="contatotelefone" id="contatotelefone" placeholder="Telefone do contato">
                  @if($errors->has('contatotelefone'))
                  <div class="invalid-feedback">
                    {{ $errors->first('contatotelefone') }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="form-group mt-2">
                <label for="contatoemail">Email</label>
                <input type="text" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="contatoemail" id="contatoemail" placeholder="Email do contato">
                @if($errors->has('contatoemail'))
                <div class="invalid-feedback">
                  {{ $errors->first('contatoemail') }}
                </div>
                @endif
              </div>
            </div>
            <div class="card-footer float-right">
              <a href="/admin/bdo/empresas" class="btn btn-default">Cancelar</a>
              <button type="submit" class="btn btn-primary ml-1">Publicar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection