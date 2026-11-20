<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MarketingPostStatus;
use App\Enums\MarketingPostType;
use App\Http\Controllers\Controller;
use App\Services\MarketingDashboardStats;

class MarketingDashboardController extends Controller
{
    public function index(MarketingDashboardStats $stats)
    {
        return view('admin.marketing.dashboard', [
            's' => $stats->build(),
            'statuses' => MarketingPostStatus::cases(),
            'types' => MarketingPostType::cases(),
        ]);
    }
}
