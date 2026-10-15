<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} — Shibaura Machine</title>
    <style>
        @page {
            margin: 28px 30px 40px 30px;
            size: A4 {{ $type === 'low_rating' ? 'landscape' : 'portrait' }};
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #06539d;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .brand-title {
            font-size: 16px;
            font-weight: bold;
            color: #06539d;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .brand-subtitle {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 4px;
        }
        .meta-table {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 14px;
        }
        .meta-label {
            font-size: 8.5px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }
        .meta-val {
            font-size: 9.5px;
            font-weight: bold;
            color: #1e293b;
        }
        .kpi-strip {
            width: 100%;
            margin-bottom: 14px;
        }
        .kpi-box {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            text-align: center;
        }
        .kpi-box .val {
            font-size: 13px;
            font-weight: bold;
            color: #06539d;
        }
        .kpi-box .lbl {
            font-size: 8px;
            color: #475569;
            text-transform: uppercase;
            font-weight: bold;
            margin-top: 2px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        table.data-table th {
            background-color: #06539d;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 6px 8px;
            border: 1px solid #06539d;
            text-align: left;
        }
        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
            vertical-align: middle;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-danger { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-warning { background-color: #fef3c7; color: #b45309; border: 1px solid #fcd34d; }
        .badge-success { background-color: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-info { background-color: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
        .badge-slate { background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
        
        .footer {
            position: fixed;
            bottom: -25px;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
        .footer-left { float: left; }
        .footer-right { float: right; }
    </style>
</head>
<body>
    <!-- Fixed Footer -->
    <div class="footer">
        <div class="footer-left">
            Shibaura Machine India Private Limited — Confidential Internal Performance Document
        </div>
        <div class="footer-right">
            Generated: {{ $generatedAt }} | Operator: {{ $generatedBy }}
        </div>
    </div>

    <!-- Header Block -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 70%; vertical-align: middle;">
                <div class="brand-title">SHIBAURA MACHINE</div>
                <div class="brand-subtitle">Plant Feedback & Operational Quality Monitoring System</div>
                <div class="report-title">{{ $title }}</div>
            </td>
            <td style="width: 30%; text-align: right; vertical-align: middle;">
                <div style="font-size: 9px; font-weight: bold; color: #06539d;">EXECUTIVE REPORT</div>
                <div style="font-size: 8.5px; color: #64748b;">Date: {{ now()->format('d M Y') }}</div>
            </td>
        </tr>
    </table>

    <!-- Meta Details Block -->
    <table class="meta-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 35%;">
                <div class="meta-label">Filter Period:</div>
                <div class="meta-val">{{ $periodLabel }}</div>
            </td>
            <td style="width: 30%;">
                <div class="meta-label">Total Records:</div>
                <div class="meta-val">{{ $totalCount }}</div>
            </td>
            <td style="width: 35%; text-align: right;">
                <div class="meta-label">Report Category:</div>
                <div class="meta-val">{{ ucfirst(str_replace('_', ' ', $type)) }}</div>
            </td>
        </tr>
    </table>

    @if($type === 'staff')
        <!-- Summary KPI Metrics for Staff Report -->
        <table class="kpi-strip" cellpadding="4" cellspacing="0">
            <tr>
                <td style="width: 25%;">
                    <div class="kpi-box">
                        <div class="val">{{ $rows->sum('visits_count') }}</div>
                        <div class="lbl">Total Tours Handled</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-box">
                        <div class="val">{{ $rows->sum('feedbacks_count') }}</div>
                        <div class="lbl">Feedbacks Collected</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-box">
                        @php
                            $tv = $rows->sum('visits_count');
                            $tf = $rows->sum('feedbacks_count');
                            $rate = $tv > 0 ? min(100, round(($tf / $tv) * 100)) : 0;
                        @endphp
                        <div class="val">{{ $rate }}%</div>
                        <div class="lbl">Response Rate</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-box">
                        @php
                            $valid = $rows->filter(fn($r) => !is_null($r->avg_rating) && $r->avg_rating > 0);
                            $avg = $valid->count() > 0 ? round($valid->avg('avg_rating'), 2) : 0;
                        @endphp
                        <div class="val">{{ number_format($avg, 2) }} / 5.0</div>
                        <div class="lbl">Mean Quality Score</div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Staff Performance Table -->
        <table class="data-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">#</th>
                    <th>Staff / Organizer</th>
                    <th>Department</th>
                    <th class="text-center">Tours</th>
                    <th class="text-center">Feedback</th>
                    <th class="text-center">Response %</th>
                    <th class="text-center">Avg Rating</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $idx => $r)
                    @php
                        $respPct = $r->visits_count > 0 ? min(100, round(($r->feedbacks_count / $r->visits_count) * 100)) : 0;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-bold">{{ $r->name }}</td>
                        <td>{{ $r->department ?: 'General Operations' }}</td>
                        <td class="text-center">{{ $r->visits_count }}</td>
                        <td class="text-center">{{ $r->feedbacks_count }}</td>
                        <td class="text-center text-bold">{{ $respPct }}%</td>
                        <td class="text-center">
                            @if($r->avg_rating)
                                <span class="badge {{ $r->avg_rating >= 4 ? 'badge-success' : ($r->avg_rating >= 3 ? 'badge-info' : 'badge-danger') }}">
                                    ★ {{ number_format((float)$r->avg_rating, 2) }}
                                </span>
                            @else
                                <span style="color: #94a3b8;">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 15px; color: #64748b;">No staff tour performance data found for the selected period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @elseif($type === 'low_rating')
        <!-- Low-Rating Incident Table -->
        <table class="data-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th style="width: 20px;" class="text-center">#</th>
                    <th style="width: 75px;">Date & Time</th>
                    <th style="width: 45px;" class="text-center">Rating</th>
                    <th>Visitor Name</th>
                    <th>Company</th>
                    <th>Host Staff</th>
                    <th>Mode</th>
                    <th style="width: 32%;">Feedback Comments / Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $idx => $f)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $f->submitted_at ? $f->submitted_at->format('d/m/Y H:i') : '—' }}</td>
                        <td class="text-center">
                            <span class="badge badge-danger text-bold">★ {{ $f->overall_rating }}</span>
                        </td>
                        <td class="text-bold">{{ $f->visit?->visitor_name ?? '—' }}</td>
                        <td>{{ $f->visit?->visitor_company ?: '—' }}</td>
                        <td>{{ $f->organizer?->name ?? 'Unassigned' }}</td>
                        <td>{{ $f->is_staff_assisted ? 'Assisted' : 'Direct' }}</td>
                        <td style="color: #991b1b; font-style: italic;">
                            {{ $f->comments ?: 'No written feedback comments' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 15px; color: #64748b;">No low-rating reviews (≤ 2 Stars) recorded for this period. Excellent performance!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @elseif($type === 'pending')
        <!-- Pending Queue Table -->
        <table class="data-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th style="width: 20px;" class="text-center">#</th>
                    <th>Visit Date</th>
                    <th>Visitor ID</th>
                    <th>Visitor Name</th>
                    <th>Company</th>
                    <th>Mobile</th>
                    <th>Host Staff</th>
                    <th class="text-center">Status</th>
                    <th>Time Since Exit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $idx => $v)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $v->visit_date ? $v->visit_date->format('d/m/Y') : '—' }}</td>
                        <td style="font-family: monospace; font-size: 8px;">{{ $v->visitor_code ?: 'VIS-' . $v->id }}</td>
                        <td class="text-bold">{{ $v->visitor_name }}</td>
                        <td>{{ $v->visitor_company ?: '—' }}</td>
                        <td>{{ $v->visitor_mobile ?: '—' }}</td>
                        <td>{{ $v->organizer?->name ?? 'Unassigned' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $v->out_time ? 'badge-info' : 'badge-success' }}">
                                {{ $v->out_time ? 'Checked Out' : 'Inside Plant' }}
                            </span>
                        </td>
                        <td>{{ $v->time_since_exit ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 15px; color: #64748b;">All visitors in this period have submitted their evaluation feedback. Queue is clear!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>
</html>
