<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class DonationController extends Controller
{
    public function index()
    {
        $campaigns = \App\Models\DonationCampaign::where('is_active', true)->get();

        return view('donation.index', compact('campaigns'));
    }

    public function show($slug)
    {
        $campaign = \App\Models\DonationCampaign::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $collected = Transaction::where('plan_name', $campaign->title)
            ->where('status', 'approved')
            ->sum('amount');

        $progress = 0;
        if ($campaign->target_amount > 0) {
            $progress = min(100, ($collected / $campaign->target_amount) * 100);
        }

        return view('donation.show', compact('campaign', 'collected', 'progress'));
    }

    public function payment($slug)
    {
        $campaign = \App\Models\DonationCampaign::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('donation.payment', compact('campaign'));
    }
}
