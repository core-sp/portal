<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\BdoOportunidade;
use App\Rules\Cnpj;
use App\BdoEmpresa;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\AnunciarVagaMail;

class BdoSiteController extends Controller
{
    protected $idempresa;
    protected $idoportunidade;

    public function index()
    {
        $oportunidades = BdoOportunidade::orderBy('created_at','DESC')
            ->whereNotIn('status', ['Sob Análise', 'Recusado'])
            ->paginate(10);
        return view('site.balcao-de-oportunidades', compact('oportunidades'));
    }

    public function buscaOportunidades()
    {
    	$buscaPalavraChave = Input::get('palavra-chave');
        $buscaSegmento = Input::get('segmento');
        $buscaRegional = ','.Input::get('regional').',';
        if(Input::get('regional') === 'todas')
            $buscaRegional = '';
        if (!empty($buscaPalavraChave) 
            or !empty($buscaSegmento) 
        ){
            $busca = true;
        } else {
            $busca = false;
        }
        $oportunidades = BdoOportunidade::where('segmento','LIKE',$buscaSegmento)
            ->where('regiaoatuacao','LIKE','%'.$buscaRegional.'%')
            ->whereNotIn('status', ['Sob Análise', 'Recusado'])
            ->where(function($query) use ($buscaPalavraChave){
                $query->where('descricao','LIKE','%'.$buscaPalavraChave.'%')
                    ->orWhere('titulo','LIKE','%'.$buscaPalavraChave.'%');
            })->orderBy('created_at','DESC')
            ->paginate(10);
        if (count($oportunidades) > 0) {
            return view('site.balcao-de-oportunidades', compact('oportunidades', 'busca'));
        } else {
            $oportunidades = null;
            return view('site.balcao-de-oportunidades', compact('oportunidades', 'busca'));
        }
    }

    public function anunciarVagaView()
    {
        return view('site.anunciar-vaga');
    }

    protected function validaAnuncio()
    {
        request()->validate([
            'idempresa' => 'required',
            'razaosocial' => 'required_if:idempresa,!=,0|max:191',
            'fantasia' => 'required_if:idempresa,!=,0|max:191',
            'cnpj' => ['required', 'max:191', new Cnpj],
            'segmento' => 'required_if:idempresa,!=,0',
            'endereco' => 'required_if:idempresa,!=,0|max:191',
            'telefone' => 'required_if:idempresa,!=,0|max:191',
            'site' => 'required_if:idempresa,!=,0|max:191',
            'email' => 'required_if:idempresa,!=,0|max:191',
            'titulo' => 'required|max:191',
            'segmentoOportunidade' => 'required',
            'nrVagas' => 'required|max:3|not_in:0',
            'regiaoAtuacao' => 'required|array|min:1|max:15',
            'descricaoOportunidade' => 'required|max:500',
            'contatonome' => 'required|max:191',
            'contatotelefone' => 'required|max:191',
            'contatoemail' => 'required|email|max:191'
        ], [
            'cnpj.required' => 'Por favor, informe o CNPJ',
            'razaosocial.required_if' => 'Por favor, informe a Razão Social',
            'fantasia.required_if' => 'Por favor, informe o Nome Fantasia',
            'endereco.required_if' => 'Por favor, informe o endereço',
            'nrVagas.required' => 'Por favor, informe a quantidade de vagas da oportunidade',
            'nrVagas.not_in' => 'Valor inválido',
            'regiaoAtuacao.required' => 'Por favor, selecione ao menos uma região de atuação',
            'descricaoOportunidade.required' => 'Por favor, insira a descrição da oportunidade',
            'contatonome.required' => 'Por favor, informe o nome do contato',
            'contatotelefone.required' => 'Por favor, informe o telefone do contato',
            'contatoemail.required' => 'Por favor, informe o email do contato',
            'segmentoOportunidade.required' => 'Por favor, informe o segmento da oportunidade',
            'required' => 'Por favor, informe o :attribute',
            'required_if' => 'Por favor, informe o :attribute',
            'email' => 'Email inválido',
            'max' => 'Excedido número máximo de caracteres'
        ]);
    }

    protected function stringEvento($nomeEmpresa, $email)
    {
        return '*' . $nomeEmpresa . '* (' . $email . ') solicitou inclusão de oportunidade no Balcão de Oportunidades';
    }

    protected function stringRegioes($regioes)
    {
        return implode(', ', $regioes);
    }

    protected function bodyEmail() {
        $body = 'Nova solicitação de inclusão de oportunidade no Balcão de Oportunidades do Core-SP.';
        $body .= '<br><br>';
        $body .= '<strong>Código da Oportunidade:</strong> #' . $this->idoportunidade;
        $body .= '<br><br>';
        $body .= 'Favor acessar o <a href="'. route('site.home') .'/admin/bdo/editar/'. $this->idoportunidade .'">painel de administrador</a> do Core-SP para validar as informações.';
        return $body;
    }

    protected function agradecimento()
    {
        $agradece = 'Sua solicitação foi enviada com sucesso!';
        $agradece .= '<br><br>';
        $agradece .= 'Muito obrigado pelo interesse em fazer parte do <strong>Balcão de Oportunidades</strong> do <strong>Core-SP!</strong>';
        $agradece .= '<br><br>';
        $agradece .= 'A(s) vaga(s) será(ão) disponibilizada(s) em até 03 (três) dias úteis, após a verificação dos dados informados.';
        $agradece .= '<br><br>';
        $agradece .= 'Caso necessite mais esclarecimentos, entre em contato conosco através do email informacoes@core-sp.org.br.';
        return $agradece;
    }

    protected function saveBdoEmpresa()
    {
        $save = BdoEmpresa::create(request(['segmento', 'cnpj', 'razaosocial', 'fantasia', 'descricao', 'capitalsocial',
        'endereco', 'site', 'email', 'telefone', 'contatonome', 'contatotelefone', 'contatoemail', 'idusuario']));

        if(!$save)
            abort(403);

        $this->idempresa = $save->idempresa;
    }

    protected function saveBdoOportunidade($idempresa)
    {
        request('segmentoOportunidade') === 'Outro' && !empty(request('outroseg')) ? $segmento = request('outroseg') : $segmento = request('segmentoOportunidade');

        $save = BdoOportunidade::create([
            'idempresa' => $idempresa,
            'titulo' => request('titulo'),
            'segmento' => $segmento,
            'regiaoatuacao' => ',' . implode(',', request('regiaoAtuacao')),
            'descricao' => request('descricaoOportunidade'),
            'vagasdisponiveis' => request('nrVagas'),
            'status' => 'Sob Análise'
        ]);

        if(!$save)
            abort(403);

        $this->idoportunidade = $save->idoportunidade;
    }

    public function anunciarVaga()
    {
        $this->validaAnuncio();

        event(new ExternoEvent($this->stringEvento(request('razaosocial'), request('email'))));

        if(request('idempresa') === "0")
            $this->saveBdoEmpresa();

        request('idempresa') !== "0" ? $idempresa = request('idempresa') : $idempresa = $this->idempresa;

        $this->saveBdoOportunidade($idempresa);

        //Mail::to(['informacoes@core-sp.org.br', 'merielen.brito@corcesp.org.br', 'desenvolvimento@core-sp.org.br'])->queue(new AnunciarVagaMail($this->bodyEmail($this->idoportunidade)));
        Mail::to('desenvolvimento@core-sp.org.br')->queue(new AnunciarVagaMail($this->bodyEmail($this->idoportunidade)));

        return view('site.agradecimento')->with([
            'agradece' => $this->agradecimento()
        ]);
    }
}
