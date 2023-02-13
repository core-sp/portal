<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SuporteIp extends Model
{
    protected $table = 'suporte_ips';
    protected $guarded = [];

    // Status LIBERADO para situação de ips não serem incluídos nas tentativas
    const LIBERADO = "LIBERADO";
    const BLOQUEADO = "BLOQUEADO";
    const DESBLOQUEADO = "DESBLOQUEADO";
    const TOTAL_TENTATIVAS = 6;

    private function mesmoDia()
    {
        return $this->updated_at->day == now()->day;
    }

    public function isUpdateTentativa()
    {
        return $this->isDesbloqueado();
    }

    public function isLiberado()
    {
        return $this->status == self::LIBERADO;
    }

    public function isDesbloqueado()
    {
        return $this->status == self::DESBLOQUEADO;
    }

    public function isBloqueado()
    {
        return $this->status == self::BLOQUEADO;
    }

    public function updateTentativa()
    {
        $tentativas = $this->tentativas;
        if($this->mesmoDia())
            $tentativas >= self::TOTAL_TENTATIVAS ? $this->update(['status' => self::BLOQUEADO]) : $this->update(['tentativas' => ++$tentativas]);
        else
            $this->update(['tentativas' => 1]);

        return $this->fresh();
    }
}
