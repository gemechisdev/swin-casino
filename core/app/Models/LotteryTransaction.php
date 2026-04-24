<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;

class LotteryTransaction extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount'       => 'float',
        'post_balance' => 'float',
        'type'         => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(LotteryTicket::class, 'lottery_ticket_id');
    }

    public function phase()
    {
        return $this->belongsTo(LotteryPhase::class, 'lottery_phase_id');
    }

    public function typeBadge(): string
    {
        $map = [
            Status::LOTTERY_TRX_PURCHASE => ['badge--danger', 'Purchase'],
            Status::LOTTERY_TRX_REFUND   => ['badge--success', 'Refund'],
            Status::LOTTERY_TRX_WIN      => ['badge--primary', 'Win'],
        ];

        [$cls, $label] = $map[$this->type] ?? ['badge--secondary', 'Unknown'];

        return '<span class="badge ' . $cls . '">' . $label . '</span>';
    }
}
