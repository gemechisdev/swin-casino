@extends('Template::layouts.master')
@section('content')
    <div class="notice"></div>
    <div class="card custom--card">
        <div class="card-header">
            <h5 class="card-title mb-0">@lang('Lottery Winners')</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive--md">
                <table class="table custom--table">
                    <thead>
                        <tr>
                            <th>@lang('User')</th>
                            <th>@lang('Campaign / Phase')</th>
                            <th>@lang('Rank')</th>
                            <th>@lang('Prize')</th>
                            <th>@lang('Date')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($winners as $winner)
                            <tr>
                                <td>{{ $winner->user->username }}</td>
                                <td>
                                    {{ __($winner->phase->campaign->name) }}
                                    <br>
                                    <small class="text-muted">#{{ $winner->phase->phase_number }}</small>
                                </td>
                                <td>{{ __($winner->prizeTier->prize_title) }}</td>
                                <td>
                                    @if($winner->prize_type == 1)
                                        <span class="text--success fw-bold">{{ showAmount($winner->prize_amount) }}</span>
                                    @else
                                        <span class="text--primary fw-bold">@lang('Physical Prize')</span>
                                    @endif
                                </td>
                                <td>{{ showDateTime($winner->created_at, 'Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center p-5" colspan="100%">
                                    <p class="text-muted mb-0">@lang('No winners recorded yet.')</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($winners->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($winners) }}
            </div>
        @endif
    </div>
@endsection
