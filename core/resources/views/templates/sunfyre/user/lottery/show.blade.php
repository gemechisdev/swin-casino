@extends('Template::layouts.master')
@section('content')
    <div class="notice"></div>
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card custom--card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __($campaign->name) }} - @lang('Phase') #{{ $phase->phase_number }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-5">
                            <div class="lottery-details-thumb">
                                <img src="{{ getImage(getFilePath('lottery') . '/' . $campaign->image, getFileSize('lottery')) }}" alt="image" class="w-100 rounded">
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="lottery-info-list">
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span class="text-muted">@lang('Ticket Price')</span>
                                    <span class="text--base fw-bold">{{ showAmount($campaign->ticket_price) }}</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span class="text-muted">@lang('Prize Pot')</span>
                                    <span class="text--success fw-bold">{{ showAmount($phase->prize_pool) }}</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span class="text-muted">@lang('Draw At')</span>
                                    <span class="text-white">{{ showDateTime($phase->draw_at) }}</span>
                                </div>
                                <div class="d-flex justify-content-between py-2">
                                    <span class="text-muted">@lang('Time Left')</span>
                                    <span class="text--warning lottery-countdown fw-bold" data-time="{{ $phase->sale_end_at }}">...</span>
                                </div>
                            </div>

                            <form action="{{ route('user.lottery.buy', $phase->id) }}" method="POST" class="mt-4">
                                @csrf
                                <div class="form-group mb-3">
                                    <label class="text-white mb-2">@lang('Number of Tickets')</label>
                                    <div class="input-group">
                                        <input type="number" name="quantity" value="1" min="1" max="100" class="form-control h-45" id="ticket-qty" required>
                                        <span class="input-group-text bg--base">@lang('Total'): <span id="total-price">{{ showAmount($campaign->ticket_price) }}</span></span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted">@lang('Available Balance'): {{ showAmount(auth()->user()->balance) }}</small>
                                        <small class="text-muted">@lang('Max per User'): {{ $campaign->max_tickets_per_user ?: __('Unlimited') }}</small>
                                    </div>
                                </div>
                                @if($phase->status == Status::LOTTERY_PHASE_ACTIVE)
                                    <button type="submit" class="btn btn--base w-100 h-45">
                                        <i class="las la-ticket-alt"></i> @lang('Buy Ticket')
                                    </button>
                                @else
                                    <button class="btn btn--secondary w-100 h-45" disabled>@lang('Sales Closed')</button>
                                @endif
                            </form>
                        </div>
                    </div>

                    @if($campaign->description)
                        <div class="mt-5">
                            <h5 class="text--base mb-3"><i class="las la-info-circle"></i> @lang('Game Instructions')</h5>
                            <div class="instruction-content text-white">
                                @php echo $campaign->description @endphp
                            </div>
                        </div>
                    @endif

                    <div class="mt-5">
                        <h5 class="text--base mb-3"><i class="las la-trophy"></i> @lang('Prize Tiers')</h5>
                        <div class="table-responsive">
                            <table class="table custom--table">
                                <thead>
                                    <tr>
                                        <th>@lang('Rank / Title')</th>
                                        <th>@lang('Winners')</th>
                                        <th>@lang('Prize')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prizeTiers as $tier)
                                        <tr>
                                            <td>{{ __($tier->prize_title) }}</td>
                                            <td>{{ $tier->winner_count }}</td>
                                            <td>
                                                @if($tier->amount_mode == 2)
                                                    <span class="text--success">{{ $tier->pot_percent }}% @lang('of Pot')</span>
                                                @else
                                                    <span class="text--success">{{ showAmount($tier->prize_amount) }}</span>
                                                @endif
                                                @if($tier->prize_type == 2)
                                                    <br><small class="text--primary">+ @lang('Physical Prize')</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card custom--card">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Recent Winners')</h5>
                </div>
                <div class="card-body p-0">
                    <div class="recent-winner-list">
                        @forelse($recentWinners as $winner)
                            <div class="winner-item d-flex align-items-center gap-3 p-3 border-bottom">
                                <div class="winner-thumb">
                                    <img src="{{ getImage(getFilePath('userProfile') . '/' . $winner->user->image, getFileSize('userProfile'), true) }}" alt="user" class="rounded-circle" width="40">
                                </div>
                                <div class="winner-info flex-grow-1">
                                    <h6 class="mb-0 text-white">{{ $winner->user->username }}</h6>
                                    <p class="mb-0 small text-muted">{{ __($winner->prizeTier->prize_title) }}</p>
                                </div>
                                <div class="winner-amount text-end">
                                    @if($winner->prize_type == 1)
                                        <span class="text--success fw-bold">{{ showAmount($winner->prize_amount) }}</span>
                                    @else
                                        <span class="text--primary fw-bold">@lang('Physical')</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-5">
                                <i class="las la-trophy la-3x text-muted mb-2"></i>
                                <p class="text-muted mb-0">@lang('No winners yet.')</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/lottery.css') }}">
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            var price = {{ $campaign->ticket_price }};
            var curSym = "{{ gs('cur_sym') }}";
            var curText = "{{ gs('cur_text') }}";

            $('#ticket-qty').on('input', function() {
                var qty = $(this).val();
                if (qty < 1) qty = 1;
                var total = (qty * price).toFixed(2);
                
                // Formatting total based on system setting
                var format = "{{ gs('currency_format') }}";
                var result = '';
                if (format == 1) result = curSym + total + ' ' + curText;
                else if (format == 2) result = total + ' ' + curText;
                else result = curSym + total;

                $('#total-price').text(result);
            });

            $('.lottery-countdown').each(function() {
                var endTime = new Date($(this).data('time')).getTime();
                var self = $(this);
                var x = setInterval(function() {
                    var now = new Date().getTime();
                    var distance = endTime - now;
                    if (distance < 0) {
                        clearInterval(x);
                        self.text("@lang('Sales Closed')");
                        return;
                    }
                    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    self.text(days + "d " + hours + "h " + minutes + "m " + seconds + "s");
                }, 1000);
            });
        })(jQuery);
    </script>
@endpush
