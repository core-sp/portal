<?php

namespace App\Contracts;

use App\User;

interface PlantaoJuridicoBloqueioSubServiceInterface {

    public function listar(User $user);

    public function view($id = null);

    public function save(User $user, $request, $id = null);

    public function getDatasHorasLinkPlantaoAjax($id);

    public function destroy($id);
}