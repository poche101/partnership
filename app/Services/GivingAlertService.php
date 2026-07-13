<?php

namespace App\Services;

use App\Models\GivingAlert;
use App\Models\GivingAlertThreshold;
use App\Models\PartnershipEntry;

class GivingAlertService
{
    /**
     * After a partnership entry is saved, raise an alert for any arm whose
     * amount on this entry meets or exceeds its configured threshold.
     */
    public function checkThresholds(PartnershipEntry $entry): void
    {
        $thresholds = GivingAlertThreshold::where('enabled', true)->get()->keyBy('arm_key');

        if ($thresholds->isEmpty()) {
            return;
        }

        foreach (PartnershipEntry::ARM_KEYS as $key) {
            $amount = (float) ($entry->{$key} ?? 0);
            $threshold = $thresholds->get($key);

            if (! $threshold || $amount < (float) $threshold->threshold_espees) {
                continue;
            }

            GivingAlert::create([
                'entry_id' => $entry->id,
                'partner_id' => $entry->partner_id,
                'church_id' => $entry->church_id,
                'arm_key' => $key,
                'amount_espees' => $amount,
                'threshold_espees' => $threshold->threshold_espees,
            ]);
        }
    }
}
