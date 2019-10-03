<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\BdoOportunidade;
use App\Rules\Cnpj;
use App\Events\ExternoEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\AnunciarVagaMail;

class BdoSiteController extends Controller
{
    public function index()
    {
        $oportunidades = BdoOportunidade::orderBy('created_at','DESC')->paginate(10);
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
            'razaoSocial' => 'required|max:191',
            'nomeFantasia' => 'required|max:191',
            'cnpj' => ['required', 'max:191', new Cnpj],
            'endereco' => 'required|max:191',
            'telefone' => 'required|max:191',
            'site' => 'required|max:191',
            'email' => 'required|email|max:191',
            'nrVagas' => 'required|max:3',
            'regiaoAtuacao' => 'required|array|min:1|max:15',
            'descricao' => 'required|max:500',
            'contatoNome' => 'required|max:191',
            'contatoTelefone' => 'required|max:191',
            'contatoEmail' => 'required|email|max:191'
        ], [
            'razaoSocial.required' => 'Por favor, informe a Razão Social',
            'nomeFantasia.required' => 'Por favor, informe o Nome Fantasia',
            'endereco.required' => 'Por favor, informe o endereço',
            'nrVagas.required' => 'Por favor, informe a quantidade de vagas da oportunidade',
            'regiaoAtuacao.required' => 'Por favor, selecione ao menos uma região de atuação',
            'descricao.required' => 'Por favor, insira a descrição da oportunidade',
            'contatoNome.required' => 'Por favor, informe o nome do contato',
            'contatoTelefone.required' => 'Por favor, informe o telefone do contato',
            'contatoEmail.required' => 'Por favor, informe o email do contato',
            'required' => 'Por favor, informe :attribute',
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
        $body .= '<strong>Razão Social: </strong>' . request('razaoSocial');
        $body .= '<br>';
        $body .= '<strong>Nome Fantasia: </strong>' . request('nomeFantasia');
        $body .= '<br>';
        $body .= '<strong>CNPJ: </strong>' . request('cnpj');
        $body .= '<br>';
        $body .= '<strong>Capital Social:</strong>' . request('capital');
        $body .= '<br>';
        $body .= '<strong>Segmento da empresa: </strong>' . request('segmento');
        $body .= '<br>';
        $body .= '<strong>Endereço: </strong>' . request('endereco');
        $body .= '<br>';
        $body .= '<strong>Telefone: </strong>' . request('telefone');
        $body .= '<br>';
        $body .= '<strong>Site: </strong>' . request('site');
        $body .= '<br>';
        $body .= '<strong>Email: </strong>' . request('email');
        $body .= '<br>';
        $body .= '<strong>Número de vagas: </strong>' . request('nrVagas');
        $body .= '<br>';
        $body .= '<strong>Segmento da Oportunidade: </strong>' . request('segmentoOportunidade');
        $body .= '<br>';
        $body .= '<strong>Região de atuação: </strong>' . $this->stringRegioes(request('regiaoAtuacao'));
        $body .= '<br>';
        $body .= '<strong>Descrição da Oportunidade: </strong>' . request('descricao');
        $body .= '<br>';
        $body .= '<strong>Nome do contato: </strong>' . request('contatoNome');
        $body .= '<br>';
        $body .= '<strong>Telefone do contato: </strong>' . request('contatoTelefone');
        $body .= '<br>';
        $body .= '<strong>Email do contato: </strong>' . request('contatoEmail');
        $body .= '<br>';
        return $body;
    }

    protected function agradecimento()
    {
        $agradece = 'Sua solicitação foi enviada com sucesso. Muito obrigado pelo interesse em fazer parte do <strong>Balcão de Oportunidades</strong> do <strong>Core-SP!</strong>';
        $agradece .= '<br><br>';
        $agradece .= 'Responderemos à sua requisição o mais rapidamente possível através do email: <strong>' . request('email') . '.</strong>';
        return $agradece;
    }

    public function anunciarVaga()
    {
        $this->validaAnuncio();

        event(new ExternoEvent($this->stringEvento(request('razaoSocial'), request('email'))));

        Mail::to('informacoes@core-sp.org.br')->queue(new AnunciarVagaMail($this->bodyEmail()));

        return view('site.agradecimento')->with([
            'agradece' => $this->agradecimento()
        ]);
    }
}
