<?php

namespace App\Contracts;

interface GerarTextoServiceInterface {

    public function view($tipo_doc);

    public function criar($tipo_doc);

    public function update($tipo_doc, $dados, $id = null);

    public function publicar($tipo_doc, bool $publicar = false);

    public function excluir($tipo_doc, $id);

    public function show($tipo_doc, $id = null);

    public function buscar($tipo_doc, $busca);
}