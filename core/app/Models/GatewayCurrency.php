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

        // Allow each gateway's ProcessController to define its own configuration check
        $alias = $this->method->alias ?? null;
        if ($alias && preg_match('/^\w+$/', $alias)) {
            $processorClass = 'App\\Http\\Controllers\\Gateway\\' . $alias . '\\ProcessController';
            if (class_exists($processorClass) && method_exists($processorClass, 'isConfigured')) {
                return $processorClass::isConfigured($this);
            }
        }

        return true;
    }
}
