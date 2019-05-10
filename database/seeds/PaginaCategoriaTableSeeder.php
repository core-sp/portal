<?php

use Illuminate\Database\Seeder;
use App\PaginaCategoria;

class PaginaCategoriaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cat = new PaginaCategoria();
        $cat->nome = "Serviços";
        $cat->idusuario = 1;
        $cat->save();
    }
}
