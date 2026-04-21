<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameLog;
use App\Models\GuessBonus;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        $pageTitle = "Games";
        $games     = Game::searchable(['name'])->orderBy('id', 'desc')->get();
        return view('admin.game.index', compact('pageTitle', 'games'));
    }

    public function edit($id)
    {
        $game      = Game::findOrFail($id);
        $pageTitle = "Update " . $game->name;

        $view    = 'game_edit';
        $bonuses = null;

        $alias = ['number_guess', 'number_slot', 'roulette', 'casino_dice', 'keno', 'blackjack', 'mines', 'poker', 'crazy_times', 'dream_catcher'];
        if (in_array($game->alias, $alias)) {
            if (in_array($game->alias, ['number_guess', 'mines', 'poker'])) {
                $bonuses = GuessBonus::where('alias', $game->alias)->get();
            }
            $view = $game->alias;
        }
        return view('admin.game.' . $view, compact('pageTitle', 'game', 'bonuses'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'            => 'required',
            'min'             => 'required|numeric',
            'max'             => 'required|numeric',
            'instruction'     => 'required',
            'win'             => 'sometimes|required|numeric',
            'invest_back'     => 'sometimes|required',
            'trending'        => 'sometimes|required',
            'featured'        => 'sometimes|required',
            'house_edge'      => 'nullable|numeric|min:0|max:100',
            'house_edge_demo' => 'nullable|numeric|min:0|max:100',
            'level.*'         => 'sometimes|required',
            'chance.*'        => 'sometimes|required|numeric',
            'image'           => ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ], [
            'level.0.required'  => 'Level 1 field is required',
            'level.1.required'  => 'Level 2 field is required',
            'level.2.required'  => 'Level 3 field is required',
            'chance.0.required' => 'No win chance field required',
            'chance.1.required' => 'Double win chance field is required',
            'chance.2.required' => 'Single win chance field is required',
            'chance.3.required' => 'Triple win field is required',
            'chance.*.numeric'  => 'Chance field must be a number',
        ]);

        $game = Game::findOrFail($id);

        $houseEdge     = $request->house_edge ?? 5.00;
        $houseEdgeDemo = $request->house_edge_demo ?? 2.00;

        $investBack = $request->invest_back ? true : false;
        $win        = (float) ($request->win ?? 0);

        if (isset($request->chance)) {
            // NumberSlot: chance[] array – compute proportionally from house_edge
            $levels = $request->level ?? [100, 200, 500];

            $winChance     = $this->calculateNumberSlotChances($houseEdge, $levels);
            $winChanceDemo = $this->calculateNumberSlotChances($houseEdgeDemo, $levels);
        } else {
            $winChance     = $this->calculateProbableWin($houseEdge, $win, $investBack);
            $winChanceDemo = $this->calculateProbableWin($houseEdgeDemo, $win, $investBack);
        }

        $game->name              = $request->name;
        $game->min_limit         = $request->min;
        $game->max_limit         = $request->max;
        $game->probable_win      = $winChance;
        $game->probable_win_demo = $winChanceDemo;
        $game->house_edge        = $houseEdge;
        $game->house_edge_demo   = $houseEdgeDemo;
        $game->invest_back       = $request->invest_back ? Status::YES : Status::NO;
        $game->trending          = $request->trending ? Status::YES : Status::NO;
        $game->featured          = $request->featured ? Status::YES : Status::NO;
        $game->instruction       = $request->instruction;
        $game->short_desc        = $request->short_desc;
        $game->level             = $request->level;
        $game->win               = $request->win;

        $oldImage = $game->image;

        if ($request->hasFile('image')) {
            try {
                $game->image = fileUploader($request->image, getFilePath('game'), getFileSize('game'), $oldImage);
            } catch (\Exception $e) {
                $notify[] = ['error', 'Could not upload the Image.'];
                return back()->withNotify($notify);
            }
        }

        $game->save();

        $notify[] = ['success', 'Game updated successfully'];
        return back()->withNotify($notify);
    }

    public function gameLog(Request $request)
    {
        $pageTitle = "Game Logs";
        $logs      = GameLog::where('status', Status::ENABLE)->searchable(['user:username'])->filter(['win_status'])->with('user', 'game')->latest('id')->paginate(getPaginate());
        return view('admin.game.log', compact('pageTitle', 'logs'));
    }

    public function chanceCreate(Request $request, $alias = null)
    {
        $request->validate([
            'chance'    => 'required|array|min:1',
            'chance.*'  => 'required|integer|min:1',
            'percent'   => 'required|array',
            'percent.*' => 'required|numeric',
        ]);

        if ($request->alias == 'mines' && count($request->chance) != 20) {
            $notify[] = ['error', '20 mines commission is required'];
            return back()->withNotify($notify);
        }
        if ($request->alias == 'poker' && count($request->chance) != 10) {
            $notify[] = ['error', '10 rank commission is required'];
            return back()->withNotify($notify);
        }

        GuessBonus::where('alias', $request->alias)->delete();

        $data = [];
        for ($a = 0; $a < count($request->chance); $a++) {
            $data[] = [
                'alias'      => $alias,
                'chance'     => $request->chance[$a],
                'percent'    => $request->percent[$a],
                'status'     => Status::ENABLE,
                'created_at' => now(),
            ];
        }

        GuessBonus::insert($data);

        $notify[] = ['success', 'Chance bonus Create Successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $game = Game::findOrFail($id);

        if ($game->status == Status::ENABLE) {
            $game->status = Status::DISABLE;
            $notify[]     = ['success', $game->name . ' disabled successfully'];
        } else {
            $game->status = Status::ENABLE;
            $notify[]     = ['success', $game->name . ' enabled successfully'];
        }

        $game->save();
        return back()->withNotify($notify);
    }

    public function kenoUpdate(Request $request, $id)
    {
        $request->validate([
            'name'              => 'required',
            'min'               => 'required|numeric',
            'max'               => 'required|numeric',
            'instruction'       => 'required',
            'invest_back'       => 'sometimes|required',
            'trending'          => 'sometimes|required',
            'featured'          => 'sometimes|required',
            'max_select_number' => 'required|integer|gte:4',
            'level.*'           => 'required|integer',
            'percent.*'         => 'required|numeric',
            'house_edge'        => 'nullable|numeric|min:0|max:100',
            'house_edge_demo'   => 'nullable|numeric|min:0|max:100',
            'image'             => ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ], [
            'level.*.required'   => 'Level field is required',
            'percent.*.required' => 'Commission field required',
            'percent.*.numeric'  => 'Commission field must be a number',
        ]);

        $game      = Game::findOrFail($id);
        $maxSelect = [
            'max_select_number' => $request->max_select_number,
        ];
        for ($i = 0; $i < count($request->percent); $i++) {
            $level[] = [
                'level'   => $request->level[$i],
                'percent' => $request->percent[$i],
            ];
        }
        $levels['levels'] = $level;
        $levels           = array_merge($maxSelect, $levels);

        $houseEdge     = $request->house_edge ?? 5.00;
        $houseEdgeDemo = $request->house_edge_demo ?? 2.00;

        $avgCommission = count($request->percent) > 0
            ? array_sum($request->percent) / count($request->percent)
            : 100;

        $winChance     = $this->calculateProbableWin($houseEdge, $avgCommission, true);
        $winChanceDemo = $this->calculateProbableWin($houseEdgeDemo, $avgCommission, true);

        $game->name              = $request->name;
        $game->min_limit         = $request->min;
        $game->max_limit         = $request->max;
        $game->invest_back       = $request->invest_back ? Status::YES : Status::NO;
        $game->trending          = $request->trending ? Status::YES : Status::NO;
        $game->featured          = $request->featured ? Status::YES : Status::NO;
        $game->instruction       = $request->instruction;
        $game->level             = $levels;
        $game->probable_win      = $winChance;
        $game->probable_win_demo = $winChanceDemo;
        $game->house_edge        = $houseEdge;
        $game->house_edge_demo   = $houseEdgeDemo;

        if ($request->hasFile('image')) {
            try {
                $game->image = fileUploader($request->image, getFilePath('game'), getFileSize('game'), @$game->image);
            } catch (\Exception $e) {
                $notify[] = ['error', 'Could not upload the Image.'];
                return back()->withNotify($notify);
            }
        }
        $game->save();

        $notify[] = ['success', 'Game updated successfully'];
        return back()->withNotify($notify);
    }
    public function crazyTimesUpdate(Request $request, $id)
    {
        $request->validate([
            'name'            => 'required',
            'min'             => 'required|numeric',
            'max'             => 'required|numeric',
            'instruction'     => 'required',
            'trending'        => 'sometimes|required',
            'featured'        => 'sometimes|required',
            'house_edge'      => 'nullable|numeric|min:0|max:100',
            'house_edge_demo' => 'nullable|numeric|min:0|max:100',
            'level.*'         => 'required|numeric|gte:0',
            'image'           => ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ], [
            'level.*.required' => 'probable field is required',
        ]);

        $game = Game::findOrFail($id);

        $houseEdge     = $request->house_edge ?? 5.00;
        $houseEdgeDemo = $request->house_edge_demo ?? 2.00;

        $levels = $request->level ?? [200, 300, 500, 1000];

        // CrazyTimes average payout multiplier across all 8 bet types
        // Fixed bets: 1→2x, 2→3x, 5→6x, 10→11x; special bets use level[]
        $avgMultiplier = (2 + 3 + 6 + 11
            + (1 + ($levels[0] ?? 200) / 100)
            + (1 + ($levels[1] ?? 300) / 100)
            + (1 + ($levels[2] ?? 500) / 100)
            + (1 + ($levels[3] ?? 1000) / 100)) / 8;

        $winChance     = min(99.99, round((100 - $houseEdge) / $avgMultiplier, 2));
        $winChanceDemo = min(99.99, round((100 - $houseEdgeDemo) / $avgMultiplier, 2));

        $game->name              = $request->name;
        $game->min_limit         = $request->min;
        $game->max_limit         = $request->max;
        $game->invest_back       = $request->invest_back ? Status::YES : Status::NO;
        $game->trending          = $request->trending ? Status::YES : Status::NO;
        $game->featured          = $request->featured ? Status::YES : Status::NO;
        $game->instruction       = $request->instruction;
        $game->probable_win      = $winChance;
        $game->probable_win_demo = $winChanceDemo;
        $game->house_edge        = $houseEdge;
        $game->house_edge_demo   = $houseEdgeDemo;
        $game->level             = $request->level;

        if ($request->hasFile('image')) {
            try {
                $game->image = fileUploader($request->image, getFilePath('game'), getFileSize('game'), @$game->image);
            } catch (\Exception $e) {
                $notify[] = ['error', 'Could not upload the Image.'];
                return back()->withNotify($notify);
            }
        }
        $game->save();

        $notify[] = ['success', 'Game updated successfully'];
        return back()->withNotify($notify);
    }

    public function dreamCatcherUpdate(Request $request, $id)
    {
        $request->validate([
            'name'            => 'required',
            'min'             => 'required|numeric',
            'max'             => 'required|numeric',
            'instruction'     => 'required',
            'trending'        => 'sometimes|required',
            'featured'        => 'sometimes|required',
            'house_edge'      => 'nullable|numeric|min:0|max:100',
            'house_edge_demo' => 'nullable|numeric|min:0|max:100',
            'image'           => ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        $houseEdge     = $request->house_edge ?? 5.00;
        $houseEdgeDemo = $request->house_edge_demo ?? 2.00;

        // Each DreamCatcher segment has an independent win probability.
        // Total payout per segment = multiplier * invest + invest (invest returned on win).
        // RTP per bet = prob * (multiplier + 1) = (100 - house_edge) / 100
        // => prob = (100 - house_edge) / (multiplier + 1)
        $probableWin     = $this->calculateDreamCatcherProbabilities($houseEdge);
        $probableWinDemo = $this->calculateDreamCatcherProbabilities($houseEdgeDemo);

        $game                    = Game::findOrFail($id);
        $game->name              = $request->name;
        $game->min_limit         = $request->min;
        $game->max_limit         = $request->max;
        $game->invest_back       = $request->invest_back ? Status::YES : Status::NO;
        $game->trending          = $request->trending ? Status::YES : Status::NO;
        $game->featured          = $request->featured ? Status::YES : Status::NO;
        $game->instruction       = $request->instruction;
        $game->probable_win      = $probableWin;
        $game->probable_win_demo = $probableWinDemo;
        $game->house_edge        = $houseEdge;
        $game->house_edge_demo   = $houseEdgeDemo;

        if ($request->hasFile('image')) {
            try {
                $game->image = fileUploader($request->image, getFilePath('game'), getFileSize('game'), @$game->image);
            } catch (\Exception $e) {
                $notify[] = ['error', 'Could not upload the Image.'];
                return back()->withNotify($notify);
            }
        }

        $game->save();

        $notify[] = ['success', 'Game updated successfully'];
        return back()->withNotify($notify);
    }

    /**
     * Calculate probable_win from house_edge for simple fixed-payout games.
     *
     * @param float $houseEdge  House edge percentage (0-100)
     * @param float $win        Win bonus percentage stored in game->win
     * @param bool  $investBack Whether the original invest is returned on win
     * @return float            Winning probability (0-100)
     */
    private function calculateProbableWin(float $houseEdge, float $win, bool $investBack): float
    {
        $rtp = (100 - $houseEdge) / 100;

        if ($win <= 0) {
            return round($rtp * 100, 2);
        }

        if ($investBack) {
            // On win: player receives invest + invest*(win/100) = invest*(1 + win/100)
            // RTP = prob * (1 + win/100)  =>  prob = RTP / (1 + win/100)
            $prob = $rtp / (1 + $win / 100);
        } else {
            // On win: player receives invest*(win/100) only
            // RTP = prob * (win/100)  =>  prob = RTP / (win/100) = RTP * 100 / win
            $prob = $rtp * 100 / $win;
        }

        return min(99.99, max(0.01, round($prob * 100, 2)));
    }

    /**
     * Calculate chance[] array for NumberSlot from house_edge and level bonuses.
     * Uses a 5:2:1 ratio for single:double:triple wins.
     *
     * @param float $houseEdge House edge percentage (0-100)
     * @param array $levels    [single_bonus%, double_bonus%, triple_bonus%]
     * @return array           [no_win%, single%, double%, triple%]
     */
    private function calculateNumberSlotChances(float $houseEdge, array $levels): array
    {
        $rtp          = (100 - $houseEdge) / 100;
        $singleBonus  = (float) ($levels[0] ?? 100);
        $doubleBonus  = (float) ($levels[1] ?? 200);
        $tripleBonus  = (float) ($levels[2] ?? 500);

        // With proportions 5:2:1 for single:double:triple
        // RTP = k*(5*singleBonus + 2*doubleBonus + tripleBonus) / 10000
        $denominator = 5 * $singleBonus + 2 * $doubleBonus + $tripleBonus;

        if ($denominator <= 0) {
            return [70, 15, 10, 5];
        }

        $k      = $rtp * 10000 / $denominator;
        $single = round($k * 5, 2);
        $double = round($k * 2, 2);
        $triple = round($k * 1, 2);
        $noWin  = max(0, round(100 - $single - $double - $triple, 2));

        return [$noWin, $single, $double, $triple];
    }

    /**
     * Calculate per-segment probabilities for DreamCatcher from house_edge.
     * Each segment: total return = (multiplier + 1) * invest (invest returned on win).
     * RTP per bet = prob * (multiplier + 1) = (100 - house_edge) / 100
     *
     * @param float $houseEdge House edge percentage (0-100)
     * @return array           Associative array of segment probabilities
     */
    private function calculateDreamCatcherProbabilities(float $houseEdge): array
    {
        $rtp = (100 - $houseEdge) / 100;

        return [
            'one'    => min(99.99, round($rtp / (1 + 1) * 100, 2)),
            'two'    => min(99.99, round($rtp / (2 + 1) * 100, 2)),
            'five'   => min(99.99, round($rtp / (5 + 1) * 100, 2)),
            'ten'    => min(99.99, round($rtp / (10 + 1) * 100, 2)),
            'twenty' => min(99.99, round($rtp / (20 + 1) * 100, 2)),
            'forty'  => min(99.99, round($rtp / (40 + 1) * 100, 2)),
            'twox'   => 10.00,
            'sevenx' => 5.00,
        ];
    }
}
