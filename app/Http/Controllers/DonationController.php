<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Settings\DonationSettings;

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
        
        $collected = Transaction::where('code', 'LIKE', 'KURON-PEDULI-%')
            // Note: To be precise, we should link transactions to campaigns. 
            // For now, assuming simple filtering or logic. 
            // Ideally, transaction should have campaign_id.
            // But user asked for simple list first.
            // Let's stick to the current "General Donation" logic or update Transaction to have 'campaign_id'.
            // For this iteration, let's allow 'plan_name' to be the Campaign Title or Custom Code.
            ->where('plan_name', $campaign->title) 
            ->where('status', 'approved')
            ->sum('amount');

        $progress = 0;
        if ($campaign->target_amount > 0) {
            $progress = min(100, ($collected / $campaign->target_amount) * 100);
        }

        return view('donation.show', compact('campaign', 'collected', 'progress'));
    }
}
