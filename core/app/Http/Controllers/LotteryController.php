<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\LotteryCampaign;
use App\Models\LotteryPhase;
use App\Models\LotteryTicket;
use App\Models\LotteryWinner;
use App\Models\LotteryTransaction;
use App\Lottery\LotteryEngine;
use Illuminate\Http\Request;

class LotteryController extends Controller
{
    protected $engine;

    public function __construct()
    {
        $this->engine = new LotteryEngine();
    }

    public function index()
    {
        $pageTitle = 'AddisWin Lottery';
        $lotteries = LotteryCampaign::active()
            ->with(['phases' => function($q) {
                $q->whereIn('status', [Status::LOTTERY_PHASE_ACTIVE, Status::LOTTERY_PHASE_CLOSED])->latest('id');
            }])
            ->paginate(getPaginate());

        return view($this->activeTemplate . 'user.lottery.index', compact('pageTitle', 'lotteries'));
    }

    public function show($id)
    {
        $campaign = LotteryCampaign::active()->findOrFail($id);
        $phase = $campaign->activePhase();
        
        if (!$phase) {
            $notify[] = ['error', 'No active phase found for this lottery.'];
            return back()->withNotify($notify);
        }

        $pageTitle = $campaign->name . ' - Phase #' . $phase->phase_number;
        $prizeTiers = $phase->prizeTiers;
        $recentWinners = LotteryWinner::with(['user', 'prizeTier'])
            ->where('lottery_phase_id', '!=', $phase->id)
            ->whereHas('phase', function($q) use ($campaign) {
                $q->where('lottery_campaign_id', $campaign->id);
            })
            ->latest()
            ->take(10)
            ->get();

        return view($this->activeTemplate . 'user.lottery.show', compact('pageTitle', 'campaign', 'phase', 'prizeTiers', 'recentWinners'));
    }

    public function buyTickets(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $phase = LotteryPhase::active()->findOrFail($id);
        $user = auth()->user();

        try {
            $this->engine->purchaseTickets($user, $phase, $request->quantity);
            $notify[] = ['success', 'Successfully purchased ' . $request->quantity . ' tickets!'];
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
        }

        return back()->withNotify($notify);
    }

    public function myTickets()
    {
        $pageTitle = 'My Lottery Tickets';
        $tickets = LotteryTicket::where('user_id', auth()->id())
            ->with(['phase.campaign', 'winner.prizeTier'])
            ->orderBy('id', 'desc')
            ->paginate(getPaginate());

        return view($this->activeTemplate . 'user.lottery.my_tickets', compact('pageTitle', 'tickets'));
    }

    public function winners(Request $request)
    {
        $pageTitle = 'Lottery Winners';
        $winners = LotteryWinner::with(['user', 'phase.campaign', 'prizeTier'])
            ->where('is_archived', Status::NO)
            ->orderBy('id', 'desc');

        if ($request->campaign_id) {
            $winners = $winners->whereHas('phase', function($q) use ($request) {
                $q->where('lottery_campaign_id', $request->campaign_id);
            });
        }

        $winners = $winners->paginate(getPaginate());
        return view($this->activeTemplate . 'user.lottery.winners', compact('pageTitle', 'winners'));
    }

    public function history()
    {
        $pageTitle = 'Lottery Transactions';
        $logs = LotteryTransaction::where('user_id', auth()->id())
            ->with(['phase.campaign', 'ticket'])
            ->orderBy('id', 'desc')
            ->paginate(getPaginate());

        return view($this->activeTemplate . 'user.lottery.history', compact('pageTitle', 'logs'));
    }
}
