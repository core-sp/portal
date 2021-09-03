<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Mail\InternoAgendamentoMail;
use App\Mail\InternoSolicitaCedulaMail;
use Illuminate\Support\Facades\Mail;
use App\User;
use App\Agendamento;
use App\SolicitaCedula;
use App\BdoOportunidade;
use App\Http\Controllers\Helper;
use App\Http\Controllers\Helpers\AgendamentoControllerHelper;
use App\Representante;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Rotina que envia emails para os perfis especificados com os agendamentos do dia as 4:00 de segunda a sexta
        $schedule->call(function() {
            $users = User::select('email','idregional','idperfil')
                ->where('idperfil','=',1)
                ->orWhere('idperfil','=',13)
                ->orWhere('idperfil','=',12)
                ->orWhere('idperfil','=',8)
                ->orWhere('idperfil','=',6)
                ->orWhere('idperfil','=',21)
                ->get();
            $hoje = date('Y-m-d');
            foreach($users as $user) {
                if($user->idperfil === 8) {
                    $count = Agendamento::where('idregional','=',$user->idregional)
                        ->where('dia','=',$hoje)
                        ->where(function($q){
                            $q->whereNull('status');
                        })->count();
                    if($count >= 1) {
                        $body = '<h3><i>(Mensagem Programada)</i></h3><p>';
                        if($count === 1)
                            $body .= 'Existe <strong>1 atendimento agendado</strong> ';
                        else
                            $body .= 'Existem <strong>'.$count.' atendimentos agendados<strong> ';
                        $body .= 'em '.$user->regional->regional.' hoje, dia <strong>'.Helper::onlyDate($hoje).'.</strong>';
                        $body .= '</p><p>----------</p><p>';
                        $body .= 'Por favor, acesse o <a href="https://core-sp.org.br/admin" target="_blank">painel de administrador</a> do Portal CORE-SP para mais informações.';
                        $body .= '</p>';
                        $regional = 'em '.$user->regional->regional;
                        $this->sendMails(Agendamento::class, $user, $body, $regional);
                    }
                } 
                elseif($user->idperfil === 21) {
                    $agendamentos = Agendamento::select('nome', 'cpf', 'protocolo', 'hora', 'tiposervico', 'idregional')
                        ->where('idregional', '=', $user->idregional)
                        ->where('dia', '=', $hoje)
                        ->where(function($q) {
                            $q->whereNull('status');
                        })->orderBy('idregional', 'ASC')
                        ->orderBy('hora', 'ASC')
                        ->get();

                    if($agendamentos->isNotEmpty()) {
                        $body = $this->tabelaAgendamentoMail($agendamentos);
                        $regional = 'em '. $user->regional->regional;
                        $this->sendMails(Agendamento::class, $user, $body, $regional);
                    }
                }
                elseif($user->idperfil === 13) {
                    $agendamentos = Agendamento::select('nome','cpf','protocolo','hora','tiposervico','idregional')
                        ->where('idregional','!=',1)
                        ->where('dia','=',$hoje)
                        ->where(function($q){
                            $q->whereNull('status');
                        })->orderBy('idregional','ASC')
                        ->orderBy('hora','ASC')
                        ->get();
                    if($agendamentos->isNotEmpty()) {
                        $body = $this->tabelaAgendamentoMail($agendamentos);
                        $regional = 'nas Seccionais';
                        $this->sendMails(Agendamento::class, $user, $body, $regional);
                    }
                } 
                elseif($user->idperfil === 12) {
                    $agendamentos = Agendamento::select('nome','cpf','protocolo','hora','tiposervico','idregional')
                        ->where('idregional','=',1)
                        ->where('dia','=',$hoje)
                        ->where(function($q){
                            $q->whereNull('status');
                        })->orderBy('hora','ASC')
                        ->get();
                    if($agendamentos->isNotEmpty()) {
                        $body = $this->tabelaAgendamentoMail($agendamentos);
                        $regional = 'em '.$user->regional->regional;
                        $this->sendMails(Agendamento::class, $user, $body, $regional);
                    }
                } 
                elseif($user->idperfil === 6 || $user->idperfil === 1) {
                    $agendamentos = Agendamento::select('nome','cpf','protocolo','hora','tiposervico','idregional')
                        ->where('dia','=',$hoje)
                        ->where(function($q){
                            $q->whereNull('status');
                        })->orderBy('idregional','ASC')
                        ->orderBy('hora','ASC')
                        ->get();
                    if($agendamentos->isNotEmpty()) {
                        $body = $this->tabelaAgendamentoMail($agendamentos);
                        $regional = 'em São Paulo e Seccionais';
                        $this->sendMails(Agendamento::class, $user, $body, $regional);
                    }
                }
            }
        })->dailyAt('4:00');

        // Rotina para atualizar os anúncios do BDO como 'Concluído' após 90 dias
        $schedule->call(function(){
            BdoOportunidade::select('idempresa', 'status', 'datainicio')
                ->where('datainicio', '<=', Carbon::now()->subDays(90)->toDateString())
                ->update([
                    'status' => 'Concluído'
                ]);
        })->dailyAt('2:00');

        // Rotina diária que deleta o cadastro do Representante se ele não confirmar via token no email enviado.
        $schedule->call(function(){
            Representante::where('created_at', '<=', Carbon::now()->subHours(24)->toDateString())
                ->where('ativo', '=', 0)
                ->delete();
        })->dailyAt('3:00');

        // Rotina que envia emails para os perfis especificados com as solicitações de cédulas 'em andamento' as 4:00 de segunda a sexta
        $schedule->call(function() {
            $users = User::select('email','idregional','idperfil')
                ->where('idperfil','=',1)
                ->orWhere('idperfil','=',13)
                ->orWhere('idperfil','=',12)
                ->orWhere('idperfil','=',8)
                ->orWhere('idperfil','=',6)
                ->orWhere('idperfil','=',21)
                ->get();
            $hoje = date('Y-m-d');
            foreach($users as $user) {
                if($user->idperfil === 8) {
                    $count = SolicitaCedula::where('idregional','=',$user->idregional)
                        ->where('status','=',SolicitaCedula::STATUS_EM_ANDAMENTO)
                        ->count();
                    if($count >= 1) {
                        $body = '<h3><i>(Mensagem Programada)</i></h3><p>';
                        if($count === 1)
                            $body .= 'Existe <strong>1 solicitação de cédula em andamento</strong> ';
                        else
                            $body .= 'Existem <strong>'.$count.' solicitações de cédulas em andamento<strong> ';
                        $body .= 'em '.$user->regional->regional.' hoje, dia <strong>'.Helper::onlyDate($hoje).'.</strong>';
                        $body .= '</p><p>----------</p><p>';
                        $body .= 'Por favor, acesse o <a href="https://core-sp.org.br/admin" target="_blank">painel de administrador</a> do Portal CORE-SP para mais informações.';
                        $body .= '</p>';
                        $regional = 'em '.$user->regional->regional;
                        $this->sendMails(SolicitaCedula::class, $user, $body, $regional);
                    }
                } 
                elseif($user->idperfil === 21) {
                    $cedulas = SolicitaCedula::where('idregional', '=', $user->idregional)
                        ->where('status', '=', SolicitaCedula::STATUS_EM_ANDAMENTO)
                        ->orderBy('idregional', 'ASC')
                        ->orderBy('id', 'ASC')
                        ->get();

                    if($cedulas->isNotEmpty()) {
                        $body = $this->tabelaSolicitaCedulaMail($cedulas);
                        $regional = 'em '. $user->regional->regional;
                        $this->sendMails(SolicitaCedula::class, $user, $body, $regional);
                    }
                }
                elseif($user->idperfil === 13) {
                    $cedulas = SolicitaCedula::where('idregional','!=',1)
                        ->where('status', '=', SolicitaCedula::STATUS_EM_ANDAMENTO)
                        ->orderBy('idregional','ASC')
                        ->orderBy('id','ASC')
                        ->get();
                    if($cedulas->isNotEmpty()) {
                        $body = $this->tabelaSolicitaCedulaMail($cedulas);
                        $regional = 'nas Seccionais';
                        $this->sendMails(SolicitaCedula::class, $user, $body, $regional);
                    }
                } 
                elseif($user->idperfil === 12) {
                    $cedulas = SolicitaCedula::where('idregional','=',1)
                        ->where('status', '=', SolicitaCedula::STATUS_EM_ANDAMENTO)
                        ->orderBy('id','ASC')
                        ->get();
                    if($cedulas->isNotEmpty()) {
                        $body = $this->tabelaSolicitaCedulaMail($cedulas);
                        $regional = 'em '.$user->regional->regional;
                        $this->sendMails(SolicitaCedula::class, $user, $body, $regional);
                    }
                } 
                elseif($user->idperfil === 6 || $user->idperfil === 1) {
                    $cedulas = SolicitaCedula::where('status', '=', SolicitaCedula::STATUS_EM_ANDAMENTO)
                        ->orderBy('idregional','ASC')
                        ->orderBy('id','ASC')
                        ->get();
                    if($cedulas->isNotEmpty()) {
                        $body = $this->tabelaSolicitaCedulaMail($cedulas);
                        $regional = 'em São Paulo e Seccionais';
                        $this->sendMails(SolicitaCedula::class, $user, $body, $regional);
                    }
                }
            }
        })->dailyAt('4:30');
    }

    // realiza o try/catch para o envio de email, pois se repete no código e deixa o método mais limpo
    private function sendMails($classe, $user, $body, $regional){
        $diaFormatado = Helper::onlyDate(date('Y-m-d'));

        if(class_basename($classe) == class_basename(Agendamento::class)){
            try {
                Mail::to($user->email)
                    ->send(new InternoAgendamentoMail($body, $regional, $diaFormatado));
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }
        } else{
            try {
                Mail::to($user->email)
                    ->send(new InternoSolicitaCedulaMail($body, $regional, $diaFormatado));
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }
        }
    }

    // retorna o $body com a tabela padrão para a maioria dos casos no schedule do agendamento e deixa o método mais limpo
    private function tabelaAgendamentoMail($agendamentos){
        $body = '<h3><i>(Mensagem Programada)</i></h3>';
        $body .= '<p>Confira abaixo a lista de agendamentos solicitados pelo Portal CORE-SP hoje, <strong>'.Helper::onlyDate(date('Y-m-d')).':</strong></p>';
        $body .= AgendamentoControllerHelper::tabelaEmailTop();
        foreach($agendamentos as $age) {
            $body .= '<tr>';
            $body .= '<td>'.$age->regional->regional.'</td>';
            $body .= '<td>'.$age->hora.'</td>';
            $body .= '<td>'.$age->protocolo.'</td>';
            $body .= '<td>'.$age->nome.'</td>';
            $body .= '<td>'.$age->cpf.'</td>';
            $body .= '<td>'.$age->tiposervico.'</td>';
            $body .= '</tr>';
        }
        $body .= AgendamentoControllerHelper::tabelaEmailBot();
        $body .= '<p>';
        $body .= 'Por favor, acesse o <a href="https://core-sp.org.br/admin" target="_blank">painel de administrador</a> do Portal CORE-SP para mais informações.';
        $body .= '</p>';
        return $body;
    }

    // retorna o $body com a tabela padrão para a maioria dos casos no schedule do solicita cédula e deixa o método mais limpo
    private function tabelaSolicitaCedulaMail($cedulas){
        $body = '<h3><i>(Mensagem Programada)</i></h3>';
        $body .= '<p>Confira abaixo a lista de solicitações de cédulas em andamento realizadas pelo Portal CORE-SP:</strong></p>';
        $body .= '<table border="1" cellspacing="0" cellpadding="6">';
        $body .= '<thead>';
        $body .= '<tr>';
        $body .= '<th>Regional</th>';
        $body .= '<th>Data de Solicitação</th>';
        $body .= '<th>Nome</th>';
        $body .= '<th>CPF/CNPJ</th>';
        $body .= '</tr>';
        $body .= '</thead>';
        $body .= '<tbody>';
        foreach($cedulas as $cedula) {
            $body .= '<tr>';
            $body .= '<td>'.$cedula->regional->regional.'</td>';
            $body .= '<td>'.$cedula->created_at->format('d/m/Y').'</td>';
            $body .= '<td>'.$cedula->representante->nome.'</td>';
            $body .= '<td>'.$cedula->representante->cpf_cnpj.'</td>';
            $body .= '</tr>';
        }
        $body .= '</tbody>';
        $body .= '</table>';
        $body .= '<p>';
        $body .= 'Por favor, acesse o <a href="https://core-sp.org.br/admin" target="_blank">painel de administrador</a> do Portal CORE-SP para mais informações.';
        $body .= '</p>';
        return $body;
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
