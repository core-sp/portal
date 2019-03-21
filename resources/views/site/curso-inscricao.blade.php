<form method="POST" role="form">
	@csrf
	<input type="hidden" name="idcurso" value="{{ $curso->idcurso }}" />
	<input type="text" name="cpf" placeholder="CPF" /><br><br>
	<input type="text" name="nome" placeholder="Nome" /><br><br>
	<input type="text" name="telefone" placeholder="Telefone" /><br><br>
	<input type="text" name="email" placeholder="Email" /><br><br>
	<input type="submit" name="submit" value="Inscrever-se" />
</form>