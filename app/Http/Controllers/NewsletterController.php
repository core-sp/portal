<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ControleController;
use App\Newsletter;
use App\Events\ExternoEvent;
use Response;
use Redirect;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $regras = [
            'nomeNl' => 'required',
            'emailNl' => 'required|unique:newsletters,email',
            'celularNl' => 'required'
        ];
        $mensagens = [
            'nomeNl.required' => 'O nome é obrigatório',
            'emailNl.required' => 'O email é obrigatório',
            'celularNl.required' => 'O celular é obrigatório',
            'emailNl.unique' => 'Este email já está cadastrado em nosso sistema'
        ];
        $validation = Validator::make($request->all(), $regras, $mensagens);
        if($validation->fails()) {
            return redirect(url()->previous().'#rodape')->withErrors($validation)->withInput($request->all());
        }

        // Remove máscara
        $celular = preg_replace("/[^0-9]/", "", $request->input('celularNl'));

        $newsletter = new Newsletter();
        $newsletter->nome = $request->input('nomeNl');
        $newsletter->email = $request->input('emailNl');
        $newsletter->celular = $celular;
        $save = $newsletter->save();
        if(!$save)
            abort(500);
        // Gera evento de inscrição no Curso
        $string = "*".$newsletter->nome."* (".$newsletter->email.")";
        $string .= " *registrou-se* na newsletter";
        event(new ExternoEvent($string));
        // Gera mensagem de agradecimento
        $agradece = "Muito obrigado por inscrever-se em nossa newsletter";
        // Retorna view de agradecimento
        return view('site.agradecimento')->with('agradece', $agradece);
    }

    public static function countNewsletter()
    {
        $contagem = Newsletter::all()->count();
        return $contagem;
    }

    public static function countNewsletterLastWeek()
    {
        $now = (new \DateTime())->modify('-7 days');
        $contagem = Newsletter::where('created_at','>=',$now)->count();
        return $contagem;
    }

    public function download()
    {
        ControleController::autorizaStatic(['1','3','2']);
        $now = date('Ymd');
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=newsletter-'.$now.'.csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ];
        $lista = Newsletter::select('nome','email','celular','created_at')->get();
        $lista = $lista->toArray();
        array_unshift($lista, array_keys($lista[0]));
        $callback = function() use($lista) {
            $fh = fopen('php://output','w');
            foreach($lista as $linha) {
                fputcsv($fh,$linha);
            }
            fclose($fh);
        };
        return Response::stream($callback, 200, $headers);
    }
}
