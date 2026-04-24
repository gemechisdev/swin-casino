<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\LotteryCampaign;
use App\Models\LotteryPhase;
use App\Models\LotteryPrizeTier;
use App\Models\LotteryWinner;
use App\Models\LotteryTicket;
use App\Lottery\LotteryEngine;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;

class LotteryController extends Controller
{
    protected $engine;

    public function __construct()
    {
        $this->engine = new LotteryEngine();
    }

    // ─── Campaigns ────────────────────────────────────────────────────────

    public function campaigns()
    {
        $pageTitle = 'Lottery Campaigns';
        $campaigns = LotteryCampaign::orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.lottery.campaigns.index', compact('pageTitle', 'campaigns'));
    }

    public function campaignCreate()
    {
        $pageTitle = 'Create Lottery Campaign';
        return view('admin.lottery.campaigns.create', compact('pageTitle'));
    }

    public function campaignStore(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'ticket_price'         => 'required|numeric|gt:0',
            'max_tickets_per_user' => 'nullable|integer|min:0',
            'total_ticket_limit'   => 'nullable|integer|min:0',
            'serial_length'        => 'required|integer|min:4|max:20',
            'draw_mode'            => 'required|integer|in:1,2',
            'auto_next_phase'      => 'required|integer|in:0,1',
            'phase_duration_days'  => 'required|integer|min:1',
            'house_edge'           => 'required|numeric|min:0|max:100',
            'description'          => 'nullable|string',
            'image'                => ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        $campaign = new LotteryCampaign();
        $this->saveCampaign($campaign, $request);

        $notify[] = ['success', 'Campaign created successfully'];
        return to_route('admin.lottery.campaigns.edit', $campaign->id)->withNotify($notify);
    }

    public function campaignEdit($id)
    {
        $campaign = LotteryCampaign::findOrFail($id);
        $pageTitle = 'Edit Campaign: ' . $campaign->name;
        return view('admin.lottery.campaigns.edit', compact('pageTitle', 'campaign'));
    }

    public function campaignUpdate(Request $request, $id)
    {
        $campaign = LotteryCampaign::findOrFail($id);
        $request->validate([
            'name'                 => 'required|string|max:255',
            'ticket_price'         => 'required|numeric|gt:0',
            'max_tickets_per_user' => 'nullable|integer|min:0',
            'total_ticket_limit'   => 'nullable|integer|min:0',
            'serial_length'        => 'required|integer|min:4|max:20',
            'draw_mode'            => 'required|integer|in:1,2',
            'auto_next_phase'      => 'required|integer|in:0,1',
            'phase_duration_days'  => 'required|integer|min:1',
            'house_edge'           => 'required|numeric|min:0|max:100',
            'description'          => 'nullable|string',
            'image'                => ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        $this->saveCampaign($campaign, $request);

        $notify[] = ['success', 'Campaign updated successfully'];
        return back()->withNotify($notify);
    }

    protected function saveCampaign($campaign, $request)
    {
        if ($request->hasFile('image')) {
            try {
                $campaign->image = fileUploader($request->image, getFilePath('lottery'), getFileSize('lottery'), $campaign->image);
            } catch (\Exception $exp) {
                throw new \Exception("Could not upload the image.");
            }
        }

        $campaign->name                 = $request->name;
        $campaign->ticket_price         = $request->ticket_price;
        $campaign->max_tickets_per_user = $request->max_tickets_per_user ?? 0;
        $campaign->total_ticket_limit   = $request->total_ticket_limit ?? 0;
        $campaign->serial_length        = $request->serial_length;
        $campaign->draw_mode            = $request->draw_mode;
        $campaign->auto_next_phase      = $request->auto_next_phase;
        $campaign->phase_duration_days  = $request->phase_duration_days;
        $campaign->house_edge           = $request->house_edge;
        $campaign->description          = $request->description;
        $campaign->save();
    }

    public function campaignStatus($id)
    {
        return LotteryCampaign::changeStatus($id);
    }

    // ─── Phases ───────────────────────────────────────────────────────────

    public function phases(Request $request)
    {
        $pageTitle = 'Lottery Phases';
        $phases = LotteryPhase::with('campaign')->orderBy('id', 'desc');
        
        if ($request->campaign_id) {
            $phases = $phases->where('lottery_campaign_id', $request->campaign_id);
        }

        $phases = $phases->paginate(getPaginate());
        return view('admin.lottery.phases.index', compact('pageTitle', 'phases'));
    }

    public function phaseCreate($campaignId)
    {
        $campaign = LotteryCampaign::findOrFail($campaignId);
        $pageTitle = 'Create New Phase for ' . $campaign->name;
        return view('admin.lottery.phases.create', compact('pageTitle', 'campaign'));
    }

    public function phaseStore(Request $request, $campaignId)
    {
        $campaign = LotteryCampaign::findOrFail($campaignId);
        $request->validate([
            'phase_number'  => 'required|integer',
            'sale_start_at' => 'required|date',
            'sale_end_at'   => 'required|date|after:sale_start_at',
            'draw_at'       => 'required|date|after:sale_end_at',
        ]);

        $phase = new LotteryPhase();
        $phase->lottery_campaign_id = $campaign->id;
        $phase->phase_number        = $request->phase_number;
        $phase->sale_start_at       = $request->sale_start_at;
        $phase->sale_end_at         = $request->sale_end_at;
        $phase->draw_at             = $request->draw_at;
        $phase->status              = Status::LOTTERY_PHASE_PENDING;
        $phase->save();

        $notify[] = ['success', 'Phase created successfully'];
        return to_route('admin.lottery.phases.edit', $phase->id)->withNotify($notify);
    }

    public function phaseEdit($id)
    {
        $phase = LotteryPhase::with(['campaign', 'prizeTiers'])->findOrFail($id);
        $pageTitle = 'Edit Phase #' . $phase->phase_number . ' - ' . $phase->campaign->name;
        return view('admin.lottery.phases.edit', compact('pageTitle', 'phase'));
    }

    public function phaseUpdate(Request $request, $id)
    {
        $phase = LotteryPhase::findOrFail($id);
        $request->validate([
            'phase_number'  => 'required|integer',
            'sale_start_at' => 'required|date',
            'sale_end_at'   => 'required|date|after:sale_start_at',
            'draw_at'       => 'required|date|after:sale_end_at',
            'status'        => 'required|integer|in:0,1,2,3,4,5',
        ]);

        $phase->phase_number  = $request->phase_number;
        $phase->sale_start_at = $request->sale_start_at;
        $phase->sale_end_at   = $request->sale_end_at;
        $phase->draw_at       = $request->draw_at;
        $phase->status        = $request->status;
        $phase->save();

        $notify[] = ['success', 'Phase updated successfully'];
        return back()->withNotify($notify);
    }

    public function phaseDraw($id)
    {
        $phase = LotteryPhase::findOrFail($id);
        try {
            $this->engine->executeDraw($phase);
            $notify[] = ['success', 'Draw executed successfully!'];
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
        }
        return back()->withNotify($notify);
    }

    public function phaseDistribute($id)
    {
        $phase = LotteryPhase::findOrFail($id);
        try {
            $this->engine->distributePrizes($phase);
            $notify[] = ['success', 'Prizes distributed successfully!'];
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
        }
        return back()->withNotify($notify);
    }

    // ─── Prize Tiers ──────────────────────────────────────────────────────

    public function tierStore(Request $request, $phaseId)
    {
        $phase = LotteryPhase::findOrFail($phaseId);
        $request->validate([
            'prize_type'   => 'required|integer|in:1,2',
            'amount_mode'  => 'required|integer|in:1,2',
            'prize_amount' => 'required_if:amount_mode,1|numeric|min:0',
            'pot_percent'  => 'required_if:amount_mode,2|numeric|min:0|max:100',
            'winner_count' => 'required|integer|min:1',
            'prize_title'  => 'required|string|max:255',
            'description'  => 'nullable|string',
        ]);

        $tier = new LotteryPrizeTier();
        $tier->lottery_phase_id = $phase->id;
        $tier->prize_title      = $request->prize_title;
        $tier->prize_type       = $request->prize_type;
        $tier->amount_mode      = $request->amount_mode;
        $tier->prize_amount     = $request->prize_amount ?? 0;
        $tier->pot_percent      = $request->pot_percent ?? 0;
        $tier->winner_count     = $request->winner_count;
        $tier->description      = $request->description;
        $tier->sort_order       = $phase->prizeTiers()->count() + 1;
        $tier->save();

        $notify[] = ['success', 'Prize tier added'];
        return back()->withNotify($notify);
    }

    public function tierDelete($id)
    {
        $tier = LotteryPrizeTier::findOrFail($id);
        $tier->delete();
        $notify[] = ['success', 'Prize tier removed'];
        return back()->withNotify($notify);
    }

    // ─── Winners & Delivery ───────────────────────────────────────────────

    public function winners(Request $request)
    {
        $pageTitle = 'Lottery Winners';
        $winners = LotteryWinner::with(['user', 'phase.campaign', 'prizeTier', 'ticket'])
            ->orderBy('id', 'desc');

        if ($request->search) {
            $search = $request->search;
            $winners = $winners->whereHas('user', function($q) use ($search) {
                $q->where('username', 'LIKE', "%$search%");
            });
        }

        if ($request->physical) {
            $winners = $winners->where('prize_type', Status::LOTTERY_PRIZE_PHYSICAL);
        }

        $winners = $winners->paginate(getPaginate());
        return view('admin.lottery.winners.index', compact('pageTitle', 'winners'));
    }

    public function winnerUpdateDelivery(Request $request, $id)
    {
        $winner = LotteryWinner::findOrFail($id);
        $request->validate([
            'delivery_status' => 'required|integer|in:0,1,2',
            'admin_note'      => 'nullable|string',
        ]);

        $winner->delivery_status = $request->delivery_status;
        $winner->admin_note      = $request->admin_note;
        
        if ($request->delivery_status == Status::LOTTERY_DELIVERY_DELIVERED && !$winner->is_distributed) {
            $winner->is_distributed = Status::YES;
            $winner->distributed_at = now();
        }

        $winner->save();

        $notify[] = ['success', 'Delivery status updated'];
        return back()->withNotify($notify);
    }

    // ─── Tickets ──────────────────────────────────────────────────────────

    public function tickets(Request $request)
    {
        $pageTitle = 'Lottery Tickets';
        $tickets = LotteryTicket::with(['user', 'phase.campaign'])->orderBy('id', 'desc');

        if ($request->search) {
            $search = $request->search;
            $tickets = $tickets->where('serial', 'LIKE', "%$search%")
                ->orWhereHas('user', function($q) use ($search) {
                    $q->where('username', 'LIKE', "%$search%");
                });
        }

        $tickets = $tickets->paginate(getPaginate());
        return view('admin.lottery.tickets.index', compact('pageTitle', 'tickets'));
    }
}
