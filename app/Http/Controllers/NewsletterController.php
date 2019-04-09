<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Newsletter;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $regras = [
            'nome' => 'required',
            'email' => 'required|unique:newsletters'
        ];
        $mensagens = [
            'required' => 'O :attribute é obrigatório',
            'email.unique' => 'Este email já está cadastrado em nosso sistema'
        ];
        $erros = $request->validate($regras, $mensagens);

        $newsletter = new Newsletter();
        $newsletter->nome = $request->input('nome');
        $newsletter->email = $request->input('email');
        $newsletter->celular = $request->input('celular');
        $newsletter->save();

        $agradece = "Muito obrigado por inscrever-se em nossa newsletter";

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
}
