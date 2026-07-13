<?php

namespace App\Http\Controllers;

use App\Models\GivingAlert;
use App\Models\GivingAlertThreshold;
use App\Support\Arms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $churchIds = $user->visibleChurchIds();

        $query = GivingAlert::with(['partner', 'church.groupChurch'])->latest('created_at')->limit(200);
        if ($churchIds !== null) {
            $query->whereIn('church_id', $churchIds);
        }

        $thresholds = GivingAlertThreshold::all()->keyBy('arm_key');

        return view('alerts.index', [
            'alerts' => $query->get(),
            'arms' => Arms::enabled(),
            'thresholds' => $thresholds,
        ]);
    }

    public function saveThreshold(Request $request)
    {
        $data = $request->validate([
            'arm_key' => ['required', 'string'],
            'threshold_espees' => ['required', 'numeric', 'gt:0'],
            'enabled' => ['sometimes', 'boolean'],
        ]);

        GivingAlertThreshold::updateOrCreate(
            ['arm_key' => $data['arm_key']],
            ['threshold_espees' => $data['threshold_espees'], 'enabled' => $request->boolean('enabled', true)]
        );

        return back()->with('success', 'Threshold saved.');
    }

    public function acknowledge(GivingAlert $alert)
    {
        $user = Auth::user();
        $churchIds = $user->visibleChurchIds();
        if ($churchIds !== null && ! in_array($alert->church_id, $churchIds, true)) {
            abort(403);
        }

        $alert->update([
            'acknowledged' => true,
            'acknowledged_by' => $user->id,
            'acknowledged_at' => now(),
        ]);

        return back()->with('success', 'Alert acknowledged.');
    }
}
