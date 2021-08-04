<?php

namespace App\Http\Controllers;

use App\TermoConsentimento;
use App\Repositories\TermoConsentimentoRepository;
use App\Events\ExternoEvent;
use Illuminate\Http\Request;
use App\Http\Controllers\ControleController;
use Response;
use Illuminate\Support\Facades\Request as IlluminateRequest;

class TermoConsentimentoController extends Controller
{
    private $termoConsentimentoRepository;

    public function __construct(TermoConsentimentoRepository $termoConsentimentoRepository)
    {
        $this->termoConsentimentoRepository = $termoConsentimentoRepository;
    }

    public function termoConsentimentoView()
    {
        return view('site.termo-consentimento');
    }

    public function termoConsentimento(Request $request)
    {
        $regras = [
            'email' => 'required|email|max:191'
        ];
        $mensagens = [
            'required' => 'O campo :attribute é obrigatório',
            'max' => 'Excedido limite de caracteres',
            'email' => 'Email inválido'
        ];

        $errors = $request->validate($regras, $mensagens);

        $ja_existe = $this->termoConsentimentoRepository->getByEmail($request->email);

        if($ja_existe)
        {
            return redirect('/termo-de-consentimento')
                ->with('message', 'E-mail já cadastrado para continuar recebendo nossos informativos.')
                ->with('class', 'alert-warning');
        }

        $save = $this->termoConsentimentoRepository->create(request()->ip(), $request->email);

        if(!$save) {
            abort(500);
        }
        
        event(new ExternoEvent("foi criado um novo registro no termo de consentimento, com a id: " . $save->id));

        return redirect('/termo-de-consentimento')
                ->with('message', 'E-mail cadastrado com sucesso para continuar recebendo nossos informativos.')
                ->with('class', 'alert-success');
    }

    public function termoConsentimentoPdf()
    {
        return response()->file('arquivos/CORE-SP_Termo_de_consentimento.pdf');
    }

    public function download()
    {
        ControleController::autorizaStatic(['1','3']);
        $now = date('Ymd');
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=emails-termo_consentimento-'.$now.'.csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ];

        $lista1 = $this->termoConsentimentoRepository->getListaTermosAceitos();
        $array;

        if(!$lista1->isEmpty())
        {
            foreach($lista1 as $temp) {
                $array[] = $temp->attributesToArray();
            }
    
            array_unshift($array, array_keys($array[0]));
            $callback = function() use($array) {
                $fh = fopen('php://output','w');
                fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));
                foreach($array as $linha) {
                    fputcsv($fh,$linha,';');
                }
                fclose($fh);
            };

            return Response::stream($callback, 200, $headers);
        }

        return redirect('/admin')
            ->with('message', 'Não há emails cadastrados na tabela de Termo de Consentimento.')
            ->with('class', 'alert-warning');
    }
}
