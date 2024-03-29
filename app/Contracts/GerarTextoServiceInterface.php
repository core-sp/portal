<?php

namespace App\Contracts;

interface GerarTextoServiceInterface {

    public function view($tipo_doc, $id = null);

    public function criar($tipo_doc);

    public function update($tipo_doc, $dados, $id = null);

    public function publicar($tipo_doc, bool $publicar = false);

    public function excluir($tipo_doc, $ids = array());

    public function show($tipo_doc, $id = null, $user = null);

    public function buscar($tipo_doc, $busca, $user = null);
}