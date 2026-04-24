<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LotteryDrawArchive extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'draw_data' => 'object',
        'drawn_at'  => 'datetime',
    ];

    public function phase()
    {
        return $this->belongsTo(LotteryPhase::class, 'lottery_phase_id');
    }
}
