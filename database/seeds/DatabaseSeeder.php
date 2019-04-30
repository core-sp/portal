<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserTableSeeder::class);
        $this->call(RegionalTableSeeder::class);
        $this->call(PaginaTableSeeder::class);
        $this->call(NoticiaTableSeeder::class);
        $this->call(CursoTableSeeder::class);
        $this->call(LicitacaoTableSeeder::class);
        $this->call(BdoEmpresaTableSeeder::class);
        $this->call(BdoOportunidadeTableSeeder::class);
        $this->call(CursoInscritoTableSeeder::class);
        $this->call(ConcursoTableSeeder::class);
        $this->call(AgendamentoTableSeeder::class);
        $this->call(PerfilTableSeeder::class);
        $this->call(PermissaoTableSeeder::class);
    }
}
