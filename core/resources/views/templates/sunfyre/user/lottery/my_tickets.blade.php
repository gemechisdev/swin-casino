@extends('Template::layouts.master')
@section('content')
    <div class="notice"></div>
    <div class="card custom--card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">@lang('My Lottery Tickets')</h5>
            <a href="{{ route('user.lottery.index') }}" class="btn btn--base btn--sm"><i class="las la-plus"></i> @lang('Buy More')</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive--md">
                <table class="table custom--table">
                    <thead>
                        <tr>
                            <th>@lang('Campaign')</th>
                            <th>@lang('Phase')</th>
                            <th>@lang('Serial Number')</th>
                            <th>@lang('Price')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Purchase Date')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ getImage(getFilePath('lottery') . '/' . $ticket->phase->campaign->image, getFileSize('lottery')) }}" alt="lottery" width="30" class="rounded">
                                        <span>{{ __($ticket->phase->campaign->name) }}</span>
                                    </div>
                                </td>
                                <td>#{{ $ticket->phase->phase_number }}</td>
                                <td><span class="fw-bold text--base">{{ $ticket->serial }}</span></td>
                                <td>{{ showAmount($ticket->purchase_price) }}</td>
                                <td>
                                    @php echo $ticket->statusBadge() @endphp
                                    @if($ticket->status == Status::LOTTERY_TICKET_WINNER && $ticket->winner && $ticket->winner->prizeTier)
                                        <br><small class="text--success">{{ __($ticket->winner->prizeTier->prize_title) }}</small>
                                    @endif
                                </td>
                                <td>{{ showDateTime($ticket->created_at) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center p-5" colspan="100%">
                                    <div class="empty-state">
                                        <i class="las la-ticket-alt la-3x text-muted mb-2"></i>
                                        <p class="text-muted">@lang('You haven\'t purchased any tickets yet.')</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($tickets->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($tickets) }}
            </div>
        @endif
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/lottery.css') }}">
@endpush
