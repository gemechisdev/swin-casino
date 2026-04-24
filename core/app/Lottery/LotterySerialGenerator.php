<?php

namespace App\Lottery;

use App\Models\LotteryTicket;
use Illuminate\Support\Str;

class LotterySerialGenerator
{
    /**
     * Generate a unique serial for a lottery campaign.
     * 
     * @param int $campaignId
     * @param int $length
     * @param string $prefix
     * @return string
     */
    public static function generate(int $campaignId, int $length = 10, string $prefix = ''): string
    {
        $serial = self::createSerial($length, $prefix);

        // Check for collisions in the same campaign
        while (LotteryTicket::where('lottery_campaign_id', $campaignId)->where('serial', $serial)->exists()) {
            $serial = self::createSerial($length, $prefix);
        }

        return $serial;
    }

    /**
     * Internal helper to create a random string.
     */
    protected static function createSerial(int $length, string $prefix): string
    {
        $randomPart = strtoupper(Str::random($length));
        return $prefix ? $prefix . '-' . $randomPart : $randomPart;
    }

    /**
     * Generate a batch of unique serials.
     * Useful if we ever want to pre-generate tickets or sell in bundles.
     */
    public static function generateBatch(int $campaignId, int $count, int $length = 10, string $prefix = ''): array
    {
        $serials = [];
        for ($i = 0; $i < $count; $i++) {
            $serials[] = self::generate($campaignId, $length, $prefix);
        }
        return $serials;
    }
}
