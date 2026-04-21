@extends('Template::layouts.master')

@section('content')
    <div class="pt-120 pb-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header text-center">
                            <h5>@lang('ETB Verifier')</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-primary">
                                <p class="mb-0">
                                    @lang('Please complete your Telebirr or CBE transfer and submit the transaction reference for verification.')
                                </p>
                            </div>
                            <form class="disableSubmission appPayment" method="{{ $data->method }}" action="{{ $data->url }}">
                                @csrf
                                <input type="hidden" name="track" value="{{ $data->track }}">

                                <div class="form-group mb-3">
                                    <label class="form-label">@lang('Transaction Reference (TxID / FT)')</label>
                                    <input type="text" class="form-control form--control" name="reference" required value="{{ old('reference') }}">
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">@lang('Payer Name')</label>
                                    <input type="text" class="form-control form--control" name="payer_name" required value="{{ old('payer_name') }}">
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">@lang('Telebirr Number (optional for MP references)')</label>
                                    <input type="text" class="form-control form--control" name="payer_telebirr_no" value="{{ old('payer_telebirr_no') }}">
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">@lang('CBE Account Number (required for FT references)')</label>
                                    <input type="text" class="form-control form--control" name="payer_cbe_account" value="{{ old('payer_cbe_account') }}">
                                </div>

                                <button class="cmn-btn w-100" type="submit">@lang('Verify & Confirm Deposit')</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

