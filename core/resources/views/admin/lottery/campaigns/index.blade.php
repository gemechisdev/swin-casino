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
                                    <th>@lang('Campaign')</th>
                                    <th>@lang('Ticket Price')</th>
                                    <th>@lang('Draw Mode')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campaigns as $campaign)
                                    <tr>
                                        <td>
                                            <div class="user">
                                                <div class="thumb">
                                                    <img src="{{ getImage(getFilePath('lottery') . '/' . $campaign->image, getFileSize('lottery')) }}" alt="image">
                                                </div>
                                                <span class="name">{{ __($campaign->name) }}</span>
                                            </div>
                                        </td>
                                        <td>{{ showAmount($campaign->ticket_price) }}</td>
                                        <td>{{ $campaign->drawModeLabel() }}</td>
                                        <td>
                                            @php echo $campaign->statusBadge @endphp
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <a class="btn btn-sm btn-outline--primary" href="{{ route('admin.lottery.campaigns.edit', $campaign->id) }}">
                                                    <i class="la la-pencil"></i> @lang('Edit')
                                                </a>
                                                <a class="btn btn-sm btn-outline--info" href="{{ route('admin.lottery.phases', ['campaign_id' => $campaign->id]) }}">
                                                    <i class="la la-layer-group"></i> @lang('Phases')
                                                </a>
                                                @if ($campaign->status == Status::DISABLE)
                                                    <button class="btn btn-sm btn-outline--success confirmationBtn"
                                                            data-action="{{ route('admin.lottery.campaign.status', $campaign->id) }}"
                                                            data-question="@lang('Are you sure to enable this campaign?')" type="button">
                                                        <i class="la la-eye"></i> @lang('Enable')
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline--danger confirmationBtn"
                                                            data-action="{{ route('admin.lottery.campaign.status', $campaign->id) }}"
                                                            data-question="@lang('Are you sure to disable this campaign?')" type="button">
                                                        <i class="la la-eye-slash"></i> @lang('Disable')
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
                @if ($campaigns->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($campaigns) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.lottery.campaign.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="la la-plus"></i> @lang('Add New Campaign')
    </a>
@endpush
