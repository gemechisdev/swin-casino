@extends('Template::layouts.master')
@section('content')
    <section class="pt-120 pb-120">
        <div class="container container-xxl">
            <div class="notice"></div>
            <div class="row g-4 justify-content-center">
                @forelse($lotteries as $lottery)
                    @php
                        $phase = $lottery->phases->first();
                    @endphp
                    <div class="col-xl-3 col-lg-4 col-sm-6">
                        <div class="game-card style--two">
                            <div class="game-card__thumb">
                                <img src="{{ getImage(getFilePath('lottery') . '/' . $lottery->image, getFileSize('lottery')) }}" alt="image">
                            </div>
                            <div class="game-card__content">
                                <h4 class="game-name">{{ __($lottery->name) }}</h4>
                                <div class="w-100 mt-2">
                                    <div class="d-flex justify-content-between mb-2">
                                        <small>@lang('Ticket Price')</small>
                                        <small class="text--base">{{ showAmount($lottery->ticket_price) }}</small>
                                    </div>
                                    @if($phase)
                                        <div class="d-flex justify-content-between mb-3">
                                            <small>@lang('Prize Pot')</small>
                                            <small class="text--success">{{ showAmount($phase->prize_pool) }}</small>
                                        </div>
                                        <a href="{{ route('user.lottery.show', $lottery->id) }}" class="cmn-btn btn-sm w-100 text-center">@lang('Play Now')</a>
                                    @else
                                        <button class="cmn-btn-two btn-sm w-100" disabled>@lang('Coming Soon')</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="d-widget text-center">
                            <h5 class="text-muted">@lang('No active lotteries found.')</h5>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
