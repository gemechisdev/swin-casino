<?php

namespace App\Http\Controllers\Gateway\EtbVerifier;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Gateway\PaymentController;
use App\Lib\CurlRequest;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProcessController extends Controller
{
    private const ACCOUNT_TAIL_DIGITS = 4;
    private const NAME_PREFIX_LENGTH = 4;
    private const NAME_SIMILARITY_THRESHOLD = 80;

    public static function isConfigured(\App\Models\GatewayCurrency $gatewayCurrency): bool
    {
        // Resolve credentials by merging gateway-level template values (gateway_parameters)
        // with per-currency overrides (gateway_parameter), same as resolvedGatewayParams().
        $params = self::resolveParams($gatewayCurrency);

        $apiKey = trim($params['api_key'] ?? '');
        if (!$apiKey) {
            return false;
        }

        $hasTelebirr = !empty(trim($params['telebirr_account'] ?? ''));
        $hasCbe      = !empty(trim($params['cbe_account'] ?? ''));

        return $hasTelebirr || $hasCbe;
    }

    /**
     * Resolve flat parameter values from both the gateway-level template
     * (gateway_parameters on Gateway) and the per-currency overrides
     * (gateway_parameter on GatewayCurrency), with per-currency taking precedence.
     */
    private static function resolveParams(\App\Models\GatewayCurrency $gatewayCurrency): array
    {
        $merged = [];

        // 1. Start with gateway-level template values (global params)
        $method = $gatewayCurrency->method;
        if ($method) {
            $rawTemplate = $method->getRawOriginal('gateway_parameters') ?? $method->gateway_parameters;
            if ($rawTemplate && is_string($rawTemplate)) {
                $template = json_decode($rawTemplate, true);
                if (is_array($template)) {
                    foreach ($template as $key => $val) {
                        // Template entries look like {"api_key": {"title": "...", "value": "actual-value"}}
                        if (is_array($val) && array_key_exists('value', $val)) {
                            $merged[$key] = $val['value'];
                        } else {
                            $merged[$key] = $val;
                        }
                    }
                }
            }
        }

        // 2. Override with per-currency flat values (gateway_parameter)
        $rawCurrency = $gatewayCurrency->getRawOriginal('gateway_parameter') ?? $gatewayCurrency->gateway_parameter;
        if ($rawCurrency && is_string($rawCurrency)) {
            $currencyParams = json_decode($rawCurrency, true);
            if (is_array($currencyParams)) {
                foreach ($currencyParams as $key => $val) {
                    // Per-currency entries are flat: {"api_key": "actual-value"}
                    if (is_array($val) && array_key_exists('value', $val)) {
                        $merged[$key] = $val['value'];
                    } else {
                        $merged[$key] = $val;
                    }
                }
            }
        }

        return $merged;
    }

    public static function process($deposit)
    {
        $alias     = $deposit->gateway->alias;
        $params    = json_decode($deposit->gatewayCurrency()->gateway_parameter);
        $apiKey    = trim($params->api_key ?? '');
        $hasTelebirr = !empty(trim($params->telebirr_account ?? ''));
        $hasCbe      = !empty(trim($params->cbe_account ?? ''));

        if (!$apiKey || (!$hasTelebirr && !$hasCbe)) {
            $send['error']   = true;
            $send['message'] = 'Gateway is not fully configured. Please contact support.';
            return json_encode($send);
        }

        $send['track']       = $deposit->trx;
        $send['view']        = 'user.payment.' . $alias;
        $send['method']      = 'post';
        $send['url']         = route('ipn.' . $alias);
        $send['has_telebirr'] = $hasTelebirr;
        $send['has_cbe']      = $hasCbe;
        return json_encode($send);
    }

    public function ipn(Request $request)
    {
        $track = $request->track;
        $deposit = Deposit::where('trx', $track)->orderBy('id', 'DESC')->first();

        if (!$deposit) {
            $notify[] = ['error', 'Invalid request'];
            return back()->withNotify($notify);
        }

        $apiRequest = $deposit->is_web;
        if ($deposit->status == Status::PAYMENT_SUCCESS) {
            $notify[] = ['error', 'Invalid request'];
            if ($apiRequest) {
                return responseError('invalid_request', $notify);
            }
            return redirect($deposit->failed_url)->withNotify($notify);
        }

        $request->validate([
            'reference'      => 'required|string|max:100',
            'payer_name'     => 'required|string|max:100',
            'payment_method' => 'nullable|string|in:telebirr,cbe',
            'payer_telebirr_no'  => 'nullable|string|max:30',
            'payer_cbe_account'  => 'nullable|string|max:40',
        ]);

        $reference = strtoupper(trim($request->reference));
        $payerName = trim($request->payer_name);

        $gatewayParameter = json_decode($deposit->gatewayCurrency()->gateway_parameter);
        $baseUrl         = rtrim(trim($gatewayParameter->api_base ?? 'https://verifyapi.leulzenebe.pro'), '/');
        $apiKey          = trim($gatewayParameter->api_key ?? '');
        $telebirrAccount = trim($gatewayParameter->telebirr_account ?? '');
        $cbeAccount      = trim($gatewayParameter->cbe_account ?? '');

        if (!$apiKey) {
            $notify[] = ['error', 'Gateway configuration is incomplete'];
            if ($apiRequest) {
                return responseError('configuration_missing', $notify);
            }
            return redirect($deposit->failed_url)->withNotify($notify);
        }

        // Determine which method to use.
        // Explicit user selection is the primary source of truth.
        // Auto-detect from prefix (MP/FT) is used only when no explicit selection is provided.
        $hasTelebirr = $telebirrAccount !== '';
        $hasCbe      = $cbeAccount !== '';

        if ($request->filled('payment_method')) {
            $selectedMethod = $request->payment_method;
        } else {
            // Auto-detect from reference prefix as convenience hint
            $selectedMethod = preg_match('/^FT[A-Z0-9]{10}$/i', $reference) ? 'cbe' : 'telebirr';
        }

        // Fall back to whichever method is actually configured if the selected one isn't
        if ($selectedMethod === 'cbe' && !$hasCbe && $hasTelebirr) {
            $selectedMethod = 'telebirr';
        } elseif ($selectedMethod === 'telebirr' && !$hasTelebirr && $hasCbe) {
            $selectedMethod = 'cbe';
        }

        if (($selectedMethod === 'cbe' && !$hasCbe) || ($selectedMethod === 'telebirr' && !$hasTelebirr)) {
            $notify[] = ['error', 'The selected payment method is not available.'];
            if ($apiRequest) {
                return responseError('method_unavailable', $notify);
            }
            return back()->withNotify($notify);
        }

        $isCbeReference = $selectedMethod === 'cbe';
        $endpoint = $isCbeReference ? '/verify-cbe' : '/verify-telebirr';
        $payload  = ['reference' => $reference];

        if ($isCbeReference) {
            $payerCbeAccount = trim((string) $request->payer_cbe_account);
            if (!$payerCbeAccount) {
                $notify[] = ['error', 'CBE account number is required for CBE transfers'];
                if ($apiRequest) {
                    return responseError('validation_error', $notify);
                }
                return back()->withNotify($notify);
            }
            $payload['accountSuffix'] = $this->getCbeAccountSuffixDigits($payerCbeAccount);
        }

        try {
            $response = CurlRequest::curlPostContent(
                $baseUrl . $endpoint,
                json_encode($payload),
                [
                    "X-API-Key: $apiKey",
                    "Content-Type: application/json",
                    "Accept: application/json",
                ]
            );
        } catch (\Exception $exception) {
            $notify[] = ['error', 'Verification service unavailable. Please try again.'];
            if ($apiRequest) {
                return responseError('service_unavailable', $notify);
            }
            return redirect($deposit->failed_url)->withNotify($notify);
        }

        $decoded = json_decode($response, true);
        if (!$decoded || !($decoded['success'] ?? false)) {
            $notify[] = ['error', $decoded['message'] ?? $decoded['error'] ?? 'Transaction not found. Check your TxID and try again.'];
            if ($apiRequest) {
                return responseError('verification_failed', $notify);
            }
            return back()->withNotify($notify);
        }

        if ($isCbeReference) {
            $apiReceiverAccount = $decoded['receiverAccount'] ?? '';
            if (!$this->accountTailMatch($cbeAccount, $apiReceiverAccount)) {
                $notify[] = ['error', 'Payment was not sent to the correct account.'];
                if ($apiRequest) {
                    return responseError('merchant_account_mismatch', $notify);
                }
                return back()->withNotify($notify);
            }

            $payerCbeAccount = trim((string) $request->payer_cbe_account);
            $apiPayerAccount = $decoded['payerAccount'] ?? '';
            if (!$this->accountTailMatch($payerCbeAccount, $apiPayerAccount)) {
                $notify[] = ['error', 'Payment was made from a different account.'];
                if ($apiRequest) {
                    return responseError('payer_account_mismatch', $notify);
                }
                return back()->withNotify($notify);
            }

            $apiPayerName = $decoded['payer'] ?? '';
            if (!$this->namesMatch($payerName, $apiPayerName)) {
                $notify[] = ['error', 'Submitted payer name does not match the name on the payment.'];
                if ($apiRequest) {
                    return responseError('name_mismatch', $notify);
                }
                return back()->withNotify($notify);
            }

            $paidAmount = $this->parseAmount($decoded['amount'] ?? '');
        } else {
            $teleData = $decoded['data'] ?? [];
            $apiReceiverAccount = $teleData['creditedPartyAccountNo'] ?? '';
            if (!$this->accountTailMatch($telebirrAccount, $apiReceiverAccount)) {
                $notify[] = ['error', 'Payment was not sent to the correct account.'];
                if ($apiRequest) {
                    return responseError('merchant_account_mismatch', $notify);
                }
                return back()->withNotify($notify);
            }

            $payerTelebirrNo = trim((string) $request->payer_telebirr_no);
            $apiPayerTelebirrNo = $teleData['payerTelebirrNo'] ?? '';
            if ($payerTelebirrNo && !$this->accountTailMatch($payerTelebirrNo, $apiPayerTelebirrNo)) {
                $notify[] = ['error', 'Payment was made from a different account.'];
                if ($apiRequest) {
                    return responseError('payer_account_mismatch', $notify);
                }
                return back()->withNotify($notify);
            }

            $apiPayerName = $teleData['payerName'] ?? '';
            if (!$this->namesMatch($payerName, $apiPayerName)) {
                $notify[] = ['error', 'Submitted payer name does not match the name on the payment.'];
                if ($apiRequest) {
                    return responseError('name_mismatch', $notify);
                }
                return back()->withNotify($notify);
            }

            $paidAmount = $this->parseAmount($teleData['settledAmount'] ?? '');
        }

        if ($paidAmount < (float) $deposit->final_amount) {
            $notify[] = ['error', 'Payment is less than required.'];
            if ($apiRequest) {
                return responseError('insufficient_amount', $notify);
            }
            return back()->withNotify($notify);
        }

        if (!$this->markReferenceUsed($reference, $deposit)) {
            $notify[] = ['error', 'This TxID has already been used.'];
            if ($apiRequest) {
                return responseError('txid_used', $notify);
            }
            return back()->withNotify($notify);
        }

        $deposit->btc_wallet = $reference;
        $deposit->detail = [
            'reference'      => $reference,
            'gateway_type'   => $isCbeReference ? 'cbe' : 'telebirr',
            'payer_name'     => $payerName,
            'payer_telebirr_no'  => $request->payer_telebirr_no,
            'payer_cbe_account'  => $request->payer_cbe_account,
            'verification_response' => $decoded,
        ];
        $deposit->save();

        PaymentController::userDataUpdate($deposit);

        $notify[] = ['success', 'Payment verified and balance added successfully'];
        if ($apiRequest) {
            return responseSuccess('payment_verified', $notify);
        }
        return redirect($deposit->success_url)->withNotify($notify);
    }

    private function getCbeAccountSuffixDigits(string $account): string
    {
        $numericOnly = preg_replace('/\D+/', '', $account);
        return strlen($numericOnly) >= 8 ? substr($numericOnly, -8) : $numericOnly;
    }

    private function accountTailMatch(string $storedAccount, string $apiAccount): bool
    {
        $storedNumeric = preg_replace('/\D+/', '', $storedAccount);
        if (!$storedNumeric) {
            return false;
        }
        $storedTail = strlen($storedNumeric) >= self::ACCOUNT_TAIL_DIGITS ? substr($storedNumeric, -self::ACCOUNT_TAIL_DIGITS) : $storedNumeric;
        preg_match('/(\d+)\s*$/', $apiAccount, $match);
        $apiTailDigits = $match[1] ?? '';
        if (!$apiTailDigits) {
            return false;
        }
        $apiTail = strlen($apiTailDigits) >= self::ACCOUNT_TAIL_DIGITS ? substr($apiTailDigits, -self::ACCOUNT_TAIL_DIGITS) : $apiTailDigits;
        return $storedTail === $apiTail;
    }

    private function namesMatch(string $registeredName, string $apiName): bool
    {
        $normalize = function ($name) {
            $cleaned = preg_replace('/[^a-z0-9]/i', '', trim((string) $name));
            return strtolower($cleaned);
        };
        $a = $normalize($registeredName);
        $b = $normalize($apiName);
        if (!$a || !$b) {
            return false;
        }
        if ($a === $b) {
            return true;
        }

        if (strlen($a) < self::NAME_PREFIX_LENGTH || strlen($b) < self::NAME_PREFIX_LENGTH || substr($a, 0, self::NAME_PREFIX_LENGTH) !== substr($b, 0, self::NAME_PREFIX_LENGTH)) {
            return false;
        }

        similar_text($a, $b, $similarity);
        return $similarity >= self::NAME_SIMILARITY_THRESHOLD;
    }

    private function parseAmount($amount): float
    {
        $cleaned = preg_replace('/[^\d.,]/', '', (string) $amount);
        $cleaned = str_replace(',', '', $cleaned);
        return $cleaned ? (float) $cleaned : 0.0;
    }

    private function markReferenceUsed(string $reference, Deposit $deposit): bool
    {
        if (!Schema::hasTable('etb_verifier_references')) {
            return !Deposit::where('method_code', $deposit->method_code)->where('btc_wallet', $reference)->exists();
        }

        try {
            DB::table('etb_verifier_references')->insert([
                'reference'  => $reference,
                'user_id'    => $deposit->user_id,
                'deposit_id' => $deposit->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
