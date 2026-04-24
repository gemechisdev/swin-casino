<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\LotteryPhase;
use App\Lottery\LotteryEngine;

class LotteryCronController extends Controller
{
    protected $engine;

    public function __construct()
    {
        $this->engine = new LotteryEngine();
    }

    /**
     * Main entry point for the lottery cron job.
     */
    public function run()
    {
        // 1. Close phases that have reached sale_end_at
        $this->closeExpiredSales();

        // 2. Execute draws for phases that are CLOSED and reached draw_at
        $this->executePendingDraws();

        // 3. Distribute prizes for DRAWN phases
        $this->distributePendingPrizes();

        // 4. Create new phases for campaigns that have auto_next_phase enabled
        // This is handled inside distributePrizes usually, but we can ensure it here.
    }

    protected function closeExpiredSales()
    {
        $phases = LotteryPhase::where('status', Status::LOTTERY_PHASE_ACTIVE)
            ->where('sale_end_at', '<=', now())
            ->get();

        foreach ($phases as $phase) {
            $this->engine->closePhaseSales($phase);
        }
    }

    protected function executePendingDraws()
    {
        $phases = LotteryPhase::where('status', Status::LOTTERY_PHASE_CLOSED)
            ->where('draw_at', '<=', now())
            ->get();

        foreach ($phases as $phase) {
            try {
                $this->engine->executeDraw($phase);
            } catch (\Exception $e) {
                // Log error or notify admin?
                // For now, it will be captured by the CronJobLog in CronController
            }
        }
    }

    protected function distributePendingPrizes()
    {
        $phases = LotteryPhase::where('status', Status::LOTTERY_PHASE_DRAWN)->get();

        foreach ($phases as $phase) {
            try {
                $this->engine->distributePrizes($phase);
            } catch (\Exception $e) {
                // Handle exception
            }
        }
    }
}
