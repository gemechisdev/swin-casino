@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">@lang('Phase Information')</h5>
                </div>
                <form action="{{ route('admin.lottery.phases.update', $phase->id) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>@lang('Phase Number')</label>
                                    <input type="number" name="phase_number" value="{{ $phase->phase_number }}" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>@lang('Sale Start At')</label>
                                    <input type="text" name="sale_start_at" value="{{ $phase->sale_start_at }}" class="form-control datepicker-here" data-language="en" data-date-format="yyyy-mm-dd" data-timepicker="true" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>@lang('Sale End At')</label>
                                    <input type="text" name="sale_end_at" value="{{ $phase->sale_end_at }}" class="form-control datepicker-here" data-language="en" data-date-format="yyyy-mm-dd" data-timepicker="true" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>@lang('Draw At')</label>
                                    <input type="text" name="draw_at" value="{{ $phase->draw_at }}" class="form-control datepicker-here" data-language="en" data-date-format="yyyy-mm-dd" data-timepicker="true" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>@lang('Status')</label>
                                    <select name="status" class="form-control">
                                        <option value="0" @selected($phase->status == 0)>@lang('Pending')</option>
                                        <option value="1" @selected($phase->status == 1)>@lang('Active')</option>
                                        <option value="2" @selected($phase->status == 2)>@lang('Sales Closed')</option>
                                        <option value="3" @selected($phase->status == 3)>@lang('Drawn')</option>
                                        <option value="4" @selected($phase->status == 4)>@lang('Completed')</option>
                                        <option value="5" @selected($phase->status == 5)>@lang('Cancelled')</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn--primary">@lang('Update Phase')</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">@lang('Prize Tiers')</h5>
                    <button class="btn btn-sm btn-outline--primary" data-bs-toggle="modal" data-bs-target="#addTierModal">
                        <i class="la la-plus"></i> @lang('Add New Tier')
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Title')</th>
                                    <th>@lang('Type')</th>
                                    <th>@lang('Winners')</th>
                                    <th>@lang('Prize Amount')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($phase->prizeTiers as $tier)
                                    <tr>
                                        <td>{{ __($tier->prize_title) }}</td>
                                        <td>{{ $tier->prizeTypeLabel() }}</td>
                                        <td>{{ $tier->winner_count }}</td>
                                        <td>
                                            @if($tier->amount_mode == 2)
                                                {{ $tier->pot_percent }}% of Pot
                                            @else
                                                {{ showAmount($tier->prize_amount) }}
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline--danger confirmationBtn"
                                                    data-action="{{ route('admin.lottery.tiers.delete', $tier->id) }}"
                                                    data-question="@lang('Are you sure to remove this prize tier?')">
                                                <i class="la la-trash"></i> @lang('Remove')
                                            </button>
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
            </div>
        </div>
    </div>

    <!-- Add Tier Modal -->
    <div id="addTierModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Add Prize Tier')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.lottery.tiers.store', $phase->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Prize Title')</label>
                            <input type="text" name="prize_title" class="form-control" placeholder="e.g. 1st Prize" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Prize Type')</label>
                            <select name="prize_type" class="form-control" required>
                                <option value="1">@lang('Cash')</option>
                                <option value="2">@lang('Physical')</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('Amount Mode')</label>
                            <select name="amount_mode" class="form-control" required>
                                <option value="1">@lang('Fixed Amount')</option>
                                <option value="2">@lang('Pot Share (%)')</option>
                            </select>
                        </div>
                        <div class="form-group amount-fixed">
                            <label>@lang('Prize Amount')</label>
                            <input type="number" step="any" name="prize_amount" class="form-control">
                        </div>
                        <div class="form-group amount-pot d-none">
                            <label>@lang('Pot Share (%)')</label>
                            <input type="number" step="any" name="pot_percent" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>@lang('Winner Count')</label>
                            <input type="number" name="winner_count" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Description')</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100">@lang('Save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/vendor/datepicker.min.css') }}">
@endpush

@push('script')
    <script src="{{ asset('assets/admin/js/vendor/datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/datepicker.en.js') }}"></script>
    <script>
        (function($) {
            "use strict";
            $('[name=amount_mode]').on('change', function() {
                if ($(this).val() == 2) {
                    $('.amount-pot').removeClass('d-none');
                    $('.amount-fixed').addClass('d-none');
                } else {
                    $('.amount-pot').addClass('d-none');
                    $('.amount-fixed').removeClass('d-none');
                }
            });
        })(jQuery);
    </script>
@endpush
