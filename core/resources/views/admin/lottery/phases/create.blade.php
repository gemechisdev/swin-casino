@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <form action="{{ route('admin.lottery.phases.store', $campaign->id) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>@lang('Phase Number')</label>
                                    <input type="number" name="phase_number" value="{{ $campaign->phases()->count() + 1 }}" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>@lang('Sale Start At')</label>
                                    <input type="text" name="sale_start_at" class="form-control datepicker-here" data-language="en" data-date-format="yyyy-mm-dd" data-timepicker="true" data-time-format="hh:ii aa" required autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>@lang('Sale End At')</label>
                                    <input type="text" name="sale_end_at" class="form-control datepicker-here" data-language="en" data-date-format="yyyy-mm-dd" data-timepicker="true" data-time-format="hh:ii aa" required autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>@lang('Draw At')</label>
                                    <input type="text" name="draw_at" class="form-control datepicker-here" data-language="en" data-date-format="yyyy-mm-dd" data-timepicker="true" data-time-format="hh:ii aa" required autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Create Phase')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('style-lib')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
@endpush

@push('style')
    <style>
        .daterangepicker {
            z-index: 9999 !important;
        }
    </style>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            
            $('.datepicker-here').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                timePicker: true,
                timePicker24Hour: false,
                autoUpdateInput: true,
                startDate: moment(),
                locale: {
                    format: 'YYYY-MM-DD hh:mm A'
                }
            });

            $('.datepicker-here').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD hh:mm A'));
            });

        })(jQuery);
    </script>
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.lottery.phases', ['campaign_id' => $campaign->id]) }}" />
@endpush
