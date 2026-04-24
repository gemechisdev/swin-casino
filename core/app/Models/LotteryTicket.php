<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;

class LotteryTicket extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'purchase_price' => 'float',
        'status'         => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────

    public function phase()
    {
        return $this->belongsTo(LotteryPhase::class, 'lottery_phase_id');
    }

    public function campaign()
    {
        return $this->belongsTo(LotteryCampaign::class, 'lottery_campaign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function winner()
    {
        return $this->hasOne(LotteryWinner::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', Status::LOTTERY_TICKET_ACTIVE);
    }

    public function scopeWinners($query)
    {
        return $query->where('status', Status::LOTTERY_TICKET_WINNER);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ─── Accessors ────────────────────────────────────────────────────────

    public function statusBadge(): string
    {
        $map = [
            Status::LOTTERY_TICKET_ACTIVE   => ['badge--primary', 'Active'],
            Status::LOTTERY_TICKET_WINNER   => ['badge--success', 'Winner'],
            Status::LOTTERY_TICKET_REFUNDED => ['badge--danger', 'Refunded'],
        ];

        [$cls, $label] = $map[$this->status] ?? ['badge--secondary', 'Unknown'];

        return '<span class="badge ' . $cls . '">' . $label . '</span>';
    }
}
