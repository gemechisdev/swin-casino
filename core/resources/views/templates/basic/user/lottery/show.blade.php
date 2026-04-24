@extends('Template::layouts.master')
@section('content')
    <section class="pt-120 pb-120">
        <div class="container container-xxl">
            <div class="notice"></div>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="d-widget h-100">
                        <div class="row">
                            <div class="col-md-5">
                                <img src="{{ getImage(getFilePath('lottery') . '/' . $campaign->image, getFileSize('lottery')) }}" alt="image" class="w-100 rounded">
                            </div>
                            <div class="col-md-7">
                                <h3 class="mb-3">{{ __($campaign->name) }}</h3>
                                <div class="list-group list-group-flush section--bg rounded mb-4">
                                    <div class="list-group-item d-flex justify-content-between bg-transparent border-secondary text-white">
                                        <span>@lang('Ticket Price')</span>
                                        <span class="text--base fw-bold">{{ showAmount($campaign->ticket_price) }}</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between bg-transparent border-secondary text-white">
                                        <span>@lang('Prize Pot')</span>
                                        <span class="text--success fw-bold">{{ showAmount($phase->prize_pool) }}</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between bg-transparent border-0 text-white">
                                        <span>@lang('Draw Date')</span>
                                        <span>{{ showDateTime($phase->draw_at) }}</span>
                                    </div>
                                </div>

                                <form action="{{ route('user.lottery.buy', $phase->id) }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label>@lang('Tickets to Buy')</label>
                                        <input type="number" name="quantity" value="1" min="1" class="form-control" required>
                                    </div>
                                    @if($phase->status == Status::LOTTERY_PHASE_ACTIVE)
                                        <button type="submit" class="cmn-btn w-100">@lang('Buy Tickets')</button>
                                    @else
                                        <button class="cmn-btn-two w-100" disabled>@lang('Sales Closed')</button>
                                    @endif
                                </form>
                            </div>
                        </div>

                        <div class="mt-5">
                            <h4 class="mb-3">@lang('Prize Structure')</h4>
                            <div class="table-responsive">
                                <table class="table table--light style--two">
                                    <thead>
                                        <tr>
                                            <th>@lang('Rank')</th>
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
                                                        {{ $tier->pot_percent }}% @lang('Pot Share')
                                                    @else
                                                        {{ showAmount($tier->prize_amount) }}
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
                <div class="col-lg-4">
                    <div class="d-widget">
                        <h4 class="mb-3">@lang('Recent Winners')</h4>
                        <ul class="list-group list-group-flush">
                            @forelse($recentWinners as $winner)
                                <li class="list-group-item bg-transparent border-secondary text-white d-flex justify-content-between px-0">
                                    <span>{{ $winner->user->username }}</span>
                                    <span class="text--success">{{ showAmount($winner->prize_amount) }}</span>
                                </li>
                            @empty
                                <li class="list-group-item bg-transparent border-0 text-white text-center">@lang('No winners yet')</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
