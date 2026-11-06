<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuickEntryInterpretation;
use App\Permissions\PermissionService;
use Illuminate\Http\Request;

/**
 * Histórico da inserção rápida: frases, propostas e o que foi guardado. Contém
 * texto livre e nomes de clientes, por isso tem permissão própria
 * (quick-entry-history.view) e não passa por logs genéricos.
 */
class QuickEntryHistoryController extends Controller
{
    public function __construct(private PermissionService $permissions)
    {
    }

    public function index(Request $request)
    {
        abort_unless($this->permissions->allows($request->user(), 'quick-entry-history', 'view'), 403);

        $status = $request->input('status');
        $items = QuickEntryInterpretation::with('user:id,name,last_name')
            ->when(in_array($status, [QuickEntryInterpretation::INTERPRETED, QuickEntryInterpretation::CONFIRMED, QuickEntryInterpretation::CANCELLED], true),
                fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.quick-entry.history', compact('items', 'status'));
    }
}
