<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;

class GatewayCurrency extends Model {

    protected $hidden = [
        'gateway_parameter',
    ];

    protected $casts = ['status' => 'boolean'];

    // Relation
    public function method() {
        return $this->belongsTo(Gateway::class, 'method_code', 'code');
    }

    public function currencyIdentifier() {
        return $this->name ?? $this->method->name . ' ' . $this->currency;
    }

    public function scopeBaseCurrency() {
        return $this->method->crypto == Status::ENABLE ? 'USD' : $this->currency;
    }

    public function scopeBaseSymbol() {
        return $this->method->crypto == Status::ENABLE ? '$' : $this->symbol;
    }

    public function scopeActiveForDeposit($query) {
        return $query->whereNotNull('method_code')
            ->whereNotNull('currency')
            ->whereHas('method', function ($gate) {
                $gate->where('status', Status::ENABLE);
            });
    }

    public function isConfiguredForDeposit() {
        if (!$this->method || $this->method->status != Status::ENABLE) {
            return false;
        }

        if (!$this->method_code || !$this->currency) {
            return false;
        }

        if ((int) $this->method_code >= 1000) {
            return true;
        }

        $requiredKeys = $this->requiredGatewayParamKeys();
        if (empty($requiredKeys)) {
            return true;
        }

        $resolvedParams = $this->resolvedGatewayParams();

        foreach ($requiredKeys as $key) {
            if (!$this->hasConfiguredValue($resolvedParams[$key] ?? null)) {
                return false;
            }
        }

        return true;
    }

    private function requiredGatewayParamKeys() {
        $rawParams = $this->method->getRawOriginal('gateway_parameters') ?? $this->method->gateway_parameters;

        if (is_string($rawParams)) {
            $rawParams = json_decode($rawParams, true);
        } elseif (is_object($rawParams)) {
            $rawParams = (array) $rawParams;
        }

        if (!is_array($rawParams) || empty($rawParams)) {
            return [];
        }

        return array_keys($rawParams);
    }

    private function resolvedGatewayParams() {
        $currencyParams = $this->decodeParamPayload($this->getRawOriginal('gateway_parameter'));
        $gatewayParams  = $this->decodeParamPayload($this->method->getRawOriginal('gateway_parameters') ?? $this->method->gateway_parameters);

        return array_replace($gatewayParams, $currencyParams);
    }

    private function decodeParamPayload($payload) {
        if (is_null($payload) || $payload === '') {
            return [];
        }

        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        } elseif (is_object($payload)) {
            $payload = (array) $payload;
        }

        if (!is_array($payload)) {
            return [];
        }

        $normalized = [];
        foreach ($payload as $key => $value) {
            if (is_object($value)) {
                $value = (array) $value;
            }

            if (is_array($value) && array_key_exists('value', $value)) {
                $normalized[$key] = $value['value'];
            } else {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    private function hasConfiguredValue($value) {
        if (is_array($value)) {
            if (array_key_exists('value', $value)) {
                return $this->hasConfiguredValue($value['value']);
            }

            foreach ($value as $nestedValue) {
                if ($this->hasConfiguredValue($nestedValue)) {
                    return true;
                }
            }

            return false;
        }

        if (is_bool($value)) {
            return true;
        }

        if (is_numeric($value)) {
            return (float) $value != 0;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return false;
        }

        $lowerValue = strtolower($value);
        if (in_array($lowerValue, ['null', 'none', 'n/a', 'na'], true)) {
            return false;
        }

        if (preg_match('/^[-_*]+$/', $value)) {
            return false;
        }

        return stripos($value, 'replace_with') === false;
    }
}
