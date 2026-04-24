* * * * *

AddisWin --- New Game Addition: Deep Implementation Guide
=======================================================

> **Scope:** Everything you need to know to add a fully working new game to the AddisWin Casino platform --- from the database seed row all the way through to the admin panel, player UI, API layer, and operational recovery.

* * * * *

1\. System Architecture Overview
--------------------------------

Before touching any file, you must understand how all the layers speak to each other. The flow for a game round is:

Code

```
Browser / Mobile App
        │
        ▼
[Laravel Route] → user.play.game / user.play.invest / user.play.end
        │
        ▼
[PlayController] → resolves alias, creates GamePlayer instance
        │
        ▼
[GamePlayer] → looks up alias in $games map → instantiates Game subclass
        │
        ▼
[Game subclass] → gameResult() / complete() / customCompleteLogic()
        │
        ▼
[Database] → game_logs, transactions, users, gameplay_bonus_logs
        │
        ▼
[JSON Response] → browser JS / mobile JSON consumer

```

Every layer is coupled by a single string: **the alias**. If the alias is mismatched anywhere in that chain the round breaks silently or with a cryptic error.

* * * * *

2\. Data Model Reference
------------------------

### 2.1 `games` table --- key columns

| Column | Type | Purpose |
| --- | --- | --- |
| `id` | bigint PK | Internal ID (some legacy blade views use hardcoded IDs like 12, 14 --- keep this in mind) |
| `name` | varchar | Display name shown everywhere |
| `alias` | varchar | **The master key.** Must be snake_case and match exactly: DB row ↔ Game class ↔ GamePlayer map ↔ blade filename ↔ route parameter |
| `image` | varchar | Filename stored under `getFilePath('game')` |
| `status` | tinyint | 0=disabled, 1=enabled. `Game::active()` scope filters on this |
| `probable_win` | decimal / JSON | Server-side win probability (0--100). For simple binary games this is a single decimal. For complex games (number_slot, roulette, dream_catcher) it is a JSON object with named keys. Cast to `object` by the `Game` model |
| `probable_win_demo` | decimal / JSON | Same as above but for demo mode |
| `house_edge` | decimal(5,2) | Admin-facing input that drives `probable_win` calculation |
| `house_edge_demo` | decimal(5,2) | Same for demo |
| `win` | decimal | Win bonus percentage paid on top of invest (used by default complete logic) |
| `invest_back` | tinyint | 1 = original stake is returned on win before paying bonus |
| `min_limit` | decimal | Minimum bet amount enforced in `Game::fallback()` |
| `max_limit` | decimal | Maximum bet amount enforced in `Game::fallback()` |
| `level` | JSON | Game-specific config blob (keno levels, number_slot multipliers, etc.) Cast to object |
| `instruction` | longtext | HTML shown in the in-game instruction modal |
| `short_desc` | text | Plain-text summary (used by blackjack, exposed via API) |
| `trending` | tinyint | 1 = included in `gamesTrending` API response |
| `featured` | tinyint | 1 = included in `gamesFeatured` API response |

### 2.2 `game_logs` table --- key columns

| Column | Purpose |
| --- | --- |
| `user_id` | FK to users |
| `game_id` | FK to games |
| `demo_play` | 1 = demo round, balance changes go to `demo_balance` |
| `status` | 0 = running (round in progress), 1 = finished, 2 = auto-recovered by cron |
| `win_status` | 0 = loss, 1 = win, 2 = push/tie |
| `user_select` | What the player chose (plain string or JSON array stored via `$this->userSelect`) |
| `result` | What the server determined (plain, JSON-encoded, or encrypted --- depends on game) |
| `invest` | Bet amount |
| `win_amo` | Amount credited on win |
| `mines` | Mine count for the Mines game (0 for all others) |
| `mine_available` | Remaining safe cells for Mines (0 for all others) |
| `gold_count` | Gold cells found in Mines (0 for all others) |

### 2.3 `guess_bonuses` table

Used by `number_guess`, `mines`, and `poker` to store per-chance/rank payout multipliers. Schema: `alias`, `chance` (integer rank), `percent` (decimal bonus), `status`.

### 2.4 `gameplay_bonus_logs` table

Tracks whether a user has already received their first-N-plays bonus for a given `game_id`. Checked inside `Game::checkAndGiveBonus()` at end of every round.

### 2.5 Schema management

**There are no Laravel migrations in this repo.** All schema changes must be made directly in `install/database.sql` --- either as `CREATE TABLE` / `INSERT INTO` at the appropriate position, or as `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` at the end of the file.

* * * * *

3\. Phase A --- Define the Game Contract
--------------------------------------

Before writing a single line of code, answer these questions:

**3.1 Alias** Choose a snake_case string with no spaces. This exact string must appear in five different places. Example: `baccarat`.

**3.2 Win model --- simple or custom?**

| Model | When to use | Base class behaviour |
| --- | --- | --- |
| **Simple binary** | Single bet resolves immediately (HeadTail, AndarBahar, RockPaperScissors) | Default `complete()` handles everything; just implement `gameResult()` |
| **Custom win amount** | The win amount is not a flat percentage but depends on the result (ColorPrediction numbers, Keno match count) | Override `$winLossData['win_amount']` in `gameResult()`; base code uses it directly |
| **Multi-step / state machine** | Multiple HTTP calls needed per round (Blackjack hit/stay, Mines click/cashout, Poker deal/call/fold) | Set `$hasCustomCompleteLogic = true` and implement `customCompleteLogic($gameLog)` |

**3.3 Result storage format**

The base `invest()` method in `Game.php` has this logic deciding how `result` is stored:

PHP

```
$gameLog->result =
    in_array($this->game->alias, ['number_slot','roulette','keno','poker','blackjack','pai_gow_poker'])
        ? json_encode($gameResult['result'])
        : ($this->game->id == 4
            ? decrypt($gameResult['result'])
            : $gameResult['result']);

```

-   Your new alias is **not** in that array, so result is stored as-is (plain string or whatever you return).
-   If you need JSON storage (e.g., to store multiple card values), **you must add your alias to that array**. This is the only hardcoded list you need to update inside the engine itself.
-   If your result is encrypted (like Blackjack), decrypt it before storing.

**3.4 `user_select` storage**

The base class sets `$this->userSelect = @$this->request->choose` automatically. Override `$this->userSelect` in `gameResult()` if you need a different value stored (Keno does this to store the chosen number array as JSON).

* * * * *

4\. Phase B --- Backend Engine Class
----------------------------------

**Location:** `core/app/Games/{ClassName}.php`

**Namespace:** `App\Games`

**Extends:** `App\Games\Game` (abstract)

### 4.1 Mandatory properties

PHP

```
protected $alias = 'your_alias'; // snake_case, matches DB games.alias exactly

```

### 4.2 Optional property overrides

PHP

```
// Show result in the /invest response (true by default). Set false for multi-step games.
protected $resultShowOnStart = false;

// Validation rules merged with global 'invest' rule before play() runs
protected $extraValidationRule = [
    'choose' => 'required|in:option_a,option_b',
];

// Validation rules applied at the /end endpoint
protected $extraEndValidationRule = [
    'game_log_id' => 'required|integer',
    'type'        => 'required|in:action_a,action_b',
];

// Keys merged into the /invest JSON response alongside game_log_id and balance
protected $extraResponseOnStart = [];

// Keys merged into the /end JSON response alongside message/type/result/bal
protected $extraResponseOnEnd = [];

// Trigger customCompleteLogic instead of default payout flow
protected $hasCustomCompleteLogic = true;

```

### 4.3 `gameResult()` --- the only required method

Must return an array with at minimum:

PHP

```
return [
    'win_status' => Status::WIN,  // or Status::LOSS or Status::PUSH
    'result'     => $yourResult,  // stored in game_logs.result
];

```

Optionally also:

PHP

```
'win_amount' => $calculatedAmount, // overrides the default win% calculation at complete time

```

Inside `gameResult()`:

1.  Read `$this->demoPlay` to pick `probable_win` vs `probable_win_demo`.
2.  Generate a random number: `$random = mt_rand(0, 10000) / 100` → range 0.00--100.00.
3.  If `$random <= $probableWin` → player wins; otherwise → loss.
4.  Compute and return result and win_status.
5.  Populate `$this->extraResponseOnStart` with any data the frontend JS needs immediately after invest (e.g., dealt cards).
6.  Optionally set `$this->userSelect` to a custom value before returning.

### 4.4 `customCompleteLogic($gameLog)` --- for multi-step games

Called by `Game::complete()` when `$hasCustomCompleteLogic = true`. Receives the running `GameLog` Eloquent model.

Must return one of two structures:

PHP

```
// Option A: handle everything yourself, skip base payout logic entirely
return [
    'should_return' => true,
    'data'          => $responseArray, // sent as JSON directly to browser
];

// Option B: finish your state changes but let base logic do payout + balance
return [
    'should_return' => false,
];

```

When `should_return = false`, the base `complete()` continues with:

-   Win bonus calculation using `$gameLog->win_status` and `$this->game->win`
-   Balance update + transaction creation
-   `$this->extraResponseOnEnd` merged into response
-   `checkAndGiveBonus()` fired

### 4.5 Registration in `GamePlayer`

**Location:** `core/app/Games/GamePlayer.php`

Add one line to the `$games` array:

PHP

```
private $games = [
    // ... existing entries ...
    'your_alias' => YourGameClass::class,
];

```

**This step is mandatory.** Without it, both `/invest` and `/end` throw "The game your_alias not found".

* * * * *

5\. Phase C --- Database Integration
----------------------------------

### 5.1 Insert the `games` row

Add at the end of the `INSERT INTO games` block in `install/database.sql`:

SQL

```
INSERT INTO `games`
  (name, alias, image, status, trending, featured, min_limit, max_limit, win,
   invest_back, probable_win, probable_win_demo, level, instruction, short_desc, house_edge, house_edge_demo)
VALUES
  ('Your Game Name', 'your_alias', 'placeholder.png', 1, 0, 0,
   10.00, 1000.00, 95.00,
   0,                      -- invest_back: 0=no, 1=yes
   '47.62',                -- probable_win: (100 - house_edge) / (1 + win/100)
   '49.00',                -- probable_win_demo: same with house_edge_demo
   NULL,                   -- level: NULL unless you need JSON config
   '<p>Instructions HTML here</p>',
   'Short plain-text description.',
   5.00,                   -- house_edge in percent
   2.00);                  -- house_edge_demo in percent

```

**`probable_win` calculation for a simple binary game (invest_back=0):**

Code

```
probable_win = (100 - house_edge) / (1 + win_percent / 100)

```

Example: house_edge=5, win=95 → probable_win = 95 / 1.95 ≈ **48.72**

**`probable_win` calculation with invest_back=1:**

Code

```
probable_win = (100 - house_edge) / (2 + win_percent / 100)

```

### 5.2 Insert `guess_bonuses` rows (if applicable)

Only needed if your game uses chance-based multipliers (like Mines per-mine-count, Poker per-rank, NumberGuess per-range). For a simple binary game this is unnecessary.

SQL

```
INSERT INTO `guess_bonuses` (alias, chance, percent, status) VALUES
  ('your_alias', 1, 50.00, 1),
  ('your_alias', 2, 100.00, 1);

```

* * * * *

6\. Phase D --- Player UI (Web)
-----------------------------

### 6.1 Blade file locations

You must create **two** files --- one per theme:

Code

```
core/resources/views/templates/basic/user/games/your_alias.blade.php
core/resources/views/templates/sunfyre/user/games/your_alias.blade.php

```

The `PlayController::playGame()` method resolves the view as:

PHP

```
view('Template::user.games.' . $alias, compact('game', 'pageTitle', 'isDemo', 'balance'))

```

All four variables (`$game`, `$pageTitle`, `$isDemo`, `$balance`) are always available.

### 6.2 Blade structure --- required elements

**Form wiring (`#game`):**

HTML

```
<form id="game" method="post">
    @csrf
    <input name="invest" type="number" step="any" required>
    <input name="choose" type="hidden">   {{-- your player choice --}}
    {{-- Any other hidden inputs your game needs --}}
</form>

```

The global `game.js` hooks into `#game`'s `submit` event and POSTs all serialized fields to `investUrl`.

**Balance display:**

HTML

```
<span class="bal">{{ showAmount($balance, currencyFormat: false) }}</span>

```

The `bal` class is updated by `game.js` after each round.

**Win/loss popup** (already handled by `game.js::setPopup()` --- no extra HTML needed unless you want to suppress the footer):

js

```
winLossPopupFooterDisplay = false; // add this if your result shouldn't show in popup

```

**Instruction modal** (identical across all games):

HTML

```
<div class="modal fade" id="exampleModalCenter">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content section--bg">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Game Rule')</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">@php echo $game->instruction @endphp</div>
        </div>
    </div>
</div>

```

### 6.3 Script wiring --- required `@push('script')` block

js

```
@push('script')
<script>
"use strict";

investUrl  = "{{ route('user.play.invest', ['your_alias', @$isDemo]) }}";
gameEndUrl = "{{ route('user.play.end',    ['your_alias', @$isDemo]) }}";
audioAssetPath = `{{ asset('assets/audio') }}`;

// Override success() if you need custom rendering after the game ends.
// Default game.js success() calls setPopup(data) + updates .bal.

function success(data) {
    // Custom post-game rendering
    // Always call setPopup(data) for the standard win/loss popup
    setPopup(data);
}

// Override startGame() if the invest response needs custom handling
function startGame(data) {
    // data.game_log_id, data.balance, plus any extraResponseOnStart keys
}
</script>
@endpush

```

**Mandatory script includes:**

HTML

```
@push('script-lib')
    <script src="{{ asset('assets/global/js/soundControl.js') }}"></script>
    <script src="{{ asset('assets/global/js/game/game.js') }}"></script>
    {{-- Add your custom game JS here if needed --}}
@endpush

```

### 6.4 `game.js` core flow (what happens automatically)

When player submits `#game`:

1.  `playGame(data, music)` → plays click sound → `beforeProcess()` → `game(data)`
2.  `game(data)` → `$.ajax POST investUrl` → on success → `startGame(data)`
3.  If `data.errors` or `data.error` → `checkErrors()` → `notify('error', ...)` → returns early
4.  `startGame(data)` must call `complete(data)` (or your custom code calls `endGame(data)`) to settle
5.  `complete(data)` → `$.ajax POST gameEndUrl` with `{game_log_id}` → on success → `gameFinish(data, timerA)`
6.  `gameFinish()` → `setTimeout → success(data)` (1800ms delay for animation)
7.  Default `success()` calls `setPopup(data)` which shows the win/loss modal

For **single-step games** (result visible immediately after invest): call `complete(data)` at the end of your `startGame()` override, or let the default `startGame` flow call `endGame(data)`.

For **multi-step games**: defer the `complete(data)` call until the player takes their final action.

### 6.5 Assets

Game-specific images should go under the active template path:

Code

```
assets/images/basic/images/games/your_game_image.png
assets/images/sunfyre/images/games/your_game_image.png

```

Reference them in blade as:

PHP

```
asset(activeTemplate(true) . 'images/games/your_game_image.png')

```

Audio files referenced from `audioAssetPath` must exist under:

Code

```
assets/audio/your_sound.wav

```

* * * * *

7\. Phase E --- Admin Panel Integration
-------------------------------------

### 7.1 Default edit flow (works for any game with standard settings)

`GameController::edit()` checks the alias against a list:

PHP

```
$alias = ['number_guess','number_slot','roulette','casino_dice','keno','blackjack','mines','poker','crazy_times','dream_catcher'];
if (in_array($game->alias, $alias)) {
    $view = $game->alias; // renders admin/game/{alias}.blade.php
} else {
    $view = 'game_edit';  // renders admin/game/game_edit.blade.php
}

```

**For a standard new game:** do nothing. It automatically renders `admin/game/game_edit.blade.php`, which supports: name, image, house edge (real + demo), win%, invest_back toggle, min/max limits, trending/featured toggles, and the instruction HTML editor. This covers the vast majority of new games.

### 7.2 Custom admin edit (only if your game needs non-standard config)

Create `core/resources/views/admin/game/your_alias.blade.php` and:

1.  Add `'your_alias'` to the `$alias` array in `GameController::edit()`.
2.  Add a dedicated `POST` route in `core/routes/admin.php` within the `game.` prefix group:

    PHP

    ```
    Route::post('your-alias-update/{id}', 'yourAliasUpdate')->name('your.alias.update');

    ```

3.  Implement the handler method in `GameController`. Follow the existing pattern:
    -   Validate inputs
    -   Call `sanitizeHouseEdge()` (already a private method)
    -   Compute `probable_win` via a new private calculator method
    -   Save `$game->level` with any custom JSON config
    -   Handle image upload via `fileUploader()`

### 7.3 `probable_win` calculation --- house edge math

The admin always inputs `house_edge` (a percentage). The engine always works with `probable_win` (the server-side win probability, 0--100). The relationship for a **simple binary game**:

Code

```
probable_win = (100 - house_edge) / multiplier

```

Where `multiplier = (invest_back ? 1 : 0) + (win_percent / 100)`.

The `calculateBinaryProbableWin()` private method in `GameController` already implements this. Call it with your game's parameters when saving.

For complex games with variable payouts, implement a custom calculator following the existing `calculateKenoProbableWin()` / `calculateCrazyTimesProbableWin()` patterns.

### 7.4 Game log rendering

The admin game log at `admin/game/log.blade.php` renders `result` with:

PHP

```
@if (gettype(json_decode($log->result)) == 'array')
    {{ implode(', ', json_decode($log->result)) }}
@else
    @if (in_array($log->game_id, [12, 14]))  // ← hardcoded Blackjack + Poker IDs
        {{ implode(', ', decrypt($log->result)) }}
    @else
        {{ __($log->result) }}
    @endif
@endif

```

If your game stores JSON arrays as the result, they display automatically. If you store plain strings, they display as-is. **Do not store Eloquent-encrypted result** unless you add your `game_id` to the `in_array` check in both admin and user log blades --- and that requires knowing the ID at code time.

* * * * *

8\. Phase F --- API / Mobile Integration
--------------------------------------

### 8.1 Core play flow (works automatically)

`Api\PlayController` delegates invest and end to `GamePlayer` exactly like the web controller. The `fromApi = true` flag causes the base engine to use `responseSuccess()` / `responseError()` helpers instead of `response()->json()`. No changes needed for the play flow.

### 8.2 Game screen metadata endpoint

`Api\PlayController::playGame()` returns game metadata before the player bets. For most games the default response is sufficient:

JSON

```
{
  "game": { ...game row... },
  "balance": "100.00",
  "imagePath": null,
  "winChance": null,
  "winPercent": [],
  "gesBon": [],
  "pokerImg": null,
  "shortDesc": null,
  "cardFindingImgName": [],
  "cardFindingImgPath": null,
  "isDemo": null
}

```

If your game needs extra data (card image paths, bonus tables, multiplier grids), add an `if ($game->alias == 'your_alias')` block inside `Api\PlayController::playGame()` and include the extra keys in the final `responseSuccess()` call.

### 8.3 Dashboard lists

-   All active games (`Game::active()->get()`) appear in the `games` list --- no code change needed.
-   Mark DB row `trending = 1` to appear in `gamesTrending`.
-   Mark DB row `featured = 1` to appear in `gamesFeatured`.
-   Image served from `asset(getFilePath('game'))`.

* * * * *

9\. Phase G --- Logs & Result Format Compatibility
------------------------------------------------

### 9.1 `user_select` display

In both admin and user log blades:

PHP

```
@if (gettype(json_decode($log->user_select)) == 'array')
    {{ implode(', ', json_decode($log->user_select)) }}
@else
    {{ __($log->user_select ?? 'N/A') }}
@endif

```

-   If you store `user_select` as a plain string (e.g., `'player'`, `'andar'`) → displays as-is. ✅
-   If you store it as a JSON array (e.g., Keno's `json_encode([12, 45, 67])`) → displays as comma-separated. ✅
-   If you store `null` → displays `N/A`. ✅

### 9.2 `result` display

PHP

```
@if (gettype(json_decode($log->result)) == 'array')
    {{ implode(', ', json_decode($log->result)) }}
@else
    @if ($log->game->alias == 'mines')
        @lang('N/A')
    @else
        @if (in_array($log->game_id, [12, 14]))
            {{ implode(', ', decrypt($log->result)) }}
        @else
            {{ __($log->result) }}
        @endif
    @endif
@endif

```

The `mines` alias is hardcoded to `N/A` because its result field is a raw random float, not a meaningful display value. If your game stores results that are not human-readable (raw floats, encrypted blobs), add a similar alias-specific `@elseif` block.

* * * * *

10\. Phase H --- Operational Safeguards
-------------------------------------

### 10.1 Incomplete game recovery

The cron job `incompleteGame` (runs every few minutes) fetches all `game_logs` with `status = 0` (running) that are older than 2 minutes, and for each:

1.  Adds `$game->invest` back to the user's balance.
2.  Creates a transaction with `remark = 'invest_return'`.
3.  Sets `game_log.status = 2` (auto-recovered, distinct from finished=1).

**Implications for your game:**

-   For **single-step games**: the invest is deducted, the round resolves, and end is called within the same user interaction. The 2-minute window is unlikely to trigger. ✅
-   For **multi-step games**: if the user abandons mid-round (closes browser after Blackjack deal), the cron will refund the invest. Your `customCompleteLogic()` must check `$gameLog->status` before processing to avoid double-crediting if the cron already recovered.

### 10.2 Running game lock

`Game::fallback()` enforces:

PHP

```
$running = GameLog::where('status', 0)
    ->where('user_id', $this->user->id)
    ->where('game_id', $this->game->id)
    ->first();

if ($running) {
    return ['error' => '1 game is in-complete. Please wait'];
}

```

This is per `game_id` --- a user can have one running round of Baccarat and one of Blackjack simultaneously, but not two of the same game. Design your completion flow with this in mind: always mark `status = GAME_FINISHED` when a round resolves, including on loss/fold paths.

### 10.3 Demo vs real balance

The `$this->demoPlay` flag (set by `GamePlayer` from the route's `{demo?}` parameter) controls:

-   Which `probable_win` value is used
-   Whether `$user->balance` or `$user->demo_balance` is debited/credited
-   Whether `Transaction` records are created (no transactions for demo play)

Your `gameResult()` must always read the correct probability:

PHP

```
$probableWin = $this->demoPlay
    ? $this->game->probable_win_demo
    : $this->game->probable_win;

```

If you implement custom win-amount payouts inside `customCompleteLogic()`, you must also branch on `$this->demoPlay`:

PHP

```
if ($this->demoPlay) {
    $user->demo_balance += $winAmount;
} else {
    $user->balance += $winAmount;
    // create Transaction record
}

```

* * * * *

11\. Phase I --- Pre-Release Validation Checklist
-----------------------------------------------

Work through these in order on a development environment:

### 11.1 Database

-   [ ]  `games` row inserted with correct `alias`, `status=1`, valid `min_limit`/`max_limit`
-   [ ]  `probable_win` formula verified against `house_edge` (see Phase C math)
-   [ ]  `guess_bonuses` rows inserted if required

### 11.2 Backend engine

-   [ ]  Game class file exists at `core/app/Games/YourClass.php`
-   [ ]  `$alias` property matches DB row exactly
-   [ ]  `GamePlayer::$games` map entry added
-   [ ]  `gameResult()` returns valid `win_status` and `result`
-   [ ]  Result format (plain / JSON / encrypted) matches what `invest()` stores and log blades display

### 11.3 Web UI

-   [ ]  Both `basic` and `sunfyre` blade files exist
-   [ ]  `investUrl` and `gameEndUrl` reference correct alias
-   [ ]  `#game` form has `name="invest"` input and `@csrf`
-   [ ]  `.bal` span exists and is updated after each round
-   [ ]  Win/loss popup appears and shows correct status
-   [ ]  Demo mode shows `demo_balance` and doesn't create transactions

### 11.4 Admin panel

-   [ ]  Game appears in `admin/game/index` list
-   [ ]  Edit page loads (either `game_edit` generic or custom blade)
-   [ ]  Save updates `house_edge`, name, image, limits successfully
-   [ ]  Game log shows in `admin/game/log` with readable `user_select` and `result`
-   [ ]  Status toggle enables/disables game correctly

### 11.5 API / Mobile

-   [ ]  `GET /api/play/{alias}` returns 200 with game metadata
-   [ ]  `POST /api/play/invest/{alias}` deducts balance and returns `game_log_id`
-   [ ]  `POST /api/play/end/{alias}` settles round and returns correct `win_status`
-   [ ]  Game visible in `games`, `gamesTrending` (if trending=1), `gamesFeatured` (if featured=1)

### 11.6 Operational

-   [ ]  Run `incompleteGame` cron manually after creating an orphaned `game_logs` row → confirms refund is applied
-   [ ]  Verify two simultaneous rounds of the same game are blocked by the running-game lock
-   [ ]  Verify win transaction posted with `remark = 'Win_Bonus'`
-   [ ]  Verify invest transaction posted with `remark = 'invest'` and `-` trx_type
-   [ ]  Verify invest_back transaction posted (if `invest_back=1`) with `remark = 'invest_back'`

* * * * *

12\. Critical Alias Consistency Table
-------------------------------------

This table summarises every place the alias must appear, identically, with no exceptions:

| Location | Required Value | File |
| --- | --- | --- |
| `games.alias` DB column | `your_alias` | `install/database.sql` |
| `Game::$alias` property | `'your_alias'` | `core/app/Games/YourClass.php` |
| `GamePlayer::$games` key | `'your_alias'` | `core/app/Games/GamePlayer.php` |
| Blade filename (basic) | `your_alias.blade.php` | `core/resources/views/templates/basic/user/games/` |
| Blade filename (sunfyre) | `your_alias.blade.php` | `core/resources/views/templates/sunfyre/user/games/` |
| `investUrl` route param | `'your_alias'` | both blade files |
| `gameEndUrl` route param | `'your_alias'` | both blade files |
| Admin `GameController::edit()` custom alias array | `'your_alias'` | only if using custom admin blade |
| `Game::complete()` alias checks (e.g., keno array) | add if needed | `core/app/Games/Game.php` |
| `Game::invest()` json_encode alias array | add if needed | `core/app/Games/Game.php` |
| Admin/user log blade alias checks | add if needed | `core/resources/views/admin/game/log.blade.php`, `user/game_log.blade.php` |

* * * * *

13\. Common Mistakes & How to Avoid Them
----------------------------------------

| Mistake | Symptom | Fix |
| --- | --- | --- |
| Alias missing from `GamePlayer::$games` | `POST /invest` or `/end` throws uncaught Exception "game not found" | Add entry to the `$games` array |
| Blade file name doesn't match alias | `PlayController@playGame` throws `View not found` | Rename blade to exact alias |
| `probable_win` set to 0 in DB | Every round is a loss | Recalculate using house_edge formula |
| `probable_win` cast fails | Model throws casting exception | Check `games.probable_win` column type: simple decimal for binary games, JSON for complex |
| Forgetting `@csrf` in form | Laravel 419 error on invest submit | Add `@csrf` inside `<form id="game">` |
| `$this->userSelect` not set for custom input | `user_select` stored as null | Set `$this->userSelect` manually inside `gameResult()` |
| Win crediting skipped for multi-step game | Balance not updated on win | In `customCompleteLogic`, either return `should_return=false` to use base logic, or manually credit `$user->balance` and create `Transaction` |
| Not setting `status = GAME_FINISHED` in custom logic | Running-game lock prevents next round | Set `$gameLog->status = Status::GAME_FINISHED; $gameLog->save()` on every terminal path |
| Storing encrypted result without updating log blades | Admin/user log shows garbled ciphertext | Add alias to the `in_array` check in both log blades, or store decrypted result |
| Adding game to only one template | 404 when user switches active template | Create blade in both `basic` and `sunfyre` directories |