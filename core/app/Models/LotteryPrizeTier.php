<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;

class LotteryPrizeTier extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'prize_type'    => 'integer',
        'amount_mode'   => 'integer',
        'prize_amount'  => 'float',
        'pot_percent'   => 'float',
        'winner_count'  => 'integer',
        'sort_order'    => 'integer',
        'has_physical'  => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────

    public function phase()
    {
        return $this->belongsTo(LotteryPhase::class, 'lottery_phase_id');
    }

    public function winners()
    {
        return $this->hasMany(LotteryWinner::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    /**
     * Resolve the effective cash prize amount for this tier.
     * For POT_SHARE tiers, pass the phase's prize_pool.
     */
    public function resolvedCashAmount(?float $prizePool = null): float
    {
        if ($this->prize_type === Status::LOTTERY_PRIZE_PHYSICAL) {
            return 0;
        }

        if ($this->amount_mode === Status::LOTTERY_PRIZE_POT_SHARE && $prizePool !== null) {
            return round($prizePool * ($this->pot_percent / 100), 8);
        }

        return (float) $this->prize_amount;
    }

    public function prizeTypeLabel(): string
    {
        $labels = [
            Status::LOTTERY_PRIZE_CASH     => 'Cash',
            Status::LOTTERY_PRIZE_PHYSICAL => 'Physical',
        ];
        return $labels[$this->prize_type] ?? 'Unknown';
    }

    public function amountModeLabel(): string
    {
        return $this->amount_mode === Status::LOTTERY_PRIZE_POT_SHARE
            ? 'Pot Share (' . $this->pot_percent . '%)'
            : 'Fixed Amount';
    }
}
