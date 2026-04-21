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
    public static function process($deposit)
    {
        $alias          = $deposit->gateway->alias;
        $send['track']  = $deposit->trx;
        $send['view']   = 'user.payment.' . $alias;
        $send['method'] = 'post';
        $send['url']    = route('ipn.' . $alias);
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
            'reference' => 'required|string|max:100',
            'payer_name' => 'required|string|max:100',
            'payer_telebirr_no' => 'nullable|string|max:30',
            'payer_cbe_account' => 'nullable|string|max:40',
        ]);

        $reference = strtoupper(trim($request->reference));
        $payerName = trim($request->payer_name);

        $gatewayParameter = json_decode($deposit->gatewayCurrency()->gateway_parameter);
        $baseUrl = rtrim(trim($gatewayParameter->api_base ?? 'https://verifyapi.leulzenebe.pro'), '/');
        $apiKey = trim($gatewayParameter->api_key ?? '');
        $telebirrAccount = trim($gatewayParameter->telebirr_account ?? '');
        $cbeAccount = trim($gatewayParameter->cbe_account ?? '');

        if (!$apiKey) {
            $notify[] = ['error', 'Gateway configuration is incomplete'];
            if ($apiRequest) {
                return responseError('configuration_missing', $notify);
            }
            return redirect($deposit->failed_url)->withNotify($notify);
        }

        $isCbeReference = (bool) preg_match('/^FT[A-Z0-9]{10}$/i', $reference);
        $endpoint = $isCbeReference ? '/verify-cbe' : '/verify-telebirr';
        $payload = ['reference' => $reference];

        if ($isCbeReference) {
            $payerCbeAccount = trim((string) $request->payer_cbe_account);
            if (!$payerCbeAccount) {
                $notify[] = ['error', 'CBE account number is required for this reference'];
                if ($apiRequest) {
                    return responseError('validation_error', $notify);
                }
                return back()->withNotify($notify);
            }
            $payload['accountSuffix'] = $this->deriveCbeAccountSuffix($payerCbeAccount);
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
                $notify[] = ['error', 'Name on payment doesn’t match your registered name.'];
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
                $notify[] = ['error', 'Name on payment doesn’t match your registered name.'];
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
            'reference' => $reference,
            'gateway_type' => $isCbeReference ? 'cbe' : 'telebirr',
            'payer_name' => $payerName,
            'payer_telebirr_no' => $request->payer_telebirr_no,
            'payer_cbe_account' => $request->payer_cbe_account,
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

    private function deriveCbeAccountSuffix(string $account): string
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
        $storedTail = strlen($storedNumeric) >= 4 ? substr($storedNumeric, -4) : $storedNumeric;
        preg_match('/(\d+)\s*$/', $apiAccount, $match);
        $apiTailDigits = $match[1] ?? '';
        if (!$apiTailDigits) {
            return false;
        }
        $apiTail = strlen($apiTailDigits) >= 4 ? substr($apiTailDigits, -4) : $apiTailDigits;
        return $storedTail === $apiTail;
    }

    private function namesMatch(string $registeredName, string $apiName): bool
    {
        $normalize = function ($name) {
            return strtolower(preg_replace('/\s+/', '', trim((string) $name)));
        };
        $a = $normalize($registeredName);
        $b = $normalize($apiName);
        if (!$a || !$b) {
            return false;
        }
        return substr($a, 0, 4) === substr($b, 0, 4);
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
