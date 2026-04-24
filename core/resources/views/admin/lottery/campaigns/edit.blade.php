@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form action="{{ isset($campaign) ? route('admin.lottery.campaign.update', $campaign->id) : route('admin.lottery.campaign.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Image')</label>
                                    <x-image-uploader class="w-100" type="lottery" :imagePath="isset($campaign) ? getImage(getFilePath('lottery') . '/' . $campaign->image, getFileSize('lottery')) : ''" />
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Name')</label>
                                            <input type="text" name="name" value="{{ old('name', @$campaign->name) }}" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Ticket Price')</label>
                                            <div class="input-group">
                                                <input type="number" step="any" name="ticket_price" value="{{ old('ticket_price', @$campaign->ticket_price) }}" class="form-control" required>
                                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Max Tickets Per User')</label>
                                            <input type="number" name="max_tickets_per_user" value="{{ old('max_tickets_per_user', @$campaign->max_tickets_per_user) }}" class="form-control">
                                            <small class="text--small text--muted"><i class="la la-info-circle"></i> @lang('Leave 0 for no limit')</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Total Ticket Limit')</label>
                                            <input type="number" name="total_ticket_limit" value="{{ old('total_ticket_limit', @$campaign->total_ticket_limit) }}" class="form-control">
                                            <small class="text--small text--muted"><i class="la la-info-circle"></i> @lang('Total tickets available per phase. Leave 0 for no limit')</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Serial Length')</label>
                                            <input type="number" name="serial_length" value="{{ old('serial_length', @$campaign->serial_length ?? 10) }}" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Phase Duration (Days)')</label>
                                            <input type="number" name="phase_duration_days" value="{{ old('phase_duration_days', @$campaign->phase_duration_days ?? 7) }}" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('House Edge (%)')</label>
                                            <div class="input-group">
                                                <input type="number" step="any" name="house_edge" value="{{ old('house_edge', @$campaign->house_edge ?? 10) }}" class="form-control" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Draw Mode')</label>
                                            <select name="draw_mode" class="form-control" required>
                                                <option value="1" @selected(@$campaign->draw_mode == 1)>@lang('Draw from Sold Tickets')</option>
                                                <option value="2" @selected(@$campaign->draw_mode == 2)>@lang('Draw from Full Serial Space')</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Auto Next Phase')</label>
                                            <select name="auto_next_phase" class="form-control" required>
                                                <option value="1" @selected(@$campaign->auto_next_phase == 1)>@lang('Yes')</option>
                                                <option value="0" @selected(@$campaign->auto_next_phase == 0)>@lang('No')</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Description')</label>
                                    <textarea name="description" rows="5" class="form-control">{{ old('description', @$campaign->description) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.lottery.campaigns') }}" />
@endpush
