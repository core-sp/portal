<form role="form" method="POST">
    @csrf
    {{ method_field('PUT') }}
    <div class="card-body">
        <table class="table table-bordered perfilEdit table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Visualizar</th>
                    <th>Criar</th>
                    <th>Editar</th>
                    <th>Apagar</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Usuários</td>
                    @foreach($permissoesArray['UserController'] as $p)
                    @php $name = $p['controller'].'_'.$p['metodo']; @endphp
                        @if(strpos($p['perfis'], $idperfil.',') !== false)
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" checked /></td>
                        @else
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" /></td>
                        @endif
                    @endforeach
                    <td><input type="checkbox" class="form-check-input" disabled /></td>
                    <td><input type="checkbox" class="form-check-input" disabled /></td>
                    <td><input type="checkbox" class="form-check-input" disabled /></td>
                </tr>
                <tr>
                    <td>Regionais</td>
                    <td><input type="checkbox" class="form-check-input" disabled /></td>
                    <td><input type="checkbox" class="form-check-input" disabled /></td>
                    @foreach($permissoesArray['RegionalController'] as $p)
                    @php $name = $p['controller'].'_'.$p['metodo']; @endphp
                        @if(strpos($p['perfis'], $idperfil.',') !== false)
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" checked /></td>
                        @else
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" /></td>
                        @endif
                    @endforeach
                    <td><input type="checkbox" class="form-check-input" disabled /></td>
                </tr>
                <tr>
                    <td>Páginas</td>
                    @foreach($permissoesArray['PaginaController'] as $p)
                    @php $name = $p['controller'].'_'.$p['metodo']; @endphp
                        @if(strpos($p['perfis'], $idperfil.',') !== false)
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" checked /></td>
                        @else
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" /></td>
                        @endif
                    @endforeach
                </tr>
                <tr>
                    <td>Notícias</td>
                    @foreach($permissoesArray['NoticiaController'] as $p)
                    @php $name = $p['controller'].'_'.$p['metodo']; @endphp
                        @if(strpos($p['perfis'], $idperfil.',') !== false)
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" checked /></td>
                        @else
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" /></td>
                        @endif
                    @endforeach
                </tr>
                <tr>
                    <td>Cursos</td>
                    @foreach($permissoesArray['CursoController'] as $p)
                    @php $name = $p['controller'].'_'.$p['metodo']; @endphp
                        @if(strpos($p['perfis'], $idperfil.',') !== false)
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" checked /></td>
                        @else
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" /></td>
                        @endif
                    @endforeach
                </tr>
                <tr>
                    <td>Cursos<br />(Inscritos)</td>
                    @foreach($permissoesArray['CursoInscritoController'] as $p)
                    @php $name = $p['controller'].'_'.$p['metodo']; @endphp
                        @if(strpos($p['perfis'], $idperfil.',') !== false)
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" checked /></td>
                        @else
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" /></td>
                        @endif
                    @endforeach
                </tr>
                <tr>
                    <td>B. de Oportunidades<br />(Empresas)</td>
                    @foreach($permissoesArray['BdoEmpresaController'] as $p)
                    @php $name = $p['controller'].'_'.$p['metodo']; @endphp
                        @if(strpos($p['perfis'], $idperfil.',') !== false)
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" checked /></td>
                        @else
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" /></td>
                        @endif
                    @endforeach
                </tr>
                <tr>
                    <td>B. de Oportunidades<br />(Oportunidades)</td>
                    @foreach($permissoesArray['BdoOportunidadeController'] as $p)
                    @php $name = $p['controller'].'_'.$p['metodo']; @endphp
                        @if(strpos($p['perfis'], $idperfil.',') !== false)
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" checked /></td>
                        @else
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" /></td>
                        @endif
                    @endforeach
                </tr>
                <tr>
                    <td>Agendamentos</td>
                    @php
                        $p = $permissoesArray['AgendamentoController'][0];
                        $name = $p['controller'].'_'.$p['metodo'];
                    @endphp
                    @if(strpos($p['perfis'], $idperfil.',') !== false)
                        <td><input type="checkbox" class="form-check-input" name="{{ $name }}" checked /></td>
                    @else
                        <td><input type="checkbox" class="form-check-input" name="{{ $name }}" /></td>
                    @endif
                    <td><input type="checkbox" class="form-check-input" disabled /></td>
                    @php
                        $p = $permissoesArray['AgendamentoController'][1];
                        $name = $p['controller'].'_'.$p['metodo'];
                    @endphp
                    @if(strpos($p['perfis'], $idperfil.',') !== false)
                        <td><input type="checkbox" class="form-check-input" name="{{ $name }}" checked /></td>
                    @else
                        <td><input type="checkbox" class="form-check-input" name="{{ $name }}" /></td>
                    @endif
                    <td><input type="checkbox" class="form-check-input" disabled /></td>
                </tr>
                <tr>
                    <td>Agendamentos<br />(Bloqueios)</td>
                    @foreach($permissoesArray['AgendamentoBloqueioController'] as $p)
                    @php $name = $p['controller'].'_'.$p['metodo']; @endphp
                        @if(strpos($p['perfis'], $idperfil.',') !== false)
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" checked /></td>
                        @else
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" /></td>
                        @endif
                    @endforeach
                </tr>
                <tr>
                    <td>Licitações</td>
                    @foreach($permissoesArray['LicitacaoController'] as $p)
                    @php $name = $p['controller'].'_'.$p['metodo']; @endphp
                        @if(strpos($p['perfis'], $idperfil.',') !== false)
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" checked /></td>
                        @else
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" /></td>
                        @endif
                    @endforeach
                </tr>
                <tr>
                    <td>Concursos</td>
                    @foreach($permissoesArray['ConcursoController'] as $p)
                    @php $name = $p['controller'].'_'.$p['metodo']; @endphp
                        @if(strpos($p['perfis'], $idperfil.',') !== false)
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" checked /></td>
                        @else
                            <td><input type="checkbox" class="form-check-input" name="{{ $name }}" /></td>
                        @endif
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer float-right">
        <a href="/admin/usuarios/perfis" class="btn btn-default">Cancelar</a>
        <button type="submit" class="btn btn-primary ml-1">Salvar</button>
    </div>
</form>