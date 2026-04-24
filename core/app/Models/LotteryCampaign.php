<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class LotteryCampaign extends Model
{
    use GlobalStatus;

    protected $guarded = ['id'];

    protected $casts = [
        'ticket_price'         => 'float',
        'max_tickets_per_user' => 'integer',
        'total_ticket_limit'   => 'integer',
        'serial_length'        => 'integer',
        'draw_mode'            => 'integer',
        'auto_next_phase'      => 'integer',
        'phase_duration_days'  => 'integer',
        'house_edge'           => 'float',
        'status'               => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────

    public function phases()
    {
        return $this->hasMany(LotteryPhase::class);
    }

    public function tickets()
    {
        return $this->hasMany(LotteryTicket::class);
    }

    // ─── Accessors / Helpers ──────────────────────────────────────────────

    /**
     * Return the current active or upcoming phase.
     */
    public function activePhase()
    {
        return $this->phases()
            ->whereIn('status', [Status::LOTTERY_PHASE_ACTIVE, Status::LOTTERY_PHASE_CLOSED])
            ->latest('id')
            ->first();
    }

    public function latestPhase()
    {
        return $this->phases()->latest('id')->first();
    }

    /**
     * Human-readable draw mode label.
     */
    public function drawModeLabel(): string
    {
        return $this->draw_mode === Status::LOTTERY_DRAW_FROM_SOLD
            ? 'Draw from sold tickets'
            : 'Draw from full serial space';
    }
}
