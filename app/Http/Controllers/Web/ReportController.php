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
        ]);

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

        return view('reports.index', [
            'rows' => $this->organizerRows($request),
            'questionStats' => $this->questionStats($request),
            'shifts' => $shifts,
            'selectedShift' => $selectedShift,
            'shiftStats' => $shiftStats,
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'shift_id' => 'nullable|integer|exists:shifts,id',
        ]);
        $rows = $this->organizerRows($request);
        $selectedShift = $request->shift_id ? Shift::find($request->shift_id) : null;
        $shiftLabel = $selectedShift ? ($selectedShift->name . ' (' . $selectedShift->formatted_24h_range . ')') : 'All Shifts';

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
