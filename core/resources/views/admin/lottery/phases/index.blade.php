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
                                    <th>@lang('Phase')</th>
                                    <th>@lang('Campaign')</th>
                                    <th>@lang('End At')</th>
                                    <th>@lang('Draw At')</th>
                                    <th>@lang('Tickets Sold')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($phases as $phase)
                                    <tr>
                                        <td>#{{ $phase->phase_number }}</td>
                                        <td>{{ __($phase->campaign->name) }}</td>
                                        <td>{{ showDateTime($phase->sale_end_at) }}</td>
                                        <td>{{ showDateTime($phase->draw_at) }}</td>
                                        <td>{{ $phase->tickets_sold }}</td>
                                        <td>
                                            @php echo $phase->statusBadge() @endphp
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <a class="btn btn-sm btn-outline--primary" href="{{ route('admin.lottery.phases.edit', $phase->id) }}">
                                                    <i class="la la-pencil"></i> @lang('Edit')
                                                </a>
                                                @if($phase->status == Status::LOTTERY_PHASE_CLOSED && $phase->draw_at <= now())
                                                    <button class="btn btn-sm btn-outline--success confirmationBtn"
                                                            data-action="{{ route('admin.lottery.phases.draw', $phase->id) }}"
                                                            data-question="@lang('Are you sure to execute the draw now?')">
                                                        <i class="la la-random"></i> @lang('Draw')
                                                    </button>
                                                @endif
                                                @if($phase->status == Status::LOTTERY_PHASE_DRAWN)
                                                    <button class="btn btn-sm btn-outline--warning confirmationBtn"
                                                            data-action="{{ route('admin.lottery.phases.distribute', $phase->id) }}"
                                                            data-question="@lang('Are you sure to distribute prizes now?')">
                                                        <i class="la la-gift"></i> @lang('Distribute')
                                                    </button>
                                                @endif
                                            </div>
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
                @if ($phases->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($phases) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    @if(request()->campaign_id)
        <a href="{{ route('admin.lottery.phases.create', request()->campaign_id) }}" class="btn btn-sm btn-outline--primary">
            <i class="la la-plus"></i> @lang('Add New Phase')
        </a>
    @endif
@endpush
