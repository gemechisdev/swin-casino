@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('User')</th>
                                    <th>@lang('Campaign / Phase')</th>
                                    <th>@lang('Serial')</th>
                                    <th>@lang('Purchase Price')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Date')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tickets as $ticket)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $ticket->user->fullname }}</span>
                                            <br>
                                            <span class="small">
                                                <a href="{{ route('admin.users.detail', $ticket->user_id) }}"><span>@</span>{{ $ticket->user->username }}</a>
                                            </span>
                                        </td>
                                        <td>
                                            {{ __($ticket->phase->campaign->name) }}
                                            <br>
                                            <small class="text-muted">@lang('Phase') #{{ $ticket->phase->phase_number }}</small>
                                        </td>
                                        <td><span class="fw-bold">{{ $ticket->serial }}</span></td>
                                        <td>{{ showAmount($ticket->purchase_price) }}</td>
                                        <td>
                                            @php echo $ticket->statusBadge() @endphp
                                        </td>
                                        <td>
                                            {{ showDateTime($ticket->created_at) }}
                                            <br>
                                            {{ diffForHumans($ticket->created_at) }}
                                        </td>
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
                @if ($tickets->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($tickets) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form />
@endpush
