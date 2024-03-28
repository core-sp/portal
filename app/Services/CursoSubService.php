<?php

namespace App\Services;

use App\CursoInscrito;
use App\Events\CrudEvent;
use App\Events\ExternoEvent;
use App\Contracts\CursoSubServiceInterface;
use App\Mail\CursoInscritoMailGuest;
use Illuminate\Support\Facades\Mail;
use PDF;
use Illuminate\Support\Facades\Storage;
use App\QRCode;

class CursoSubService implements CursoSubServiceInterface {

    private function variaveis($curso)
    {
        return [
            'pluraliza' => 'inscritos',
            'plural' => 'inscritos',
            'singular' => 'inscrito',
            'singulariza' => 'o inscrito',
            'continuacao_titulo' => 'em <strong>'.$curso->tipo.': '.$curso->tema.'</strong>',
            'btn_lixeira' => '<a href="'.route('cursos.index').'" class="btn btn-default"><i class="fas fa-list"></i> Lista de Cursos</a>',
            'busca' => 'cursos/inscritos/'.$curso->idcurso,
            'addonsHome' => '<a href="'.route('inscritos.download', $curso->idcurso).'" class="btn btn-primary mb-2">Baixar CSV</a>',
            'btn_criar' => '<a href="'.route('inscritos.create', $curso->idcurso).'" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Adicionar inscrito</a> ',
        ];
    }

    private function tabelaCompleta($resultados, $user)
    {
        // Opções de cabeçalho da tabela
        $headers = [
            'ID',
            'CPF',
            'Nome',
            'Telefone',
            'Email',
            'Tipo da Inscrição',
            'Campo adicional',
            'Ações'
        ];
        // Opções de conteúdo da tabela
        $contents = [];
        $userPodeEdit = $user->can('updateOther', $user);
        $userPodeDestroy = $user->can('delete', $user);
        $podeCancelar = $resultados->isNotEmpty() ? $resultados->get(0)->podeCancelar() : false;
        foreach($resultados as $resultado) 
        {
            $acoes = '';
            if($userPodeEdit)
                $acoes .= ' <a href="'.route('inscritos.edit', $resultado->idcursoinscrito).'" class="btn btn-sm btn-default">Editar</a> ';
            if($userPodeDestroy && $podeCancelar) {
                $acoes .= '<form method="POST" action="'.route('inscritos.destroy', $resultado->idcursoinscrito).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="delete" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-danger" value="Cancelar Inscrição" onclick="return confirm(\'Tem certeza que deseja cancelar a inscrição?\')" />';
                $acoes .= '</form>';
            }elseif(!$resultado->possuiPresenca()){
                $acoes .= '<form method="POST" action="'.route('inscritos.update.presenca', $resultado->idcursoinscrito).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="put" />';
                $acoes .= '<input type="hidden" name="presenca" value="Sim" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-success" value="Confirmar presença" />';
                $acoes .= '</form> ';
                $acoes .= '<form method="POST" action="'.route('inscritos.update.presenca', $resultado->idcursoinscrito).'" class="d-inline">';
                $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                $acoes .= '<input type="hidden" name="_method" value="put" />';
                $acoes .= '<input type="hidden" name="presenca" value="Não" />';
                $acoes .= '<input type="submit" class="btn btn-sm btn-warning" value="Dar falta" />';
                $acoes .= '</form>';
            }elseif($resultado->possuiPresenca()){
                $acoes .= $resultado->compareceu() ? "<p class='d-inline text-success'><strong><i class='fas fa-check checkIcone'></i> Compareceu&nbsp;</strong></p>" :
                "<p class='d-inline text-danger'><strong><i class='fas fa-ban checkIcone'></i> Não Compareceu&nbsp;</strong></p>";

                if($resultado->compareceu() && $resultado->possuiCodigoCertificado()){
                    $acoes .= '<form method="POST" action="'.route('inscritos.reenviar.codigo', $resultado->idcursoinscrito).'" class="d-inline">';
                    $acoes .= '<input type="hidden" name="_token" value="'.csrf_token().'" />';
                    $acoes .= '<input type="hidden" name="_method" value="put" />';
                    $acoes .= '<button type="submit" class="btn btn-sm btn-primary" value="" />Reenviar Código</button>';
                    $acoes .= '</form> ';
                }
            }

            if(empty($acoes))
                $acoes = '<i class="fas fa-lock text-muted"></i>';
            $conteudo = [
                $resultado->idcursoinscrito,
                $resultado->cpf,
                $resultado->nome,
                $resultado->telefone,
                $resultado->email,
                $resultado->tipo_inscrito,
                $resultado->campo_adicional,
                $acoes
            ];
            array_push($contents, $conteudo);
        }
        // Classes da tabela
        $classes = [
            'table',
            'table-hover'
        ];

        $tabela = montaTabela($headers, $contents, $classes);
        return $tabela;
    }

    private function gerarQRCode($checksum, $idinscrito)
    {
        $nome_file = $idinscrito . '_' . now()->timestamp . '.png';
        $link = route('cursos.certificado.validar', (string) $checksum);
        $generator = new QRCode($link, ['s' => 'qr', 'wq' => 0, 'fc' => '#004587', 'sf' => 3]);

        /* Create bitmap image. */
        $image = $generator->render_image();
        imagepng($image, storage_path('app/certificados/temp/' . $nome_file));
        imagedestroy($image);

        return $nome_file;
    }

    public function tiposInscricao()
    {
        return CursoInscrito::tiposInscricao();
    }

    public function getTotalInscritos()
    {
        return CursoInscrito::count();
    }

    public function getRegrasCampoAdicional($id)
    {
        return CursoInscrito::findOrFail($id)->curso->getRegras();
    }

    public function listar($curso, $user)
    {
        $resultados = $curso->cursoinscrito()
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        $variaveis = $this->variaveis($curso);

        if($user->cannot('create', $user) || !$curso->podeInscrever())
            unset($variaveis['btn_criar']);

        return [
            'resultados' => $resultados, 
            'tabela' => $this->tabelaCompleta($resultados, $user), 
            'variaveis' => (object) $variaveis
        ];
    }

    public function view($curso = null, $id = null)
    {
        if(!isset($id) && !isset($curso))
            throw new \Exception('Deve inserir model curso ou id do inscrito', 500);

        $resultado = isset($id) ? CursoInscrito::findOrFail($id) : null;
        $curso = isset($resultado) ? $resultado->curso : $curso;

        if(!$curso->podeInscrever() && !isset($id))
            throw new \Exception('Não autorizado a adicionar inscrito fora do período de inscrição no curso com ID '.$curso->idcurso.'.', 403);

        $variaveis = $this->variaveis($curso);
        $variaveis['form'] = 'cursoinscrito';
        $variaveis['titulo_criar'] = 'Adicionar inscrito em '.$curso->tipo.': '.$curso->tema;

        return [
            'resultado' => $resultado,
            'curso' => $curso,
            'variaveis' => (object) $variaveis,
            'tipos' => CursoInscrito::tiposInscricao(),
        ];
    }

    public function save($validated, $user, $curso = null, $id = null)
    {
        if(!isset($id) && !isset($curso))
            throw new \Exception('Deve inserir model curso ou id do inscrito', 500);

        $inscrito = isset($id) ? CursoInscrito::findOrFail($id) : null;
        $curso = isset($inscrito) ? $inscrito->curso : $curso;

        if(!isset($id) && !$curso->podeInscrever())
            throw new \Exception('Não autorizado a adicionar inscrito fora do período de inscrição no curso com ID '.$curso->idcurso.'.', 403);

        $validated['idusuario'] = $user->idusuario;
        $validated['nome'] = mb_convert_case(mb_strtolower($validated['nome']), MB_CASE_TITLE);

        if(!isset($id) && $curso->tipoParaCertificado())
            $validated['codigo_certificado'] = CursoInscrito::gerarCodigoCertificado($validated['conta_no_portal']);

        if(isset($validated['cpf']))
            $validated['cpf'] = formataCpfCnpj(apenasNumeros($validated['cpf']));

        if($curso->add_campo)
        {
            $validated['campo_adicional'] = $curso->getFormatCampoAdicional($validated[$curso->campo_rotulo]);
            unset($validated[$curso->campo_rotulo]);
        }

        $acao = !isset($id) ? 'adicionou' : 'editou';

        if(!isset($inscrito))
        {
            unset($validated['conta_no_portal']);
            $inscrito = $curso->cursoinscrito()->create($validated);
        }else
            $inscrito->update($validated);
        
        event(new CrudEvent('inscrito em curso', $acao, $inscrito->idcursoinscrito));

        if(!isset($id) && $curso->tipoParaCertificado())
            Mail::to($inscrito->email)->queue(new CursoInscritoMailGuest($inscrito->textoAgradece()['agradece']));

        return [
            'idcurso' => $curso->idcurso
        ];
    }

    public function buscar($curso, $busca, $user)
    {
        $resultados = $curso->cursoinscrito()
        ->where(function($query) use($busca){
            $query->where('cpf','LIKE','%'.$busca.'%')
            ->orWhere('nome','LIKE','%'.$busca.'%')
            ->orWhere('email','LIKE','%'.$busca.'%');
        })
        ->paginate(10);

        $variaveis = $this->variaveis($curso);
        $variaveis['slug'] = 'cursos/inscritos/'.$curso->idcurso;

        if(!$curso->podeInscrever())
            unset($variaveis['btn_criar']);

        return [
            'resultados' => $resultados,
            'tabela' => $this->tabelaCompleta($resultados, $user), 
            'variaveis' => (object) $variaveis
        ];
    }

    public function destroy($id)
    {
        $inscrito = CursoInscrito::findOrFail($id);

        if(!$inscrito->podeCancelar())
            throw new \Exception('Não autorizado a cancelar inscrição com ID '.$id.' fora do período de inscrição.', 403);

        $inscrito->delete() ? event(new CrudEvent('inscrito em curso', 'cancelou inscrição', $id)) : null;

        return [
            'idcurso' => $inscrito->curso->idcurso
        ];
    }

    public function updatePresenca($id, $validated)
    {
        $inscrito = CursoInscrito::findOrFail($id);

        if($inscrito->podeCancelar())
            throw new \Exception('Não pode atualizar presença da inscrição (id: '.$id.') se ainda pode cancelar a inscrição.', 400);

        $acao = $validated['presenca'] == 'Sim' ? 'presença' : 'falta';

        $inscrito->update(['presenca' => $validated['presenca']]);

        event(new CrudEvent('no curso', 'confirmou '.$acao.' do participante '.$id, $inscrito->idcurso));
    }

    public function liberarInscricao($curso, $rep = null, $situacao = '')
    {
        if(isset($rep) && $curso->representanteInscrito($rep->cpf_cnpj))
            return [
                'rota' => 'representante.cursos',
                'message' => 'Já está inscrito neste curso!',
                'class' => 'alert-info',
            ];

        if(!$curso->podeInscreverExterno())
            return [
                'rota' => isset($rep) ? 'representante.cursos' : 'cursos.index.website',
                'message' => 'Não é possível realizar inscrição neste curso no momento',
                'class' => 'alert-danger',
            ];

        $rep = isset($rep);
        if(!$curso->liberarAcesso($rep, $situacao))
            return $situacao == '' ? [
                'rota' => 'representante.login',
                'message' => 'Deve realizar login na área restrita do representante para se inscrever.',
                'class' => 'alert-danger'
            ] : [
                'rota' => 'representante.cursos',
                'message' => '<i class="fas fa-info-circle"></i>&nbsp;Para liberar sua inscrição entre em contato com o setor de atendimento da <a href="'.route('regionais.siteGrid').'" target="_blank">seccional</a> de interesse.',
                'class' => 'alert-danger'
            ];
        
        return [];
    }

    public function inscricaoExterna($curso, $validated = null)
    {
        if(!isset($validated))
            return array();

        $validated['nome'] = mb_convert_case(mb_strtolower($validated['nome']), MB_CASE_TITLE);
        $validated['cpf'] = formataCpfCnpj(apenasNumeros($validated['cpf']));

        if($curso->tipoParaCertificado())
            $validated['codigo_certificado'] = CursoInscrito::gerarCodigoCertificado($validated['conta_no_portal']);

        $txt_certificado = isset($validated['codigo_certificado']) ? 'foi gerado código para o certificado' : null;
        $ip = $validated['ip'];

        unset($validated['ip']);
        unset($validated['termo']);
        unset($validated['conta_no_portal']);

        if($curso->add_campo)
        {
            $validated['campo_adicional'] = $curso->getFormatCampoAdicional($validated[$curso->campo_rotulo]);
            unset($validated[$curso->campo_rotulo]);
        }
        
        $inscrito = $curso->cursoinscrito()->create($validated);
        $termo = $inscrito->termos()->create(['ip' => $ip]);

        $string = $inscrito->nome." (CPF: ".$inscrito->cpf.") *inscreveu-se* no curso *".$inscrito->curso->tipo." - ".$inscrito->curso->tema;
        $string .= "*, turma *".$inscrito->curso->idcurso."*, ".$txt_certificado." e " . $termo->message();
        event(new ExternoEvent($string));
        
        $textos = $inscrito->textoAgradece();
        Mail::to($inscrito->email)->queue(new CursoInscritoMailGuest($textos['agradece']));

        return $textos;
    }

    public function reenviarCodigo($id, $service)
    {
        $inscrito = CursoInscrito::findOrFail($id);

        if(!$inscrito->possuiCodigoCertificado())
            return [
                'message' => 'Este CPF / CNPJ não possui código para ser enviado.',
                'class' => 'alert-danger'
            ];

        $conta_portal = $service->getService('Representante')->getRepresentanteByCpfCnpj(apenasNumeros($inscrito->cpf));
        $msg = $inscrito->podeGerarCertificado($conta_portal);

        if(isset($msg['message']))
            return $msg;
        
        Mail::to($inscrito->email)->queue(new CursoInscritoMailGuest($inscrito->textoReenvio()));
    }

    public function gerarCertificado($inscrito, $validated, $rep_autenticado = false)
    {
        if($rep_autenticado)
            $inscrito->update(['codigo_certificado' => null]);

        $msg = $inscrito->podeGerarCertificado($validated['conta_no_portal']);

        if(isset($msg['message']))
            return $msg;

        if($rep_autenticado)
            event(new ExternoEvent(' e realizou download do certificado.', 'Cursos'));

            $checksum = $inscrito->getChecksum();
            $nome_file = $this->gerarQRCode($checksum, $inscrito->idcursoinscrito);

            $download = PDF::loadView('site.inc.certificadoPDF', compact('inscrito', 'nome_file'))
                ->setPaper('a4', 'landscape')
                ->setWarnings(false)
                // ->download('certificado.pdf')
                ->stream('certificado.pdf');

            Storage::disk('local')->delete('certificados/temp/' . $nome_file);
            
        return [
            'download' => $download,
        ];
    }

    public function validarCertificado($checksum)
    {
        $valido = CursoInscrito::where('checksum', $checksum)->first();

        if(isset($valido))
            return [
                'message' => '<i class="fas fa-award"></i> Certificado do curso ' . $valido->curso->tema . ' é válido!',
                'class' => 'alert-success'
            ];
        return [
            'message' => '<i class="fas fa-award"></i> Certificado inválido!',
            'class' => 'alert-danger'
        ];
    }
}