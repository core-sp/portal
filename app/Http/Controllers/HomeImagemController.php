<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\HomeImagem;
use App\Events\CrudEvent;

class HomeImagemController extends Controller
{
    private $class = 'HomeImagemController';

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function editBannerPrincipal()
    {
        $this->authorize('updateOther', auth()->user());
        $resultado = HomeImagem::select('ordem','url','url_mobile','link','target')
            ->orderBy('ordem','ASC')
            ->get();
        $variaveis = [
            'singular' => 'banner principal',
            'singulariza' => 'o banner principal',
            'plural' => 'banner principal',
            'pluraliza' => 'banner principal',
            'form' => 'bannerprincipal'
        ];
        $variaveis = (object) $variaveis;
        return view('admin.crud.editar', compact('resultado', 'variaveis'));
    }

    public function updateBannerPrincipal(Request $request)
    {
        $this->authorize('updateOther', auth()->user());
        $array = $request->all();
        unset($array['_token'], $array['_method']);
        $chunk = array_chunk($array, 4);
        $update1 = HomeImagem::where('ordem',1)
            ->where('funcao','bannerprincipal')
            ->update([
                'url' => $chunk[0][0],
                'url_mobile' => $chunk[0][1],
                'link' => $chunk[0][2],
                'target' => $chunk[0][3]
            ]);
        $update2 = HomeImagem::where('ordem',2)
            ->where('funcao','bannerprincipal')
            ->update([
                'url' => $chunk[1][0],
                'url_mobile' => $chunk[1][1],
                'link' => $chunk[1][2],
                'target' => $chunk[1][3]
            ]);
        $update3 = HomeImagem::where('ordem',3)
            ->where('funcao','bannerprincipal')
            ->update([
                'url' => $chunk[2][0],
                'url_mobile' => $chunk[2][1],
                'link' => $chunk[2][2],
                'target' => $chunk[2][3]
            ]);
        $update4 = HomeImagem::where('ordem',4)
            ->where('funcao','bannerprincipal')
            ->update([
                'url' => $chunk[3][0],
                'url_mobile' => $chunk[3][1],
                'link' => $chunk[3][2],
                'target' => $chunk[3][3]
            ]);
        $update5 = HomeImagem::where('ordem','5')
            ->where('funcao','bannerprincipal')
            ->update([
                'url' => $chunk[4][0],
                'url_mobile' => $chunk[4][1],
                'link' => $chunk[4][2],
                'target' => $chunk[4][3]
            ]);
        $update6 = HomeImagem::where('ordem','6')
            ->where('funcao','bannerprincipal')
            ->update([
                'url' => $chunk[5][0],
                'url_mobile' => $chunk[5][1],
                'link' => $chunk[5][2],
                'target' => $chunk[5][3]
            ]);
        $update7 = HomeImagem::where('ordem','7')
            ->where('funcao','bannerprincipal')
            ->update([
                'url' => $chunk[6][0],
                'url_mobile' => $chunk[6][1],
                'link' => $chunk[6][2],
                'target' => $chunk[6][3]
            ]);
        if(!$update1 || !$update2 || !$update3 || !$update4 || !$update5 || !$update6 || !$update7)
            abort(500);
        event(new CrudEvent('banner principal', 'editou', 1));
        return redirect('/admin')
            ->with('message', '<i class="icon fa fa-check"></i>Banner editado com sucesso!')
            ->with('class', 'alert-success');
    }
}
