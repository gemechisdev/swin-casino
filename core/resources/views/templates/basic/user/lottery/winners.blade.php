@extends('Template::layouts.master')
@section('content')
    <section class="pt-120 pb-120">
        <div class="container container-xxl">
            <div class="notice"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card custom--card">
                        <div class="card-body p-0">
                            <div class="table-responsive--md">
                                <table class="table custom--table">
                                    <thead>
                                        <tr>
                                            <th>@lang('User')</th>
                                            <th>@lang('Campaign')</th>
                                            <th>@lang('Rank')</th>
                                            <th>@lang('Prize')</th>
                                            <th>@lang('Date')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($winners as $winner)
                                            <tr>
                                                <td>{{ $winner->user->username }}</td>
                                                <td>{{ __($winner->phase->campaign->name) }} (#{{ $winner->phase->phase_number }})</td>
                                                <td>{{ __($winner->prizeTier->prize_title) }}</td>
                                                <td>{{ showAmount($winner->prize_amount) }}</td>
                                                <td>{{ showDateTime($winner->created_at, 'Y-m-d') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($winners->hasPages())
                            <div class="card-footer py-4">
                                {{ paginateLinks($winners) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
