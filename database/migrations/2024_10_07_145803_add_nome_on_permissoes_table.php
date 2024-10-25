<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Permissao;
use App\Perfil;

class AddNomeOnPermissoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $perfis = Perfil::select('idperfil')->get()->pluck('idperfil')->toArray();

        Schema::table('permissoes', function (Blueprint $table) use($perfis) {

            Permissao::orderBy('idpermissao')->get()->each(function ($item, $key) use($perfis){
                
                $perfis_aceitos = array_filter(explode(',', str_replace(' ', '', $item->perfis)), function($value) use($perfis) {
                    return isset($value) && in_array($value, $perfis) && ($value != 1);
                });

                if(count($perfis_aceitos) > 0){
                    $valores = array_map('intval', $perfis_aceitos);
                    $item->perfis()->sync($valores);
                }
            });

            if(config('app.env') != "testing")
                \Log::channel('interno')->info('Migrate AddNomeOnPermissoesTable: adicionado os perfis da tabela permissão na nova tabela intermediária "perfil_permissao".');

            if (!Schema::hasColumn('permissoes', 'nome') && !Schema::hasColumn('permissoes', 'grupo_menu')) {
                $table->string('nome', 255)->nullable()->after('metodo');
                $table->string('grupo_menu', 255)->nullable()->after('nome');
                $table->string('perfis')->nullable()->change();

                if(config('app.env') != "testing")
                    \Log::channel('interno')->info('Migrate AddNomeOnPermissoesTable: criado campo "nome" e "grupo_menu", e campo "perfis" como nullable.');
            }
        });

        $call = \Artisan::call('db:seed --class=PermissoesTableSeeder --force');

        if(config('app.env') != "testing")
            \Log::channel('interno')->info('Migrate AddNomeOnPermissoesTable: campo "nome" preenchido pelo nome da permissão via seed.');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissoes', function (Blueprint $table) {
            $table->dropColumn('nome');
            $table->dropColumn('grupo_menu');
            $table->string('perfis')->nullable(false)->change();
        });
    }
}
