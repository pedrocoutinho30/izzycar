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

        // Sem permissão de leads (rota partilhada) não há contagens nem cartões.
        $veLeads = $permissions->scope($user, 'leads', 'view') !== null;

        return view('admin.mobile.dashboard', [
            'veLeads' => $veLeads,
            'leadsNovas' => !$veLeads ? 0 : $leads()->where('lead_status', 'nova')->count(),
            'followupsHoje' => !$veLeads ? 0 : $leads()->whereDate('next_followup_at', today())->count(),
        ]);
    }
}
