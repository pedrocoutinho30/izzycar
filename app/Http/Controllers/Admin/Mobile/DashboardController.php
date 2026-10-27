<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Permissions\PermissionService;

class DashboardController extends Controller
{
    public function index(PermissionService $permissions)
    {
        $user = auth()->user();

        // Leads visíveis ao utilizador (âmbito "todas" ou "próprias").
        $leads = fn () => $permissions->applyScope(Client::query()->where('is_lead', true), $user, 'leads');

        return view('admin.mobile.dashboard', [
            'leadsNovas' => $leads()->where('lead_status', 'nova')->count(),
            'followupsHoje' => $leads()->whereDate('next_followup_at', today())->count(),
        ]);
    }
}
