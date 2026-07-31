<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportsController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $reports = $this->scanReports();
        return view('admin.v2.reports.index', compact('reports'));
    }

    public function download(string $filename): BinaryFileResponse
    {
        // Procura o ficheiro em qualquer subpasta de reports
        $file = null;
        foreach (glob(storage_path('app/reports/*/' . $filename)) as $found) {
            $file = $found;
            break;
        }

        abort_unless($file && file_exists($file), 404);

        return response()->download($file, $filename);
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

    private function scanReports(): array
    {
        $reports = [];
        $path    = storage_path('app/reports');

        if (!is_dir($path)) {
            return [];
        }

        foreach (glob($path . '/*/*.pdf') as $file) {
            $name = basename($file);

            if (preg_match('/^relatorio_(\d{4})_(\d{2})\.pdf$/', $name, $m)) {
                // Mensal
                $reports[] = [
                    'type'     => 'monthly',
                    'type_label' => 'Mensal',
                    'type_color' => 'primary',
                    'name'     => $name,
                    'label'    => \Carbon\Carbon::create($m[1], $m[2], 1)->locale('pt_PT')->translatedFormat('F Y'),
                    'size'     => round(filesize($file) / 1024) . ' KB',
                    'mtime'    => \Carbon\Carbon::createFromTimestamp(filemtime($file)),
                    'sort_key' => $m[1] . $m[2] . '00',
                ];
            } elseif (preg_match('/^relatorio_trimestral_(\d{4})_Q(\d)\.pdf$/', $name, $m)) {
                // Trimestral
                $qLabels = [1 => '1.º Trim.', 2 => '2.º Trim.', 3 => '3.º Trim.', 4 => '4.º Trim.'];
                $reports[] = [
                    'type'       => 'quarterly',
                    'type_label' => 'Trimestral',
                    'type_color' => 'warning',
                    'name'       => $name,
                    'label'      => ($qLabels[(int)$m[2]] ?? "Q{$m[2]}") . ' ' . $m[1],
                    'size'       => round(filesize($file) / 1024) . ' KB',
                    'mtime'      => \Carbon\Carbon::createFromTimestamp(filemtime($file)),
                    'sort_key'   => $m[1] . '0' . ($m[2] * 3) . '50',
                ];
            } elseif (preg_match('/^relatorio_anual_(\d{4})\.pdf$/', $name, $m)) {
                // Anual
                $reports[] = [
                    'type'       => 'annual',
                    'type_label' => 'Anual',
                    'type_color' => 'danger',
                    'name'       => $name,
                    'label'      => 'Ano ' . $m[1],
                    'size'       => round(filesize($file) / 1024) . ' KB',
                    'mtime'      => \Carbon\Carbon::createFromTimestamp(filemtime($file)),
                    'sort_key'   => $m[1] . '1299',
                ];
            }
        }

        usort($reports, fn($a, $b) => strcmp($b['sort_key'], $a['sort_key']));

        return $reports;
    }
}
