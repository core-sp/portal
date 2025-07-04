<?php

namespace App\Http\Controllers;

use App\Repositories\BdoEmpresaRepository;
use App\Repositories\BdoOportunidadeRepository;
use App\Http\Requests\AnunciarVagaRequest;
use App\BdoOportunidade;
use App\Rules\Cnpj;
use App\BdoEmpresa;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\AnunciarVagaMail;
use App\Contracts\MediadorServiceInterface;
use Illuminate\Support\Facades\Request as IlluminateRequest;
use Illuminate\Support\Facades\View;

class BdoSiteController extends Controller
{
    private $bdoEmpresaRepository;
    private $bdoOportunidadeRepository;
    private $service;
    private $avisoAtivado;

    public function __construct(BdoEmpresaRepository $bdoEmpresaRepository, BdoOportunidadeRepository $bdoOportunidadeRepository, MediadorServiceInterface $service) 
    {
        $this->bdoEmpresaRepository = $bdoEmpresaRepository;
        $this->bdoOportunidadeRepository = $bdoOportunidadeRepository;
        $this->service = $service;
        $this->avisoAtivado = $this->service->getService('Aviso')->avisoAtivado($this->service->getService('Aviso')->areas()[1]);

        if($this->avisoAtivado)
        {
            $aviso = $this->service->getService('Aviso')->getByArea($this->service->getService('Aviso')->areas()[1]);
            View::share('aviso', $aviso);
            if(\Route::is('bdosite.anunciarVaga'))
                request()->merge(['avisoAtivado' => $this->avisoAtivado]);
        }
    }

    public function index()
    {
        $oportunidades = $this->bdoOportunidadeRepository->getToBalcaoSite();
        $regionais = $this->service->getService('Regional')->getRegionais();
        $segmentos = BdoEmpresa::segmentos();

        foreach($oportunidades as $o) {
            $o->regiaoFormatada = $this->displayRegioes($o->regiaoatuacao, $regionais->toArray());
        }

        return view('site.balcao-de-oportunidades', compact('oportunidades', 'regionais', 'segmentos'));
    }

    public function buscaOportunidades()
    {
    	$buscaPalavraChave = IlluminateRequest::input('palavra-chave');
        $buscaSegmento = IlluminateRequest::input('segmento');
        $buscaRegional = IlluminateRequest::input('regional') === 'todas' ?  $buscaRegional = '' : ','. IlluminateRequest::input('regional').',';

        $oportunidades = $this->bdoOportunidadeRepository->buscagetToBalcaoSite($buscaSegmento, $buscaRegional, $buscaPalavraChave);
        $regionais = $this->service->getService('Regional')->getRegionais();
        $segmentos = BdoEmpresa::segmentos();
        
        if (count($oportunidades) > 0) {
            foreach($oportunidades as $o) {
                $o->regiaoFormatada = $this->displayRegioes($o->regiaoatuacao, $regionais->toArray());
            }      
        } 
        else {
            $oportunidades = null;
        }

        return view('site.balcao-de-oportunidades', compact('oportunidades', 'regionais', 'segmentos'));
    }

    public function anunciarVagaView()
    {
        if(request()->session()->exists('errors') && $this->avisoAtivado)
            request()->session()->forget(['errors', '_old_input']);

        $regionais = $this->service->getService('Regional')->getRegionais();

        return view('site.anunciar-vaga', compact('regionais'));
    }

    public function anunciarVaga(AnunciarVagaRequest $request)
    {
        $validated = $request->validated();

        if($validated['idempresa'] == "0") {

            $request->descricao = 'Empresa cadastrada pelo site.';
            $empresa = $this->bdoEmpresaRepository->store($request->toEmpresaModel());

            if(!$empresa) {
                abort(403);
            }         
            
            $request->idempresa = $empresa->idempresa;
        } 
        else {
            $empresa = $this->bdoEmpresaRepository->getOportunidadesAbertasbyEmpresa($validated['idempresa']);

            if($empresa->oportunidade_count > 0) {
                return redirect()
                    ->back()
                    ->with([
                        'message' => 'A empresa informada <strong>já possui uma vaga sob análise ou em andamento no Balcão de Oportunidades</strong> do Core-SP. Para solicitar nova inclusão, favor entrar em contato através do e-mail: <strong>comunicacao@core-sp.org.br</strong> informando CNPJ, nome do responsável e telefone para contato.',
                        'class' => 'alert-danger'
                    ]);
            }
        }

        if($request->segmentoOportunidade === 'Outro' && !empty(request('outroseg'))) {
            $request->segmentoOportunidade = $request->outroseg;
        }

        $oportunidade = $this->bdoOportunidadeRepository->store($request->toOportunidadeModel());       

        if(!$oportunidade) {
            abort(403);
        }

        $termo = $oportunidade->termos()->create([
            'ip' => request()->ip()
        ]);

        event(new ExternoEvent('*' . $empresa->razaosocial . '* (' . $empresa->email . ') solicitou inclusão de oportunidade no Balcão de Oportunidades e '.$termo->message()));

        Mail::to(['comunicacao@core-sp.org.br', 'desenvolvimento@core-sp.org.br'])->queue(new AnunciarVagaMail($oportunidade->idoportunidade));

        return view('site.agradecimento')->with([
            'agradece' => $this->agradecimento()
        ]);
    }

    /** Método para montar o display das regiões de atuação da oportunidade */
    public function displayRegioes($regiaoAtuacao, $regionais)
    {
        if($regiaoAtuacao !== ',1,2,3,4,5,6,7,8,9,10,11,12,13,') {
            $regiaoAtuacao = explode(',',trim($regiaoAtuacao, ','));          

            $regArray = [];

            foreach($regiaoAtuacao as $r) {
                $index = array_search($r, array_column($regionais, 'idregional'));
                
                if ($index !== false) {
                    array_push($regArray, $regionais[$index]['regional']);
                }
            }      

            $regArray = implode(',',$regArray);
            $mostra = str_replace(',',' / ',$regArray);
            
            return $mostra;
        } 
        else {
            return "Em todo o estado de São Paulo";
        }
    }

    protected function agradecimento()
    {
        $agradece = 'Sua solicitação foi enviada com sucesso!';
        $agradece .= '<br><br>';
        $agradece .= 'Muito obrigado pelo interesse em fazer parte do <strong>Balcão de Oportunidades</strong> do <strong>Core-SP!</strong>';
        $agradece .= '<br><br>';
        $agradece .= 'A(s) vaga(s) será(ão) disponibilizada(s) em até 03 (três) dias úteis, após a verificação dos dados informados.';
        $agradece .= '<br><br>';
        $agradece .= 'Caso necessite mais esclarecimentos, entre em contato conosco através do email comunicacao@core-sp.org.br.';
        return $agradece;
    }
}
