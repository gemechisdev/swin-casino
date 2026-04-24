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
                                    <input type="text" name="sale_start_at" class="form-control datepicker-here" data-language="en" data-date-format="yyyy-mm-dd" data-timepicker="true" required autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>@lang('Sale End At')</label>
                                    <input type="text" name="sale_end_at" class="form-control datepicker-here" data-language="en" data-date-format="yyyy-mm-dd" data-timepicker="true" required autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>@lang('Draw At')</label>
                                    <input type="text" name="draw_at" class="form-control datepicker-here" data-language="en" data-date-format="yyyy-mm-dd" data-timepicker="true" required autocomplete="off">
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

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/vendor/datepicker.min.css') }}">
@endpush

@push('script')
    <script src="{{ asset('assets/admin/js/vendor/datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/datepicker.en.js') }}"></script>
    <script>
        (function($) {
            "use strict";
            if (!$('.datepicker-here').val()) {
                $('.datepicker-here').datepicker({
                    timepicker: true,
                    language: 'en',
                    dateFormat: 'yyyy-mm-dd',
                    timeFormat: 'hh:ii aa'
                });
            }
        })(jQuery);
    </script>
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.lottery.phases', ['campaign_id' => $campaign->id]) }}" />
@endpush
