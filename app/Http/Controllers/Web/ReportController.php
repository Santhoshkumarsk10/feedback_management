<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\FeedbackAnswer;
use App\Models\Question;
use App\Models\User;
use App\Models\Visit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'report_tab' => 'nullable|string|in:staff,low_rating,pending',
        ]);

        $reportTab = $request->input('report_tab', 'staff');

        // Section 6.8: Low-Rating Report (Ratings <= 2 for management follow-up - capped at recent 1000)
        $lowRatingsQuery = Feedback::with(['visit', 'organizer', 'submittedBy'])
            ->where('overall_rating', '<=', 2);
        $this->dates($lowRatingsQuery, 'submitted_at', $request);
        $lowRatings = $lowRatingsQuery->latest('submitted_at')->take(1000)->get();

        // Section 6.8: Pending Feedback Report (Visitors with no feedback in date range - capped at recent 1000)
        $pendingVisitsQuery = Visit::with(['organizer'])
            ->doesntHave('feedback');
        $this->dates($pendingVisitsQuery, 'visit_date', $request);
        $pendingVisits = $pendingVisitsQuery->latest('visit_date')->latest('id')->take(1000)->get();

        return view('reports.index', [
            'rows' => $this->organizerRows($request),
            'questionStats' => $this->questionStats($request),
            'lowRatings' => $lowRatings,
            'pendingVisits' => $pendingVisits,
            'reportTab' => $reportTab,
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'type' => 'nullable|string|in:staff,low_rating,pending',
            'format' => 'nullable|string|in:csv,excel,xlsx,pdf',
        ]);

        $type = $request->input('type', 'staff');
        $format = $request->input('format', 'csv');
        if ($format === 'excel') {
            $format = 'xlsx';
        }

        $periodLabel = ($request->from && $request->to)
            ? ($request->from . ' to ' . $request->to)
            : ($request->from ? ('From ' . $request->from) : ($request->to ? ('Up to ' . $request->to) : 'All Time'));
        $currentUser = auth()->user();
        $generatedBy = $currentUser ? $currentUser->name : 'System';
        $generatedAt = now(config('app.plant_timezone', 'Asia/Kolkata'))->format('d M Y, h:i A') . ' IST';

        if ($type === 'low_rating') {
            $title = 'Low-Rating Reviews (≤ 2★) Incident Report (Recent 1,000 Max)';
            $baseFilename = 'low-rating-incidents-' . now()->format('Ymd');
            $lowRatingsQuery = Feedback::with(['visit', 'organizer', 'submittedBy'])
                ->where('overall_rating', '<=', 2);
            $this->dates($lowRatingsQuery, 'submitted_at', $request);
            $items = $lowRatingsQuery->latest('submitted_at')->take(1000)->get();
            $meta = compact('title', 'periodLabel', 'generatedBy', 'generatedAt');

            if ($format === 'pdf') {
                return $this->exportPdf($type, $items, $meta, $baseFilename . '.pdf');
            } elseif ($format === 'xlsx') {
                return $this->exportExcel($type, $items, $meta, $baseFilename . '.xlsx');
            }
            return $this->exportCsvLowRating($items, $baseFilename . '.csv');
        }

        if ($type === 'pending') {
            $title = 'Pending Feedback Queue Report (Recent 1,000 Max)';
            $baseFilename = 'pending-feedback-report-' . now()->format('Ymd');
            $pendingVisitsQuery = Visit::with(['organizer'])->doesntHave('feedback');
            $this->dates($pendingVisitsQuery, 'visit_date', $request);
            $items = $pendingVisitsQuery->latest('visit_date')->latest('id')->take(1000)->get();
            $meta = compact('title', 'periodLabel', 'generatedBy', 'generatedAt');

            if ($format === 'pdf') {
                return $this->exportPdf($type, $items, $meta, $baseFilename . '.pdf');
            } elseif ($format === 'xlsx') {
                return $this->exportExcel($type, $items, $meta, $baseFilename . '.xlsx');
            }
            return $this->exportCsvPending($items, $baseFilename . '.csv');
        }

        // Default: Staff Performance Report
        $title = 'Staff Tour Performance Report';
        $rows = $this->organizerRows($request);
        $baseFilename = 'staff-performance-report-' . now()->format('Ymd');
        $meta = compact('title', 'periodLabel', 'generatedBy', 'generatedAt');

        if ($format === 'pdf') {
            return $this->exportPdf($type, $rows, $meta, $baseFilename . '.pdf');
        } elseif ($format === 'xlsx') {
            return $this->exportExcel($type, $rows, $meta, $baseFilename . '.xlsx');
        }
        return $this->exportCsvStaff($rows, $baseFilename . '.csv');
    }

    private function exportPdf(string $type, $data, array $meta, string $filename)
    {
        $pdf = Pdf::loadView('reports.pdf', [
            'type' => $type,
            'title' => $meta['title'],
            'periodLabel' => $meta['periodLabel'],
            'totalCount' => $data->count(),
            'rows' => $type === 'staff' ? $data : null,
            'items' => $type !== 'staff' ? $data : null,
            'generatedBy' => $meta['generatedBy'],
            'generatedAt' => $meta['generatedAt'],
        ]);

        if ($type === 'low_rating' || $type === 'pending') {
            $pdf->setPaper('A4', 'landscape');
        } else {
            $pdf->setPaper('A4', 'portrait');
        }

        return $pdf->download($filename);
    }

    private function exportExcel(string $type, $data, array $meta, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($meta['title'], 0, 30));

        // Company Banner
        $sheet->setCellValue('A1', 'SHIBAURA MACHINE INDIA PRIVATE LIMITED');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('06539D'));

        // Report Title
        $sheet->setCellValue('A2', strtoupper($meta['title']));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

        // Metadata block
        $sheet->setCellValue('A3', 'Filter Period: ' . $meta['periodLabel'] . '   |   Generated: ' . $meta['generatedAt'] . ' by ' . $meta['generatedBy']);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        $startRow = 5;

        if ($type === 'staff') {
            $headers = ['#', 'Staff / Organizer', 'Department', 'Tours Handled', 'Feedbacks Received', 'Response Rate %', 'Average Rating (Stars)'];
            $colLetter = 'G';
            $sheet->fromArray($headers, null, 'A' . $startRow);

            $currentRow = $startRow + 1;
            foreach ($data as $idx => $r) {
                $respRate = $r->visits_count > 0 ? min(100, round(($r->feedbacks_count / $r->visits_count) * 100)) : 0;
                $sheet->fromArray([
                    $idx + 1,
                    $r->name,
                    $r->department ?: 'General Operations',
                    $r->visits_count,
                    $r->feedbacks_count,
                    $respRate . '%',
                    $r->avg_rating ? round((float) $r->avg_rating, 2) : '—',
                ], null, 'A' . $currentRow);
                $currentRow++;
            }
        } elseif ($type === 'low_rating') {
            $headers = ['#', 'Submission Date', 'Rating (Stars)', 'Visitor Name', 'Company', 'Host Staff', 'Mode', 'Submitted By', 'Visitor Comments / Remarks'];
            $colLetter = 'I';
            $sheet->fromArray($headers, null, 'A' . $startRow);

            $currentRow = $startRow + 1;
            foreach ($data as $idx => $f) {
                $sheet->fromArray([
                    $idx + 1,
                    $f->submitted_at ? $f->submitted_at->format('Y-m-d H:i:s') : '—',
                    $f->overall_rating . ' Stars',
                    $f->visit?->visitor_name ?? '—',
                    $f->visit?->visitor_company ?: '—',
                    $f->organizer?->name ?? 'Unassigned',
                    $f->is_staff_assisted ? 'Staff-assisted' : 'Direct Visitor',
                    $f->submittedBy?->name ?? '—',
                    $f->comments ?: 'No comments provided',
                ], null, 'A' . $currentRow);
                $currentRow++;
            }
        } else { // pending
            $headers = ['#', 'Visit Date', 'Visitor ID', 'Visitor Name', 'Company', 'Mobile Number', 'Host Staff', 'Checkout Status', 'Time Since Exit'];
            $colLetter = 'I';
            $sheet->fromArray($headers, null, 'A' . $startRow);

            $currentRow = $startRow + 1;
            foreach ($data as $idx => $v) {
                $sheet->fromArray([
                    $idx + 1,
                    $v->visit_date ? $v->visit_date->format('Y-m-d') : '—',
                    $v->visitor_code ?: 'VIS-' . $v->id,
                    $v->visitor_name,
                    $v->visitor_company ?: '—',
                    $v->visitor_mobile ?: '—',
                    $v->organizer?->name ?? 'Unassigned',
                    $v->out_time ? 'Checked Out' : 'Inside Plant',
                    $v->time_since_exit ?? '—',
                ], null, 'A' . $currentRow);
                $currentRow++;
            }
        }

        $lastRow = max($startRow + 1, $currentRow - 1);

        // Header row styling
        $headerRange = 'A' . $startRow . ':' . $colLetter . $startRow;
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '06539D']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($startRow)->setRowHeight(25);

        // Borders
        $sheet->getStyle('A' . $startRow . ':' . $colLetter . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ]);

        // Alternating zebra rows
        for ($r = $startRow + 1; $r <= $lastRow; $r++) {
            if ($r % 2 === 0) {
                $sheet->getStyle('A' . $r . ':' . $colLetter . $r)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                ]);
            }
        }

        // Auto-fit columns
        foreach (range('A', $colLetter) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function exportCsvLowRating($items, string $filename)
    {
        return response()->streamDownload(function () use ($items) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Submission Date', 'Rating (Stars)', 'Visitor Name', 'Company', 'Host Staff', 'Submitted Mode', 'Submitted By', 'Visitor Comments']);
            foreach ($items as $f) {
                fputcsv($out, [
                    $f->submitted_at ? $f->submitted_at->format('Y-m-d H:i:s') : '—',
                    $f->overall_rating . ' Stars',
                    $f->visit?->visitor_name ?? '—',
                    $f->visit?->visitor_company ?? '—',
                    $f->organizer?->name ?? 'Unassigned',
                    $f->is_staff_assisted ? 'Staff-assisted' : 'Direct Visitor',
                    $f->submittedBy?->name ?? '—',
                    $f->comments ?: 'No comments provided',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function exportCsvPending($items, string $filename)
    {
        return response()->streamDownload(function () use ($items) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Visit Date', 'Visitor ID', 'Visitor Name', 'Company', 'Mobile', 'Host Staff', 'Checkout Status', 'Time Since Exit']);
            foreach ($items as $v) {
                fputcsv($out, [
                    $v->visit_date ? $v->visit_date->format('Y-m-d') : '—',
                    $v->visitor_code ?: 'VIS-' . $v->id,
                    $v->visitor_name,
                    $v->visitor_company ?: '—',
                    $v->visitor_mobile ?: '—',
                    $v->organizer?->name ?? 'Unassigned',
                    $v->out_time ? 'Checked Out' : 'Inside Plant',
                    $v->time_since_exit ?? '—',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function exportCsvStaff($rows, string $filename)
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Staff / Organizer', 'Department', 'Visits Handled', 'Feedbacks Received', 'Response %', 'Avg Rating']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->name,
                    $r->department ?: 'General',
                    $r->visits_count,
                    $r->feedbacks_count,
                    $r->visits_count ? min(100, round($r->feedbacks_count / $r->visits_count * 100)) : 0,
                    round((float) $r->avg_rating, 2),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function organizerRows(Request $r)
    {
        return User::where('is_active', true)
            ->withCount([
                'visitsAsOrganizer as visits_count' => fn ($q) => $this->filterVisits($q, $r),
                'feedbacksReceived as feedbacks_count' => fn ($q) => $this->filterFeedbacks($q, $r),
            ])
            ->withAvg(['feedbacksReceived as avg_rating' => fn ($q) => $this->filterFeedbacks($q, $r)], 'overall_rating')
            ->orderBy('name')
            ->get();
    }

    private function questionStats(Request $r)
    {
        return Question::where('type', 'rating')->orderBy('sort_order')->get()->map(function ($q) use ($r) {
            $base = FeedbackAnswer::where('question_id', $q->id)
                ->whereHas('feedback', function ($f) use ($r) {
                    $this->dates($f, 'submitted_at', $r);
                });

            return [
                'section' => $q->section ?: 'General Questionnaire',
                'question' => $q->question,
                'count' => (clone $base)->count(),
                'avg' => round((float) (clone $base)->avg(DB::raw('CAST(answer AS DECIMAL(10,2))')), 2),
            ];
        });
    }

    private function filterVisits($q, Request $r)
    {
        return $this->dates($q, 'visit_date', $r);
    }

    private function filterFeedbacks($q, Request $r)
    {
        return $this->dates($q, 'submitted_at', $r);
    }

    private function dates($q, string $column, Request $r)
    {
        return $q->when($r->from, fn ($x, $v) => $x->whereDate($column, '>=', $v))
                 ->when($r->to, fn ($x, $v) => $x->whereDate($column, '<=', $v));
    }
}
