<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;

class LotteryPhase extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'sale_start_at'  => 'datetime',
        'sale_end_at'    => 'datetime',
        'draw_at'        => 'datetime',
        'drawn_at'       => 'datetime',
        'tickets_sold'   => 'integer',
        'total_revenue'  => 'float',
        'prize_pool'     => 'float',
        'house_cut'      => 'float',
        'status'         => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────

    public function campaign()
    {
        return $this->belongsTo(LotteryCampaign::class, 'lottery_campaign_id');
    }

    public function tickets()
    {
        return $this->hasMany(LotteryTicket::class);
    }

    public function prizeTiers()
    {
        return $this->hasMany(LotteryPrizeTier::class)->orderBy('sort_order');
    }

    public function winners()
    {
        return $this->hasMany(LotteryWinner::class);
    }

    public function drawArchives()
    {
        return $this->hasMany(LotteryDrawArchive::class);
    }

    public function lotteryTransactions()
    {
        return $this->hasMany(LotteryTransaction::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', Status::LOTTERY_PHASE_ACTIVE);
    }

    public function scopeDue($query)
    {
        return $query
            ->where('status', Status::LOTTERY_PHASE_CLOSED)
            ->where('draw_at', '<=', now());
    }

    public function scopeSaleCloseable($query)
    {
        return $query
            ->where('status', Status::LOTTERY_PHASE_ACTIVE)
            ->where('sale_end_at', '<=', now());
    }

    // ─── Accessors ────────────────────────────────────────────────────────

    public function statusBadge(): string
    {
        $map = [
            Status::LOTTERY_PHASE_PENDING   => ['badge--secondary', 'Pending'],
            Status::LOTTERY_PHASE_ACTIVE    => ['badge--success', 'Active'],
            Status::LOTTERY_PHASE_CLOSED    => ['badge--warning', 'Sales Closed'],
            Status::LOTTERY_PHASE_DRAWN     => ['badge--info', 'Drawn'],
            Status::LOTTERY_PHASE_COMPLETED => ['badge--primary', 'Completed'],
            Status::LOTTERY_PHASE_CANCELLED => ['badge--danger', 'Cancelled'],
        ];

        [$cls, $label] = $map[$this->status] ?? ['badge--secondary', 'Unknown'];

        return '<span class="badge ' . $cls . '">' . $label . '</span>';
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            Status::LOTTERY_PHASE_PENDING   => 'Pending',
            Status::LOTTERY_PHASE_ACTIVE    => 'Active',
            Status::LOTTERY_PHASE_CLOSED    => 'Sales Closed',
            Status::LOTTERY_PHASE_DRAWN     => 'Drawn',
            Status::LOTTERY_PHASE_COMPLETED => 'Completed',
            Status::LOTTERY_PHASE_CANCELLED => 'Cancelled',
        ];
        return $labels[$this->status] ?? 'Unknown';
    }

    /**
     * Re-calculate prize pool from current revenue and campaign house_edge.
     */
    public function recalculatePrizePool(): void
    {
        $houseEdge      = $this->campaign->house_edge ?? 0;
        $this->prize_pool = round($this->total_revenue * (1 - $houseEdge / 100), 8);
        $this->house_cut  = round($this->total_revenue - $this->prize_pool, 8);
    }
}
