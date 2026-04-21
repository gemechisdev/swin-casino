@extends('admin.layouts.app')
@section('panel')
    <form action="{{ route('admin.game.update', $game->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="form-group">
                            <label>@lang('Game Name')</label>
                            <input class="form-control" name="name" type="text" value="{{ $game->name }}"
                                placeholder="@lang('Game Name')" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Image')</label>
                            <x-image-uploader image="{{ $game->image }}" class="w-100" type="game" :required=false />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title">@lang('Win Setting')</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>@lang('House Edge (%)')</label>
                            <div class="input-group mb-3">
                                <input class="form-control house-edge-input" name="house_edge" type="number"
                                    id="house_edge"
                                    value="{{ $game->house_edge ?? 5 }}" step="0.01" min="0" max="100"
                                    placeholder="@lang('House Edge %')">
                                <span class="input-group-text" id="basic-addon2">@lang('%')</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Demo House Edge (%)')</label>
                            <div class="input-group mb-3">
                                <input class="form-control house-edge-demo-input" name="house_edge_demo" type="number"
                                    id="house_edge_demo"
                                    value="{{ $game->house_edge_demo ?? 2 }}" step="0.01" min="0" max="100"
                                    placeholder="@lang('Demo House Edge %')">
                                <span class="input-group-text" id="basic-addon2">@lang('%')</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Winning Chance (%)')</label>
                            <div class="input-group mb-3">
                                <input class="form-control" id="win_chance_display" type="text" readonly
                                    value="{{ $game->probable_win ?? 'Auto' }}" placeholder="@lang('Auto-calculated')">
                                <span class="input-group-text">@lang('%') @lang('(read-only)')</span>
                            </div>
                            <small class="text-muted">@lang('Effective Win Rate: auto-calculated from House Edge')</small>
                        </div>
                        @if ($game->alias != 'color_prediction')
                            <div class="form-group">
                                @if ($game->alias == 'mines')
                                    <label>@lang('Win Amount Per Mines')</label>
                                @else
                                    <label>@lang('Win Amount')</label>
                                @endif
                                <div class="input-group mb-3">
                                    <input class="form-control" name="win" type="number"
                                        value="{{ getAmount($game->win) }}" step="any" placeholder="@lang('Win')">
                                    <span class="input-group-text" id="basic-addon2">@lang('%')</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>@lang('Invest')</label>
                                <input name="invest_back" data-width="100%" data-onstyle="-success" data-offstyle="-danger"
                                    data-bs-toggle="toggle" data-on="@lang('Give Back')" data-off="@lang('No Back"')"
                                    type="checkbox" @checked($game->invest_back)>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title">@lang('Invest Amount Setting')</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>@lang('Minimum Invest Amount')</label>
                            <div class="input-group mb-3">
                                <input class="form-control" name="min" type="number"
                                    value="{{ getAmount($game->min_limit) }}" step="any" min="1"
                                    placeholder="@lang('Minimum Invest Amount')" required>
                                <span class="input-group-text">{{ gs('cur_text') }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Maximum Invest Amount')</label>
                            <div class="input-group mb-3">
                                <input class="form-control" name="max" type="number"
                                    value="{{ getAmount($game->max_limit) }}" step="any" min="1"
                                    placeholder="@lang('Maximum Invest Amount')" required>
                                <span class="input-group-text">{{ gs('cur_text') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title">@lang('For App')</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>@lang('Trending')</label>
                            <input name="trending" data-width="100%" data-onstyle="-success" data-offstyle="-danger"
                                data-bs-toggle="toggle" data-on="@lang('Yes')" data-off="@lang('No')"
                                type="checkbox" @checked($game->trending)>
                        </div>
                        <div class="form-group">
                            <label>@lang('Featured')</label>
                            <input name="featured" data-width="100%" data-onstyle="-success" data-offstyle="-danger"
                                data-bs-toggle="toggle" data-on="@lang('Yes')" data-off="@lang('No')"
                                type="checkbox" @checked($game->featured)>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">@lang('Game Instructions')</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <textarea class="form-control border-radius-5 nicEdit" name="instruction" rows="8">@php echo $game->instruction @endphp</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.game.index') }}" />
@endpush

@push('script')
<script>
(function($) {
    "use strict";
    function updateWinRate() {
        var he = parseFloat($('#house_edge').val()) || 0;
        var win = parseFloat($('[name="win"]').val()) || 0;
        var investBack = $('[name="invest_back"]').is(':checked') || $('[name="invest_back"]').val() == '1';
        var rate = 0;
        if (win > 0) {
            if (investBack) {
                rate = ((100 - he) / (1 + win / 100)).toFixed(2);
            } else {
                rate = ((100 - he) * 100 / win).toFixed(2);
            }
        } else {
            rate = (100 - he).toFixed(2);
        }
        rate = Math.min(99.99, Math.max(0, rate));
        $('#win_chance_display').val('Effective Win Rate: ' + rate + '%');
    }
    $('#house_edge, #house_edge_demo, [name="win"]').on('input change', updateWinRate);
    updateWinRate();
})(jQuery);
</script>
@endpush
