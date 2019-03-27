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
        $this->call(PerfilTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(RegionalTableSeeder::class);
        $this->call(PaginaTableSeeder::class);
        $this->call(NoticiaTableSeeder::class);
    }
}
