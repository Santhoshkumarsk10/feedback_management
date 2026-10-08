<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\FeedbackAnswer;
use App\Models\Question;
use App\Models\Shift;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'shift_id' => 'nullable|integer|exists:shifts,id',
            'report_tab' => 'nullable|string|in:staff,low_rating,pending',
        ]);

        $reportTab = $request->input('report_tab', 'staff');
        $shifts = Shift::active()->orderBy('start_time')->get();
        $selectedShift = $request->shift_id ? $shifts->firstWhere('id', (int) $request->shift_id) : null;

        // Shift performance comparison metrics across all active shifts
        $shiftStats = $shifts->map(function ($s) use ($request) {
            $visitsQuery = Visit::where('shift_id', $s->id);
            $this->dates($visitsQuery, 'visit_date', $request);
            $visitsCount = $visitsQuery->count();

            $feedbacksQuery = Feedback::whereHas('visit', function ($v) use ($s, $request) {
                $v->where('shift_id', $s->id);
                $this->dates($v, 'visit_date', $request);
            });
            $feedbacksCount = $feedbacksQuery->count();
            $avgRating = (clone $feedbacksQuery)->avg('overall_rating');

            return [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code,
                'time_range' => $s->formatted_24h_range,
                'time_12h' => $s->formatted_12h_range,
                'visits_count' => $visitsCount,
                'feedbacks_count' => $feedbacksCount,
                'response_rate' => $visitsCount > 0 ? min(100, round(($feedbacksCount / $visitsCount) * 100)) : 0,
                'avg_rating' => $avgRating ? round((float) $avgRating, 2) : 0,
                'is_active_now' => $s->is_currently_active,
            ];
        });

        // Section 6.8: Low-Rating Report (Ratings <= 2 for management follow-up)
        $lowRatingsQuery = Feedback::with(['visit.shift', 'organizer', 'submittedBy'])
            ->where('overall_rating', '<=', 2);
        $this->dates($lowRatingsQuery, 'submitted_at', $request);
        if ($request->shift_id) {
            $lowRatingsQuery->whereHas('visit', fn ($v) => $v->where('shift_id', $request->shift_id));
        }
        $lowRatings = $lowRatingsQuery->latest('submitted_at')->get();

        // Section 6.8: Pending Feedback Report (Visitors with no feedback in date range)
        $pendingVisitsQuery = Visit::with(['shift', 'organizer'])
            ->doesntHave('feedback');
        $this->dates($pendingVisitsQuery, 'visit_date', $request);
        if ($request->shift_id) {
            $pendingVisitsQuery->where('shift_id', $request->shift_id);
        }
        $pendingVisits = $pendingVisitsQuery->latest('visit_date')->latest('id')->get();

        return view('reports.index', [
            'rows' => $this->organizerRows($request),
            'questionStats' => $this->questionStats($request),
            'shifts' => $shifts,
            'selectedShift' => $selectedShift,
            'shiftStats' => $shiftStats,
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
            'shift_id' => 'nullable|integer|exists:shifts,id',
            'type' => 'nullable|string|in:staff,low_rating,pending',
        ]);

        $type = $request->input('type', 'staff');
        $selectedShift = $request->shift_id ? Shift::find($request->shift_id) : null;
        $shiftLabel = $selectedShift ? ($selectedShift->name . ' (' . $selectedShift->formatted_24h_range . ')') : 'All Shifts';

        if ($type === 'low_rating') {
            $lowRatingsQuery = Feedback::with(['visit.shift', 'organizer', 'submittedBy'])
                ->where('overall_rating', '<=', 2);
            $this->dates($lowRatingsQuery, 'submitted_at', $request);
            if ($request->shift_id) {
                $lowRatingsQuery->whereHas('visit', fn ($v) => $v->where('shift_id', $request->shift_id));
            }
            $items = $lowRatingsQuery->latest('submitted_at')->get();

            $filename = 'low-rating-incidents-' . now()->format('Ymd') . '.csv';

            return response()->streamDownload(function () use ($items, $shiftLabel) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Submission Date', 'Rating (Stars)', 'Visitor Name', 'Company', 'Host Staff', 'Submitted Mode', 'Submitted By', 'Visitor Comments']);
                foreach ($items as $f) {
                    fputcsv($out, [
                        $f->submitted_at->format('Y-m-d H:i:s'),
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

        if ($type === 'pending') {
            $pendingVisitsQuery = Visit::with(['shift', 'organizer'])->doesntHave('feedback');
            $this->dates($pendingVisitsQuery, 'visit_date', $request);
            if ($request->shift_id) {
                $pendingVisitsQuery->where('shift_id', $request->shift_id);
            }
            $items = $pendingVisitsQuery->latest('visit_date')->latest('id')->get();

            $filename = 'pending-feedback-report-' . now()->format('Ymd') . '.csv';

            return response()->streamDownload(function () use ($items) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Visit Date', 'Visitor ID', 'Visitor Name', 'Company', 'Mobile', 'Duty Shift', 'Host Staff', 'Checkout Status', 'Time Since Exit']);
                foreach ($items as $v) {
                    fputcsv($out, [
                        $v->visit_date->format('Y-m-d'),
                        $v->visitor_code ?: 'VIS-' . $v->id,
                        $v->visitor_name,
                        $v->visitor_company ?: '—',
                        $v->visitor_mobile ?: '—',
                        $v->shift?->name ?: '—',
                        $v->organizer?->name ?: 'Unassigned',
                        $v->out_time ? 'Checked Out' : 'Inside Plant',
                        $v->time_since_exit ?? '—',
                    ]);
                }
                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        // Default: Staff Performance Report
        $rows = $this->organizerRows($request);
        $filename = ($selectedShift ? Str::slug($selectedShift->name) . '-' : '') . 'performance-report-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($rows, $shiftLabel) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Staff / Organizer', 'Department', 'Shift Filter', 'Visits Handled', 'Feedbacks Received', 'Response %', 'Avg Rating']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->name,
                    $r->department ?: 'General',
                    $shiftLabel,
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
        return User::whereIn('role', ['staff', 'organizer'])
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
                    if ($r->shift_id) {
                        $f->whereHas('visit', fn ($v) => $v->where('shift_id', $r->shift_id));
                    }
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
        $this->dates($q, 'visit_date', $r);
        return $q->when($r->shift_id, fn ($x, $v) => $x->where('shift_id', $v));
    }

    private function filterFeedbacks($q, Request $r)
    {
        $this->dates($q, 'submitted_at', $r);
        return $q->when($r->shift_id, fn ($x, $v) => $x->whereHas('visit', fn ($w) => $w->where('shift_id', $v)));
    }

    private function dates($q, string $column, Request $r)
    {
        return $q->when($r->from, fn ($x, $v) => $x->whereDate($column, '>=', $v))
                 ->when($r->to, fn ($x, $v) => $x->whereDate($column, '<=', $v));
    }
}
