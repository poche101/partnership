<?php

namespace App\Http\Controllers;

use App\Models\PartnershipArm;
use Illuminate\Http\Request;

class ArmController extends Controller
{
    public function index()
    {
        return view('arms.index', ['arms' => PartnershipArm::orderBy('sort_order')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:100', 'unique:partnership_arms,key', 'regex:/^[a-z0-9_]+$/'],
            'label' => ['required', 'string', 'max:255'],
        ]);

        $max = PartnershipArm::max('sort_order') ?? 0;
        PartnershipArm::create([...$data, 'sort_order' => $max + 1, 'enabled' => true]);

        return back()->with('success', 'Arm added.');
    }

    public function update(Request $request, PartnershipArm $arm)
    {
        $data = $request->validate([
            'key' => ['sometimes', 'string', 'max:100'],
            'label' => ['sometimes', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer'],
            'enabled' => ['sometimes', 'boolean'],
        ]);

        $arm->update($data);

        return back()->with('success', 'Arm updated.');
    }
}
