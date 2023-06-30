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
use App\AgendamentoSala;
use Illuminate\Support\Facades\Storage;
use App\SuspensaoExcecao;

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

        // Suspensões com data finalizada serão excluídas como soft delete
        $schedule->call(function(){
            SuspensaoExcecao::where('data_final', '<', now()->format('Y-m-d'))
            ->delete();
        })->dailyAt('1:00');

        $schedule->call(function(){
            // Agendamentos não justificados ou status não atualizados após 2 dias
            $agendados = AgendamentoSala::whereNull('status')
            ->whereDate('dia', '<=', now()->subDays(3)->toDateString())
            ->get();

            foreach($agendados as $agendado){
                $agendado->update([
                    'status' => AgendamentoSala::STATUS_NAO_COMPARECEU
                ]);
                $dados = [
                    'idrepresentante' => $agendado->idrepresentante,
                    'data_inicial' => now()->format('Y-m-d'),
                    'data_final' => now()->addDays(30)->format('Y-m-d'),
                ];
                $suspenso = SuspensaoExcecao::existeSuspensao(apenasNumeros($agendado->representante->cpf_cnpj));
                $opcao = ' foi suspenso automaticamente por 30 dias';
                $final = ' a contar do dia ' . now()->format('d/m/Y') . '. Data da justificativa: ' . formataData(now());
                $texto = '[Rotina do Portal] - Após verificação dos agendamentos, o agendamento com o protocolo '. $agendado->protocolo;
                $texto .= ' teve o status atualizado para ' . AgendamentoSala::STATUS_NAO_COMPARECEU . ' devido ao não envio de justificativa. Então, o CPF / CNPJ ';
                $texto .= $agendado->representante->cpf_cnpj;
                if(!isset($suspenso)){
                    $texto .= $opcao . $final;
                    $dados['justificativa'] = json_encode([$texto], JSON_FORCE_OBJECT);
                    $agendado->suspensos()->create($dados);
                }
                else{
                    $texto .= isset($suspenso->data_final) ? $opcao . $final : ' foi mantida a suspensão por tempo indeterminado' . $final;
                    $dados['data_final'] = isset($suspenso->data_final) ? $dados['data_final'] : null;
                    $dados['justificativa'] = $suspenso->addJustificativa($texto);
                    $dados['agendamento_sala_id'] = $agendado->id;
                    $suspenso->update($dados);
                }
                \Log::channel('interno')->info($texto);
            }
        })->dailyAt('1:30');

        // Agendamentos com anexo finalizados com 1 mês ou mais terão o anexo removido.
        $schedule->call(function(){
            $agendados = AgendamentoSala::whereIn('status', [AgendamentoSala::STATUS_NAO_COMPARECEU, AgendamentoSala::STATUS_JUSTIFICADO])
            ->whereNotNull('anexo')
            ->whereDate('updated_at', '<=', now()->subMonth()->toDateString())
            ->get();

            foreach($agendados as $agendado)
            {
                $removeu = Storage::disk('local')->delete('representantes/agendamento_sala/'.$agendado->anexo);
                $removeu ? \Log::channel('interno')->info('[Rotina do Portal] - Removido anexo do agendamento de sala com ID ' . $agendado->id.'.') : 
                \Log::channel('interno')->info('[Rotina do Portal] - Não foi removido anexo do agendamento de sala com ID ' . $agendado->id.'.');
            }
        })->monthlyOn(1, '2:00');
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
