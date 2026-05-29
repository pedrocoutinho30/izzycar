<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Inspeção #{{ $inspection->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            background: #fff;
        }

        .page-break {
            page-break-after: always;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            margin-bottom: 30px;
            border-radius: 4px;
        }

        .header-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .header-subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 15px;
        }

        .vehicle-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.3);
        }

        .vehicle-details {
            flex: 1;
        }

        .vehicle-model {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .vehicle-specs {
            font-size: 12px;
            opacity: 0.9;
        }

        .score-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
        }

        .score-value {
            font-size: 36px;
            font-weight: bold;
        }

        .score-label {
            font-size: 11px;
            opacity: 0.9;
        }

        /* Stats */
        .stats {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-card {
            flex: 1;
            min-width: 150px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        /* Section */
        .section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            padding: 12px 15px;
            background: #f5f5f5;
            border-left: 4px solid #667eea;
            margin-bottom: 15px;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table thead {
            background: #f9f9f9;
            border-bottom: 2px solid #ddd;
        }

        .items-table th {
            padding: 10px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            color: #333;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }

        .category-row {
            background: #f9f9f9;
            font-weight: 600;
            padding: 8px 10px !important;
        }

        /* Badge styles */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            text-align: center;
        }

        .badge-ok {
            background: #d4edda;
            color: #155724;
        }

        .badge-atencao {
            background: #fff3cd;
            color: #856404;
        }

        .badge-problema {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-nao_verificado {
            background: #e2e3e5;
            color: #383d41;
        }

        /* Problem section */
        .problem-item {
            margin-bottom: 20px;
            padding: 15px;
            border-left: 4px solid #dc3545;
            background: #fff5f5;
        }

        .problem-title {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .problem-notes {
            font-size: 12px;
            margin-bottom: 10px;
            color: #555;
        }

        /* Parent inspection diff */
        .diff-section {
            margin-bottom: 20px;
            padding: 15px;
            border-left: 4px solid #17a2b8;
            background: #e7f3f8;
        }

        .diff-title {
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .diff-table {
            width: 100%;
            border-collapse: collapse;
        }

        .diff-table tr {
            border-bottom: 1px solid #ccc;
        }

        .diff-table td {
            padding: 8px;
            font-size: 11px;
        }

        /* General photos */
        .photos-section {
            margin-top: 20px;
        }

        .photo-grid {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .photo-item {
            flex: 0 1 calc(33.333% - 10px);
            text-align: center;
        }

        .photo-item img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Observations */
        .observations {
            margin-top: 30px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    {{-- Header with vehicle info and score --}}
    <div class="header">
        <div class="header-title">Relatório de Inspeção Automóvel</div>
        <div class="header-subtitle">Inspeção #{{ $inspection->id }} - {{ $inspection->created_at->format('d/m/Y H:i') }}</div>
        
        <div class="vehicle-info">
            <div class="vehicle-details">
                <div class="vehicle-model">{{ trim(($inspection->brand ?? '') . ' ' . ($inspection->model ?? '')) ?: '— sem identificação' }}</div>
                <div class="vehicle-specs">
                    @if($inspection->sub_model){{ $inspection->sub_model }} · @endif
                    @if($inspection->year){{ $inspection->year }} · @endif
                    @if($inspection->registration){{ $inspection->registration }} · @endif
                    @if($inspection->fuel){{ $inspection->fuel }}@endif
                </div>
            </div>
            <div class="score-circle">
                <div class="score-value">{{ $summary['percentage'] }}%</div>
                <div class="score-label">Pontuação</div>
            </div>
        </div>
    </div>

    {{-- Stats cards --}}
    <div class="stats">
        <div class="stat-card">
            <div class="stat-label">Total de Itens</div>
            <div class="stat-value">{{ $summary['total_items'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Verificados</div>
            <div class="stat-value">{{ $summary['verified_items'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Problemas</div>
            <div class="stat-value">{{ $summary['problem_items'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pontos</div>
            <div class="stat-value">{{ $summary['total_points'] ?? 0 }}/{{ $summary['max_points'] ?? 0 }}</div>
        </div>
    </div>

    {{-- Parent inspection diff --}}
    @if($inspection->parent)
    <div class="section">
        <div class="section-title">📋 Revisão da Inspeção #{{ $inspection->parent_inspection_id }}</div>
        <p style="margin-bottom: 15px; font-size: 12px;">
            Esta inspeção é uma revisão da <strong>Inspeção #{{ $inspection->parent_inspection_id }}</strong> 
            (criada em {{ $inspection->parent->created_at->format('d/m/Y') }}).
        </p>

        @if($changedEntries->count())
            <div class="diff-section">
                <div class="diff-title">{{ $changedEntries->count() }} {{ $changedEntries->count() === 1 ? 'ponto alterado' : 'pontos alterados' }} face à inspeção anterior:</div>
                <table class="diff-table">
                    <tr style="background: #d4e6f1; font-weight: 600;">
                        <td>Item</td>
                        <td>Estado anterior</td>
                        <td>Estado actual</td>
                    </tr>
                    @foreach($changedEntries as $change)
                    <tr>
                        <td>{{ $change['item']->name ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $change['old_status'] }}">
                                {{ ['nao_verificado' => 'N. V.', 'ok' => 'OK', 'atencao' => 'Atenção', 'problema' => 'Problema'][$change['old_status']] ?? $change['old_status'] }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $change['new_status'] }}">
                                {{ ['nao_verificado' => 'N. V.', 'ok' => 'OK', 'atencao' => 'Atenção', 'problema' => 'Problema'][$change['new_status']] ?? $change['new_status'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        @else
            <p style="font-size: 12px; color: #155724; background: #d4edda; padding: 10px; border-radius: 4px;">
                ✓ Nenhuma alteração de estado face à inspeção anterior.
            </p>
        @endif
    </div>
    @endif

    {{-- Verified items by category --}}
    @if($categoriesWithEntries->count())
    <div class="section">
        <div class="section-title">✓ Itens Verificados</div>
        @foreach($categoriesWithEntries as $catData)
            <div style="margin-bottom: 20px;">
                <div style="font-weight: 600; margin-bottom: 10px; font-size: 13px; color: #667eea;">
                    {{ $catData['category']->name }}
                </div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="width: 100px;">Estado</th>
                            <th style="width: 80px;">Prioridade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($catData['verifiedItems'] as $verified)
                        <tr>
                            <td>{{ $verified['item']->name }}</td>
                            <td>
                                <span class="badge badge-{{ $verified['entry']->status }}">
                                    {{ ['ok' => 'OK', 'atencao' => 'Atenção', 'problema' => 'Problema'][$verified['entry']->status] ?? $verified['entry']->status }}
                                </span>
                            </td>
                            <td>
                                @if($verified['entry']->status !== 'ok' && $verified['entry']->priority)
                                    <span class="badge" style="background: #e8f4f8; color: #333; border: 1px solid #b3d9e6;">
                                        {{ ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta'][$verified['entry']->priority] ?? $verified['entry']->priority }}
                                    </span>
                                @else
                                    <span style="color: #999;">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
    @endif

    {{-- Problems section --}}
    @if($problemEntries->count())
    <div class="section">
        <div class="section-title">⚠️ Pontos Problemáticos</div>
        @foreach($problemEntries as $problem)
        <div class="problem-item">
            <div class="problem-title">
                {{ $problem->item->name }} 
                <span class="badge" style="margin-left: 8px; background: #fff; color: #721c24; border: 1px solid #f5c6cb;">
                    {{ ['baixa' => 'Prioridade Baixa', 'media' => 'Prioridade Média', 'alta' => 'Prioridade Alta'][$problem->priority] ?? $problem->priority }}
                </span>
            </div>
            @if($problem->notes)
            <div class="problem-notes"><strong>Notas:</strong> {{ $problem->notes }}</div>
            @endif
            @if($problem->media->count())
            <div style="margin-top: 10px;">
                <strong style="font-size: 11px; display: block; margin-bottom: 8px;">Fotos:</strong>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    @foreach($problem->media as $media)
                    <img src="{{ storage_path('app/' . $media->path) }}" 
                         style="max-width: 150px; max-height: 150px; border: 1px solid #ddd; border-radius: 4px;" />
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- General photos --}}
    @if($inspection->generalMedia->count())
    <div class="section">
        <div class="section-title">📸 Fotos do Veículo</div>
        <div class="photo-grid">
            @foreach($inspection->generalMedia as $photo)
            <div class="photo-item">
                <img src="{{ storage_path('app/' . $photo->path) }}" />
                @if($photo->description)
                <p style="font-size: 11px; margin-top: 5px; color: #666;">{{ $photo->description }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>Relatório gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Izzycar - Gestão de Inspeções Automóvel</p>
    </div>
</body>
</html>
