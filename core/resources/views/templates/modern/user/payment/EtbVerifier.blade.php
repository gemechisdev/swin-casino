@extends('Template::layouts.master')

@section('content')
    <div class="pt-120 pb-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card custom--card">
                        <div class="card-header text-center border-0 bg-transparent">
                            <h5 class="title mt-2">@lang('ETB Verifier')</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $hasTelebirr = $data->has_telebirr ?? false;
                                $hasCbe      = $data->has_cbe ?? false;
                                $bothAvailable = $hasTelebirr && $hasCbe;
                                $autoMethod  = $bothAvailable ? null : ($hasCbe ? 'cbe' : 'telebirr');
                            @endphp

                            <div class="alert alert-primary">
                                <p class="mb-0">
                                    @if($bothAvailable)
                                        @lang('Please select your payment method (Telebirr or CBE), complete your transfer, and submit the transaction reference below.')
                                    @elseif($hasCbe)
                                        @lang('Please send via CBE bank transfer to our account and submit the transaction reference below.')
                                    @else
                                        @lang('Please send via Telebirr to our account and submit the transaction reference below.')
                                    @endif
                                </p>
                            </div>

                            <form class="disableSubmission appPayment" method="{{ $data->method }}" action="{{ $data->url }}">
                                @csrf
                                <input type="hidden" name="track" value="{{ $data->track }}">

                                @if($bothAvailable)
                                    <div class="form-group mb-3">
                                        <label class="form-label">@lang('Payment Method')</label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" id="methodTelebirr" value="telebirr" checked>
                                                <label class="form-check-label" for="methodTelebirr">@lang('Telebirr')</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" id="methodCbe" value="cbe">
                                                <label class="form-check-label" for="methodCbe">@lang('CBE')</label>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <input type="hidden" name="payment_method" value="{{ $autoMethod }}">
                                @endif

                                <div class="form-group mb-3">
                                    <label class="form-label">@lang('Transaction/Reference Number')</label>
                                    <input type="text" class="form-control form--control" name="reference" required placeholder="{{ $hasCbe && !$hasTelebirr ? 'e.g. FT24262KAYZ0' : ($hasTelebirr && !$hasCbe ? 'e.g. MP210924001234' : '') }}" value="{{ old('reference') }}">
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">@lang('Payer Name')</label>
                                    <input type="text" class="form-control form--control" name="payer_name" required value="{{ old('payer_name') }}">
                                </div>

                                @if($hasTelebirr)
                                    <div class="form-group mb-3 telebirr-field" @if($bothAvailable) style="display:block;" @endif>
                                        <label class="form-label">@lang('Your Telebirr Phone Number')</label>
                                        <input type="text" class="form-control form--control" name="payer_telebirr_no" value="{{ old('payer_telebirr_no') }}">
                                    </div>
                                @endif

                                @if($hasCbe)
                                    <div class="form-group mb-3 cbe-field" @if($bothAvailable) style="display:none;" @endif>
                                        <label class="form-label">@lang('Your CBE Account Number')</label>
                                        <input type="text" class="form-control form--control" name="payer_cbe_account" value="{{ old('payer_cbe_account') }}">
                                    </div>
                                @endif

                                <button class="btn btn--base w-100 mt-3" type="submit">@lang('Verify & Confirm Deposit')</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@if($data->has_telebirr && $data->has_cbe)
@push('script')
<script>
    (function () {
        var telebirrFields = document.querySelectorAll('.telebirr-field');
        var cbeFields      = document.querySelectorAll('.cbe-field');

        function toggleFields(method) {
            telebirrFields.forEach(function (el) { el.style.display = method === 'telebirr' ? 'block' : 'none'; });
            cbeFields.forEach(function (el)      { el.style.display = method === 'cbe'      ? 'block' : 'none'; });
        }

        document.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
            radio.addEventListener('change', function () { toggleFields(this.value); });
        });

        // Auto-detect from reference input and pre-select method
        var referenceInput = document.querySelector('input[name="reference"]');
        if (referenceInput) {
            referenceInput.addEventListener('input', function () {
                var val = this.value.trim().toUpperCase();
                if (/^FT/.test(val)) {
                    var cbeRadio = document.getElementById('methodCbe');
                    if (cbeRadio) { cbeRadio.checked = true; toggleFields('cbe'); }
                } else if (/^MP/.test(val)) {
                    var telebirrRadio = document.getElementById('methodTelebirr');
                    if (telebirrRadio) { telebirrRadio.checked = true; toggleFields('telebirr'); }
                }
            });
        }
    })();
</script>
@endpush
@endif
