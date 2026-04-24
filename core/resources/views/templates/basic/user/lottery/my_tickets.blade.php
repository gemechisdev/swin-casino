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
                                            <th>@lang('Campaign')</th>
                                            <th>@lang('Phase')</th>
                                            <th>@lang('Serial Number')</th>
                                            <th>@lang('Price')</th>
                                            <th>@lang('Status')</th>
                                            <th>@lang('Date')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tickets as $ticket)
                                            <tr>
                                                <td>{{ __($ticket->phase->campaign->name) }}</td>
                                                <td>#{{ $ticket->phase->phase_number }}</td>
                                                <td class="fw-bold">{{ $ticket->serial }}</td>
                                                <td>{{ showAmount($ticket->purchase_price) }}</td>
                                                <td>@php echo $ticket->statusBadge() @endphp</td>
                                                <td>{{ showDateTime($ticket->created_at) }}</td>
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
                        @if($tickets->hasPages())
                            <div class="card-footer py-4">
                                {{ paginateLinks($tickets) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
