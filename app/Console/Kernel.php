<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Mail\InternoAgendamentoMail;
use Illuminate\Support\Facades\Mail;
use App\User;
use App\Agendamento;
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
            $diaFormatado = Helper::onlyDate($hoje);
            foreach($users as $user) {
                if($user->idperfil === 8) {
                    $count = Agendamento::where('idregional','=',$user->idregional)
                        ->where('dia','=',$hoje)
                        ->where(function($q){
                            $q->whereNull('status');
                        })->count();
                    if($count >= 1) {
                        $body = '<h3><i>(Mensagem Programada)</i></h3>';
                        $body .= '<p>';
                        if($count === 1)
                            $body .= 'Existe <strong>1 atendimento agendado</strong> ';
                        else
                            $body .= 'Existem <strong>'.$count.' atendimentos agendados<strong> ';
                        $body .= 'em '.$user->regional->regional.' hoje, dia <strong>'.Helper::onlyDate($hoje).'.</strong>';
                        $body .= '</p>';
                        $body .= '<p>';
                        $body .= '----------';
                        $body .= '</p>';
                        $body .= '<p>';
                        $body .= 'Por favor, acesse o <a href="https://core-sp.org.br/admin" target="_blank">painel de administrador</a> do Portal CORE-SP para mais informações.';
                        $body .= '</p>';
                        try {
                            Mail::to($user->email)
                                ->send(new InternoAgendamentoMail($body, 'em '.$user->regional->regional, $diaFormatado));
                        } catch (\Exception $e) {
                            \Log::error($e->getMessage());
                        }
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
                        $body = '<h3><i>(Mensagem Programada)</i></h3>';
                        $body .= '<p>Confira abaixo a lista de agendamentos solicitados pelo Portal CORE-SP hoje, <strong>'.Helper::onlyDate($hoje).':</strong></p>';
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
                        $regional = 'em '. $user->regional->regional;
                        try {
                            Mail::to($user->email)
                                ->send(new InternoAgendamentoMail($body, $regional, $diaFormatado));
                        } catch (\Exception $e) {
                            \Log::error($e->getMessage());
                        }
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
                        $body = '<h3><i>(Mensagem Programada)</i></h3>';
                        $body .= '<p>Confira abaixo a lista de agendamentos solicitados pelo Portal CORE-SP hoje, <strong>'.Helper::onlyDate($hoje).':</strong></p>';
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
                        $regional = 'nas Seccionais';
                        try {
                            Mail::to($user->email)
                                ->send(new InternoAgendamentoMail($body, $regional, $diaFormatado));
                        } catch (\Exception $e) {
                            \Log::error($e->getMessage());
                        }
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
                        $body = '<h3><i>(Mensagem Programada)</i></h3>';
                        $body .= '<p>Confira abaixo a lista de agendamentos solicitados pelo Portal CORE-SP hoje, <strong>'.Helper::onlyDate($hoje).':</strong></p>';
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
                        try {
                            Mail::to($user->email)
                                ->send(new InternoAgendamentoMail($body, 'em '.$user->regional->regional, $diaFormatado));
                        } catch (\Exception $e) {
                            \Log::error($e->getMessage());
                        }
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
                        $body = '<h3><i>(Mensagem Programada)</i></h3>';
                        $body .= '<p>Confira abaixo a lista de agendamentos solicitados pelo Portal CORE-SP hoje, <strong>'.Helper::onlyDate($hoje).':</strong></p>';
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
                        $regional = 'em São Paulo e Seccionais';
                        try {
                            Mail::to($user->email)
                                ->send(new InternoAgendamentoMail($body, $regional, $diaFormatado));
                        } catch (\Exception $e) {
                            \Log::error($e->getMessage());
                        }
                    }
                }
            }
        })->dailyAt('4:00');

        $schedule->call(function(){
            BdoOportunidade::select('idempresa', 'status', 'datainicio')
                ->where('datainicio', '<=', Carbon::now()->subDays(90)->toDateString())
                ->update([
                    'status' => 'Concluído'
                ]);
        })->dailyAt('2:00');

        $schedule->call(function(){
            Representante::where('created_at', '<=', Carbon::now()->subHours(24)->toDateString())
                ->where('ativo', '=', 0)
                ->delete();
        })->dailyAt('3:00');
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
