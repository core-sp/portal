<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Mail\InternoAgendamentoMail;
use App\Mail\ConexaoGerentiMail;
use Illuminate\Support\Facades\Mail;
use App\User;
use App\Agendamento;
use App\BdoOportunidade;
use App\Representante;
use Carbon\Carbon;
use PDO;
use PDOException;
use App\SolicitaCedula;
use App\Mail\InternoSolicitaCedulaMail;
use App\Pagamento;

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
        // Relatório dos agendados no dia de hoje
        $schedule->call(function() {
            $users = User::select('email','idregional','idperfil')
                ->where('idperfil', 1)
                ->orWhere('idperfil', 6)
                ->orWhere('idperfil', 8)
                ->orWhere('idperfil', 12)
                ->orWhere('idperfil', 13)
                ->orWhere('idperfil', 21)
                ->orderBy('idperfil')
                ->get();

            $todos_agendados = Agendamento::select('regionais.idregional', 'regionais.regional', 'agendamentos.*')
                ->join('regionais', 'agendamentos.idregional', '=', 'regionais.idregional')
                ->whereDate('dia', date('Y-m-d'))
                ->whereNull('status')
                ->orderBy('regionais.regional', 'ASC')
                ->orderBy('hora', 'ASC')
                ->get();

            foreach($users as $user)
            {
                $resultado = $user->getRelatorioAgendadosPorPerfil($todos_agendados);
                if(isset($resultado))
                {
                    try{
                        Mail::to($user->email)
                            ->send(new InternoAgendamentoMail($user, $resultado['agendados'], $resultado['subject']));
                    } catch(\Exception $e) {
                        \Log::error($e->getMessage());
                    }
                }
            }
        })->dailyAt('4:00');

        // Rotina mensal para 'Cancelar' os agendamentos com 1 mês ou mais e status null
        $schedule->call(function() {
            Agendamento::whereDate('dia', '<=', Carbon::today()->subMonth()->format('Y-m-d'))
                ->whereNull('status')
                ->update(['status' => Agendamento::STATUS_CANCELADO]);
        })->monthlyOn(5, '4:30');

        $schedule->call(function(){
            BdoOportunidade::select('idempresa', 'status', 'datainicio')
                ->where('datainicio', '<=', Carbon::now()->subDays(90)->toDateString())
                ->update([
                    'status' => 'Concluído'
                ]);
        })->dailyAt('2:00');

        $schedule->call(function(){
            Representante::where('created_at', '<=', Carbon::now()->subHours(24)->toDateTimeString())
                ->where('ativo', '=', 0)
                ->delete();
        })->hourly();
        
        // Verifica conexão com o gerenti a cada hora, caso não consiga se conectar, envia emails
        // $schedule->call(function(){
        //     $user = User::where('idusuario', 68)->first();
        //     $body = '<h3><i>(Mensagem Programada)</i></h3>';
        //     $body .= '<p><strong>Erro!</strong> Não foi possível estabelecer uma conexão com o sistema Gerenti no dia de hoje: <strong>'.Carbon::now()->format('d/m/Y, \à\s H:i').'</strong></p>';
        //     try {
        //         $host = env('GERENTI_HOST');
        //         $dbname = env('GERENTI_DATABASE');
        //         $username = env('GERENTI_USERNAME');
        //         $password = env('GERENTI_PASSWORD');
        //         $conexao = new PDO('firebird:dbname='.$host.':'.$dbname.';charset=UTF8',$username,$password);
        //         Mail::to($user->email)->send(new ConexaoGerentiMail($body .= 'LOGADO'));
        //     } catch (PDOException $e) {
        //         Mail::to($user->email)->send(new ConexaoGerentiMail($body .= $e->getMessage()));
        //     } finally{
        //         $conexao = null;
        //     }
        // })->hourly();

        // Rotina para envio de relatório de solicitações de cédula feitas no dia anterior
        $schedule->call(function() {
            $users = User::where('idperfil', 1)
                ->orWhere('idusuario', 54)
                ->orWhere('idusuario', 77)
                ->get();
            $hoje = date('Y-m-d');
            $ontem = Carbon::yesterday()->toDateString();
            $diaFormatado = onlyDate($ontem);
            foreach($users as $user) {
                $cedulas = SolicitaCedula::whereDate('created_at', $ontem)
                    ->where('status', SolicitaCedula::STATUS_EM_ANDAMENTO)
                    ->get()
                    ->sortBy(function($q){
                        return $q->regional->regional;
                    });
                if($cedulas->isNotEmpty()) {
                    try {
                        Mail::to($user->email)
                            ->send(new InternoSolicitaCedulaMail($cedulas, $diaFormatado));
                    } catch (\Exception $e) {
                        \Log::error($e->getMessage());
                    }
                }
            }
        })->dailyAt('4:15');

        // Rotina para reenviar pagamentos autorizados pelo portal em caso de erro e necessitam de confirmação a cada hora
        $schedule->call(function(){
            try {
                $all = Pagamento::where('status', 'AUTHORIZED')->whereNotNull('transacao_temp')->get();
                if($all->isNotEmpty())
                {
                    $service = resolve('App\Contracts\MediadorServiceInterface');
                    foreach($all as $pagamento)
                    {
                        $dados['updatePagamento'] = [
                            'transacao' => json_decode($pagamento->transacao_temp, true),
                            'user' => $pagamento->getUser(),
                            'pagamentos' => collect([
                                $pagamento, 
                                Pagamento::where('combined_id', $pagamento->combined_id)->where('payment_id', '!=', $pagamento->payment_id)->first()
                            ])
                        ];
                        \Log::channel('externo')->info('[Rotina Portal Pagamento] - Pagamento com a combined_id: ' . $pagamento->getIdPagamento() . ' em nova tentativa de confirmação de pagamento após erro.');
                        $service->getService('Pagamento')->rotinaUpdateTransacao($dados, $service);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('[Erro na rotina do Kernel (Confirmação de pagamento Autorizado)] - [Message: ' . $e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
            }
        })->hourly();

        // Rotina para reenviar pagamentos realizados pelo portal e erro ao transmitir para o Gerenti no momento da transação
        // $schedule->call(function(){
        //     try {
        //         $all = Pagamento::where('gerenti_ok', false)->get();
        //         foreach($all as $pagamento)
        //         {
        //             // Tenta enviar dados para o gerenti novamente, se sucesso atualiza os campos
        //             $pagamento->updateAposSucessoGerenti();
        //             \Log::channel('externo')->info('[Rotina Portal Pagamento] - Pagamento com a ID: ' . $pagamento->id . ' foi reenviado para o Gerenti com SUCESSO após erro.');
        //         }
        //     } catch (\Exception $e) {
        //         \Log::error('[Erro na rotina do Kernel (Reenvio de pagamento para o Gerenti)] - [Message: ' . $e->getMessage().'], [Código: '.$e->getCode().'], [Arquivo: '.$e->getFile().'], [Linha: '.$e->getLine().']');
        //     }
        // })->everyThirtyMinutes();
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
