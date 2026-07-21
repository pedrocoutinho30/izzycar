<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportsController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $reportsPath = storage_path('app/reports');
        $reports     = [];

        if (is_dir($reportsPath)) {
            foreach (glob($reportsPath . '/*/relatorio_*.pdf') as $file) {
                $name    = basename($file);
                // relatorio_2025_06.pdf
                if (preg_match('/relatorio_(\d{4})_(\d{2})\.pdf$/', $name, $m)) {
                    $reports[] = [
                        'path'    => $file,
                        'name'    => $name,
                        'year'    => (int) $m[1],
                        'month'   => (int) $m[2],
                        'label'   => \Carbon\Carbon::create($m[1], $m[2], 1)->locale('pt_PT')->translatedFormat('F Y'),
                        'size'    => round(filesize($file) / 1024) . ' KB',
                        'mtime'   => \Carbon\Carbon::createFromTimestamp(filemtime($file)),
                        'key'     => $m[1] . '-' . $m[2],
                    ];
                }
            }
        }

        usort($reports, fn($a, $b) => strcmp($b['key'], $a['key']));

        return view('admin.v2.reports.index', compact('reports'));
    }

    public function download(string $year, string $month): BinaryFileResponse
    {
        $file = storage_path("app/reports/{$year}/relatorio_{$year}_{$month}.pdf");
        abort_unless(file_exists($file), 404);

        $label = \Carbon\Carbon::create($year, $month, 1)->locale('pt_PT')->translatedFormat('F_Y');

        return response()->download($file, "Relatorio_Mensal_{$label}.pdf");
    }

    public function generate(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        Artisan::call('reports:monthly', [
            '--month' => $validated['month'],
            '--email' => config('mail.admin_address', env('MAIL_FROM_ADDRESS', 'geral@izzycar.com')),
        ]);

        return redirect()->route('admin.v2.reports.index')
            ->with('success', 'Relatório gerado e enviado por email com sucesso.');
    }
}
