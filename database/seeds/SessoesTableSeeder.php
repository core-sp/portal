<?php

use Illuminate\Database\Seeder;
use App\Sessao;

class SessoesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sessao = new Sessao();
        $sessao->idusuario = 6;
        $sessao->ip_address = '150.150.1.67';
        $sessao->created_at = '2019-05-03 14:57:24';
        $sessao->updated_at = '2019-05-03 15:02:27';
        $sessao->save();

        $sessao = new Sessao();
        $sessao->idusuario = 32;
        $sessao->ip_address = '150.150.1.72';
        $sessao->created_at = '2019-05-06 16:49:27';
        $sessao->updated_at = '2019-05-06 16:53:23';
        $sessao->save();

        $sessao = new Sessao();
        $sessao->idusuario = 5;
        $sessao->ip_address = '150.150.1.93';
        $sessao->created_at = '2019-05-07 11:56:13';
        $sessao->updated_at = '2019-05-07 11:56:13';
        $sessao->save();

        $sessao = new Sessao();
        $sessao->idusuario = 2;
        $sessao->ip_address = '150.150.1.30';
        $sessao->created_at = '2019-05-07 17:22:12';
        $sessao->updated_at = '2019-05-07 17:22:12';
        $sessao->save();

        $sessao = new Sessao();
        $sessao->idusuario = 31;
        $sessao->ip_address = '150.150.1.45';
        $sessao->created_at = '2019-05-06 09:59:42';
        $sessao->updated_at = '2019-05-06 15:02:57';
        $sessao->save();
    }
}
