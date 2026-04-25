@extends('Template::layouts.master')
@section('content')
    <div class="notice"></div>
    <div class="lottery-header mb-5 text-center">
        <h2 class="text--base mb-2">@lang('AddisWin Lottery Hub')</h2>
        <p class="text-muted fs-18">@lang('Try your luck with our exclusive Ethiopian lottery campaigns. High stakes, bigger wins!')</p>
    </div>

    <div class="row g-4">
        @forelse($lotteries as $lottery)
            @php
                $phase = $lottery->phases->first();
            @endphp
            <div class="col-xl-4 col-md-6">
                <div class="game-card">
                    <div class="game-card__thumb">
                        <img src="{{ getImage(getFilePath('lottery') . '/' . $lottery->image, getFileSize('lottery')) }}" alt="image">
                        @if($phase)
                            <span class="lottery-badge lottery-badge--prize">
                                <i class="las la-trophy"></i> {{ showAmount($phase->prize_pool) }}
                            </span>
                        @endif
                    </div>
                    <div class="game-card__content">
                        <h4 class="game-card__title">{{ __($lottery->name) }}</h4>
                        
                        <div class="lottery-details-list">
                            <div class="lottery-detail-item">
                                <span class="label">
                                    <i class="las la-ticket-alt text--base"></i> @lang('Ticket Price')
                                </span>
                                <span class="value text--base">{{ showAmount($lottery->ticket_price) }}</span>
                            </div>
                            
                            @if($phase)
                                <div class="lottery-detail-item">
                                    <span class="label">
                                        <i class="las la-clock text--warning"></i> @lang('Ends In')
                                    </span>
                                    <span class="value">
                                        <span class="lottery-badge--countdown lottery-countdown" data-time="{{ $phase->sale_end_at }}">...</span>
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if($phase)
                            <a href="{{ route('user.lottery.show', $lottery->id) }}" class="btn btn--base w-100 mt-auto">
                                <i class="las la-play"></i> @lang('Play Now')
                            </a>
                        @else
                            <button class="btn btn--secondary w-100 mt-auto" disabled>
                                <i class="las la-ban"></i> @lang('Coming Soon')
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center">
                <div class="empty-state py-5">
                    <div class="empty-state__icon mb-3">
                        <i class="las la-ticket-alt la-5x text-muted"></i>
                    </div>
                    <h4 class="text-muted">@lang('No active lotteries at the moment.')</h4>
                    <p class="text-muted">@lang('Check back later for new exciting campaigns!')</p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($lotteries->hasPages())
        <div class="mt-5 text-end">
            {{ paginateLinks($lotteries) }}
        </div>
    @endif
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
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
