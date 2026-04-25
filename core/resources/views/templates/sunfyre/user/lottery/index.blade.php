@extends('Template::layouts.master')
@section('content')
    <div class="notice"></div>
    <div class="lottery-header mb-5 text-center">
        <h2 class="text--base">@lang('AddisWin Lottery Hub')</h2>
        <p class="text-muted">@lang('Try your luck with our exclusive Ethiopian lottery campaigns. High stakes, bigger wins!')</p>
    </div>

    <div class="row g-4">
        @forelse($lotteries as $lottery)
            @php
                $phase = $lottery->phases->first();
            @endphp
            <div class="col-xl-4 col-md-6">
                <div class="lottery-card">
                    <div class="lottery-card__thumb">
                        <img src="{{ getImage(getFilePath('lottery') . '/' . $lottery->image, getFileSize('lottery')) }}" alt="image">
                        @if($phase)
                            <span class="lottery-badge">
                                <i class="las la-trophy"></i> {{ showAmount($phase->prize_pool) }}
                            </span>
                        @endif
                    </div>
                    <div class="lottery-card__content">
                        <h4 class="lottery-card__title">{{ __($lottery->name) }}</h4>
                        
                        <div class="lottery-card__details">
                            <div class="lottery-card__detail">
                                <span class="label"><i class="las la-ticket-alt"></i> @lang('Ticket Price')</span>
                                <span class="value text--base">{{ showAmount($lottery->ticket_price) }}</span>
                            </div>
                            
                            @if($phase)
                                <div class="lottery-card__detail">
                                    <span class="label"><i class="las la-clock"></i> @lang('Ends In')</span>
                                    <span class="value text--warning lottery-countdown" data-time="{{ $phase->sale_end_at }}">...</span>
                                </div>
                            @endif
                        </div>

                        <div class="lottery-card__btn">
                            @if($phase)
                                <a href="{{ route('user.lottery.show', $lottery->id) }}" class="btn btn--base w-100">
                                    <i class="las la-play"></i> @lang('Play Now')
                                </a>
                            @else
                                <button class="btn btn--secondary w-100" disabled>
                                    <i class="las la-ban"></i> @lang('Coming Soon')
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center">
                <div class="empty-state py-5">
                    <i class="las la-ticket-alt la-5x text-muted mb-3"></i>
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
