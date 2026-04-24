@extends('Template::layouts.master')
@section('content')
    <div class="notice"></div>
    <div class="card custom--card">
        <div class="card-header">
            <h5 class="card-title mb-0">@lang('Lottery Transactions')</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive--md">
                <table class="table custom--table">
                    <thead>
                        <tr>
                            <th>@lang('TRX')</th>
                            <th>@lang('Campaign / Phase')</th>
                            <th>@lang('Type')</th>
                            <th>@lang('Amount')</th>
                            <th>@lang('Balance')</th>
                            <th>@lang('Date')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td><span class="fw-bold text--base">{{ $log->trx }}</span></td>
                                <td>
                                    {{ __($log->phase->campaign->name) }}
                                    <br>
                                    <small class="text-muted">#{{ $log->phase->phase_number }}</small>
                                </td>
                                <td>
                                    @php echo $log->typeBadge() @endphp
                                </td>
                                <td>
                                    <span class="fw-bold @if($log->type == Status::LOTTERY_TRX_WIN || $log->type == Status::LOTTERY_TRX_REFUND) text--success @else text--danger @endif">
                                        @if($log->type == Status::LOTTERY_TRX_WIN || $log->type == Status::LOTTERY_TRX_REFUND) + @else - @endif
                                        {{ showAmount($log->amount) }}
                                    </span>
                                </td>
                                <td>{{ showAmount($log->post_balance) }}</td>
                                <td>{{ showDateTime($log->created_at) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center p-5" colspan="100%">
                                    <p class="text-muted mb-0">@lang('No transactions found.')</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($logs) }}
            </div>
        @endif
    </div>
@endsection
