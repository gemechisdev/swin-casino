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
            'name'          => 'required',
            'min'           => 'required|numeric',
            'max'           => 'required|numeric',
            'instruction'   => 'required',
            'win'           => 'sometimes|required|numeric',
            'invest_back'   => 'sometimes|required',
            'trending'      => 'sometimes|required',
            'featured'      => 'sometimes|required',
            'house_edge'      => 'required|numeric|gte:0|lt:100',
            'house_edge_demo' => 'required|numeric|gte:0|lt:100',
            'level.*'       => 'sometimes|required',
            'image'         => ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ], [
            'level.0.required'  => 'Level 1 field is required',
            'level.1.required'  => 'Level 2 field is required',
            'level.2.required'  => 'Level 3 field is required',
        ]);

        $game = Game::findOrFail($id);
        $houseEdge = $this->sanitizeHouseEdge($request->house_edge, 5);
        $houseEdgeDemo = $this->sanitizeHouseEdge($request->house_edge_demo, 2);
        $winChance = $this->calculateProbableWin($game, $houseEdge, $request, $game->probable_win);
        $winChanceDemo = $this->calculateProbableWin($game, $houseEdgeDemo, $request, $game->probable_win_demo);

        $game->name              = $request->name;
        $game->min_limit         = $request->min;
        $game->max_limit         = $request->max;
        $game->house_edge        = $houseEdge;
        $game->house_edge_demo   = $houseEdgeDemo;
        $game->probable_win      = $winChance;
        $game->probable_win_demo = $winChanceDemo;
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
            'house_edge'        => 'required|numeric|gte:0|lt:100',
            'house_edge_demo'   => 'required|numeric|gte:0|lt:100',
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
        $houseEdge = $this->sanitizeHouseEdge($request->house_edge, 5);
        $houseEdgeDemo = $this->sanitizeHouseEdge($request->house_edge_demo, 2);

        $game->name              = $request->name;
        $game->min_limit         = $request->min;
        $game->max_limit         = $request->max;
        $game->invest_back       = $request->invest_back ? Status::YES : Status::NO;
        $game->trending          = $request->trending ? Status::YES : Status::NO;
        $game->featured          = $request->featured ? Status::YES : Status::NO;
        $game->instruction       = $request->instruction;
        $game->level             = $levels;
        $game->house_edge        = $houseEdge;
        $game->house_edge_demo   = $houseEdgeDemo;
        $game->probable_win      = $this->calculateKenoProbableWin($houseEdge, $request->percent);
        $game->probable_win_demo = $this->calculateKenoProbableWin($houseEdgeDemo, $request->percent);

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
            'name'          => 'required',
            'min'           => 'required|numeric',
            'max'           => 'required|numeric',
            'instruction'   => 'required',
            'trending'      => 'sometimes|required',
            'featured'      => 'sometimes|required',
            'house_edge'      => 'required|numeric|gte:0|lt:100',
            'house_edge_demo' => 'required|numeric|gte:0|lt:100',
            'level.*'       => 'required|numeric|gte:0',
            'image'         => ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ], [
            'level.*.required' => 'probable field is required',
        ]);

        $game = Game::findOrFail($id);
        $houseEdge = $this->sanitizeHouseEdge($request->house_edge, 5);
        $houseEdgeDemo = $this->sanitizeHouseEdge($request->house_edge_demo, 2);
        $probableWin = $this->calculateCrazyTimesProbableWin($houseEdge, $request->level);
        $probableWinDemo = $this->calculateCrazyTimesProbableWin($houseEdgeDemo, $request->level);

        $game->name              = $request->name;
        $game->min_limit         = $request->min;
        $game->max_limit         = $request->max;
        $game->invest_back       = $request->invest_back ? Status::YES : Status::NO;
        $game->trending          = $request->trending ? Status::YES : Status::NO;
        $game->featured          = $request->featured ? Status::YES : Status::NO;
        $game->instruction       = $request->instruction;
        $game->house_edge        = $houseEdge;
        $game->house_edge_demo   = $houseEdgeDemo;
        $game->probable_win      = $probableWin;
        $game->probable_win_demo = $probableWinDemo;
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
            'name'         => 'required',
            'min'          => 'required|numeric',
            'max'          => 'required|numeric',
            'instruction'  => 'required',
            'trending'     => 'sometimes|required',
            'featured'     => 'sometimes|required',
            'house_edge'      => 'required|numeric|gte:0|lt:100',
            'house_edge_demo' => 'required|numeric|gte:0|lt:100',
            'image'           => ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ], [
            'house_edge.required' => 'House edge field is required',
        ]);

        $houseEdge = $this->sanitizeHouseEdge($request->house_edge, 5);
        $houseEdgeDemo = $this->sanitizeHouseEdge($request->house_edge_demo, 2);
        $game                    = Game::findOrFail($id);
        $probableWin = $this->calculateDreamCatcherProbableWin($houseEdge, $game->probable_win);
        $probableWinDemo = $this->calculateDreamCatcherProbableWin($houseEdgeDemo, $game->probable_win_demo);
        $game->name              = $request->name;
        $game->min_limit         = $request->min;
        $game->max_limit         = $request->max;
        $game->invest_back       = $request->invest_back ? Status::YES : Status::NO;
        $game->trending          = $request->trending ? Status::YES : Status::NO;
        $game->featured          = $request->featured ? Status::YES : Status::NO;
        $game->instruction       = $request->instruction;
        $game->house_edge        = $houseEdge;
        $game->house_edge_demo   = $houseEdgeDemo;
        $game->probable_win      = $probableWin;
        $game->probable_win_demo = $probableWinDemo;
        
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

    private function sanitizeHouseEdge($value, $default)
    {
        $edge = is_numeric($value) ? (float) $value : $default;
        $edge = max(0, min($edge, 99.99));
        return round($edge, 2);
    }

    private function calculateProbableWin($game, $houseEdge, Request $request, $existingProbable = null)
    {
        if ($game->alias == 'number_slot') {
            return $this->calculateNumberSlotProbableWin($houseEdge, $request->level ?? [], $existingProbable);
        }

        $winPercent = $request->filled('win') ? $request->win : $game->win;
        $investBack = $request->has('invest_back') ? Status::YES : $game->invest_back;
        return $this->calculateBinaryProbableWin($houseEdge, $winPercent, $investBack);
    }

    private function calculateBinaryProbableWin($houseEdge, $winPercent, $investBack)
    {
        $multiplier = ($investBack == Status::YES ? 1 : 0) + (max((float) $winPercent, 0) / 100);
        if ($multiplier <= 0) {
            return round(max(0, min(100 - $houseEdge, 100)), 2);
        }

        return round(max(0, min(((100 - $houseEdge) / $multiplier), 100)), 2);
    }

    private function calculateKenoProbableWin($houseEdge, $percents)
    {
        $multipliers = [];
        foreach ($percents ?? [] as $percent) {
            $multipliers[] = 1 + ((float) $percent / 100);
        }

        $averageMultiplier = count($multipliers) ? array_sum($multipliers) / count($multipliers) : 1;
        return round(max(0, min(((100 - $houseEdge) / $averageMultiplier), 100)), 2);
    }

    private function calculateCrazyTimesProbableWin($houseEdge, $levels)
    {
        $multipliers = [2, 3, 6, 11];
        foreach ($levels ?? [] as $level) {
            $multipliers[] = 1 + ((float) $level / 100);
        }

        $averageMultiplier = array_sum($multipliers) / count($multipliers);
        return round(max(0, min(((100 - $houseEdge) / $averageMultiplier), 100)), 2);
    }

    private function calculateDreamCatcherProbableWin($houseEdge, $existingProbable)
    {
        $probable = [
            'one'    => round((100 - $houseEdge) / 2, 2),
            'two'    => round((100 - $houseEdge) / 3, 2),
            'five'   => round((100 - $houseEdge) / 6, 2),
            'ten'    => round((100 - $houseEdge) / 11, 2),
            'twenty' => round((100 - $houseEdge) / 21, 2),
            'forty'  => round((100 - $houseEdge) / 41, 2),
            'twox'   => (float) ($existingProbable->twox ?? 0),
            'sevenx' => (float) ($existingProbable->sevenx ?? 0),
        ];

        foreach (['one', 'two', 'five', 'ten', 'twenty', 'forty'] as $key) {
            $probable[$key] = max(0, min($probable[$key], 100));
        }

        return $probable;
    }

    private function calculateNumberSlotProbableWin($houseEdge, $levels, $existingProbable)
    {
        $levelMultipliers = [];
        foreach ($levels ?? [] as $level) {
            $levelMultipliers[] = max((float) $level, 0) / 100;
        }

        if (!count($levelMultipliers)) {
            $levelMultipliers = [0.3, 1, 3];
        }

        $averageWinMultiplier = array_sum($levelMultipliers) / count($levelMultipliers);
        $totalWinChance = $averageWinMultiplier > 0 ? round(((100 - $houseEdge) / $averageWinMultiplier), 2) : 0;
        $totalWinChance = max(0, min($totalWinChance, 100));

        $existing = (array) $existingProbable;
        $weights = [
            max((float) ($existing[1] ?? 0), 0),
            max((float) ($existing[2] ?? 0), 0),
            max((float) ($existing[3] ?? 0), 0),
        ];

        if (array_sum($weights) <= 0) {
            $weights = [1, 1, 1];
        }

        $weightSum = array_sum($weights);
        $first = round($totalWinChance * ($weights[0] / $weightSum), 2);
        $second = round($totalWinChance * ($weights[1] / $weightSum), 2);
        $third = round($totalWinChance - $first - $second, 2);
        $noWin = round(100 - ($first + $second + $third), 2);

        return [$noWin, $first, $second, $third];
    }
}
