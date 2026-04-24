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
                                            <th>@lang('TRX')</th>
                                            <th>@lang('Campaign')</th>
                                            <th>@lang('Type')</th>
                                            <th>@lang('Amount')</th>
                                            <th>@lang('Balance')</th>
                                            <th>@lang('Date')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($logs as $log)
                                            <tr>
                                                <td>{{ $log->trx }}</td>
                                                <td>{{ __($log->phase->campaign->name) }}</td>
                                                <td>@php echo $log->typeBadge() @endphp</td>
                                                <td class="fw-bold @if($log->type == Status::LOTTERY_TRX_WIN) text--success @else text--danger @endif">
                                                    @if($log->type == Status::LOTTERY_TRX_WIN) + @else - @endif
                                                    {{ showAmount($log->amount) }}
                                                </td>
                                                <td>{{ showAmount($log->post_balance) }}</td>
                                                <td>{{ showDateTime($log->created_at) }}</td>
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
                        @if($logs->hasPages())
                            <div class="card-footer py-4">
                                {{ paginateLinks($logs) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
