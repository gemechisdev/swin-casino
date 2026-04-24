<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;

class LotteryWinner extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'prize_type'     => 'integer',
        'prize_amount'   => 'float',
        'is_distributed' => 'integer',
        'is_archived'    => 'integer',
        'distributed_at' => 'datetime',
        'delivery_status' => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────

    public function ticket()
    {
        return $this->belongsTo(LotteryTicket::class, 'lottery_ticket_id');
    }

    public function phase()
    {
        return $this->belongsTo(LotteryPhase::class, 'lottery_phase_id');
    }

    public function prizeTier()
    {
        return $this->belongsTo(LotteryPrizeTier::class, 'lottery_prize_tier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('is_distributed', Status::NO)->where('is_archived', Status::NO);
    }

    public function scopePhysical($query)
    {
        return $query->where('prize_type', Status::LOTTERY_PRIZE_PHYSICAL);
    }

    // ─── Accessors ────────────────────────────────────────────────────────

    public function deliveryBadge(): string
    {
        if ($this->prize_type !== Status::LOTTERY_PRIZE_PHYSICAL) {
            return '';
        }

        $map = [
            Status::LOTTERY_DELIVERY_PENDING    => ['badge--warning', 'Pending'],
            Status::LOTTERY_DELIVERY_DISPATCHED => ['badge--info', 'Dispatched'],
            Status::LOTTERY_DELIVERY_DELIVERED  => ['badge--success', 'Delivered'],
        ];

        [$cls, $label] = $map[$this->delivery_status] ?? ['badge--secondary', 'Unknown'];

        return '<span class="badge ' . $cls . '">' . $label . '</span>';
    }
}
