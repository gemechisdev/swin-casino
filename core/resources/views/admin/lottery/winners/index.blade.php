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
                                    <th>@lang('Prize')</th>
                                    <th>@lang('Delivery')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($winners as $winner)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $winner->user->fullname }}</span>
                                            <br>
                                            <span class="small">
                                                <a href="{{ route('admin.users.detail', $winner->user_id) }}"><span>@</span>{{ $winner->user->username }}</a>
                                            </span>
                                        </td>
                                        <td>
                                            {{ __($winner->phase->campaign->name) }}
                                            <br>
                                            <small class="text-muted">@lang('Phase') #{{ $winner->phase->phase_number }}</small>
                                        </td>
                                        <td>{{ $winner->ticket->serial }}</td>
                                        <td>
                                            {{ __($winner->prizeTier->prize_title) }}
                                            <br>
                                            @if($winner->prize_type == 1)
                                                <span class="text--success">{{ showAmount($winner->prize_amount) }}</span>
                                            @else
                                                <span class="text--primary">@lang('Physical Prize')</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php echo $winner->deliveryBadge() @endphp
                                        </td>
                                        <td>
                                            @if($winner->prize_type == 2)
                                                <button class="btn btn-sm btn-outline--primary editBtn"
                                                        data-id="{{ $winner->id }}"
                                                        data-status="{{ $winner->delivery_status }}"
                                                        data-note="{{ $winner->admin_note }}"
                                                        data-action="{{ route('admin.lottery.winners.delivery', $winner->id) }}">
                                                    <i class="la la-truck"></i> @lang('Delivery')
                                                </button>
                                            @else
                                                <span class="text-muted">@lang('N/A')</span>
                                            @endif
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
                @if ($winners->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($winners) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Delivery Update Modal -->
    <div id="deliveryModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Update Delivery Status')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Delivery Status')</label>
                            <select name="delivery_status" class="form-control" required>
                                <option value="0">@lang('Pending')</option>
                                <option value="1">@lang('Dispatched')</option>
                                <option value="2">@lang('Delivered')</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('Admin Note')</label>
                            <textarea name="admin_note" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100">@lang('Update')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.editBtn').on('click', function() {
                var modal = $('#deliveryModal');
                modal.find('form').attr('action', $(this).data('action'));
                modal.find('[name=delivery_status]').val($(this).data('status'));
                modal.find('[name=admin_note]').val($(this).data('note'));
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
