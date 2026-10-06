<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FeedbackAnswer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);

        return view('reports.index', [
            'rows' => $this->organizerRows($request),
            'questionStats' => $this->questionStats($request),
        ]);
    }

    public function export(Request $request)
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);
        $rows = $this->organizerRows($request);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Organizer', 'Department', 'Visits', 'Feedbacks', 'Response %', 'Avg Rating']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->name, $r->department, $r->visits_count, $r->feedbacks_count,
                    $r->visits_count ? min(100, round($r->feedbacks_count / $r->visits_count * 100)) : 0,
                    round((float) $r->avg_rating, 2),
                ]);
            }
            fclose($out);
        }, 'organizer-report-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function organizerRows(Request $r)
    {
        return User::where('role', 'organizer')
            ->withCount([
                'visitsAsOrganizer as visits_count' => fn ($q) => $this->dates($q, 'visit_date', $r),
                'feedbacksReceived as feedbacks_count' => fn ($q) => $this->dates($q, 'submitted_at', $r),
            ])
            ->withAvg(['feedbacksReceived as avg_rating' => fn ($q) => $this->dates($q, 'submitted_at', $r)], 'overall_rating')
            ->orderBy('name')->get();
    }

    private function questionStats(Request $r)
    {
        return Question::where('type', 'rating')->orderBy('sort_order')->get()->map(function ($q) use ($r) {
            $base = FeedbackAnswer::where('question_id', $q->id)
                ->whereHas('feedback', fn ($f) => $this->dates($f, 'submitted_at', $r));

            return [
                'section' => $q->section ?: 'General Questionnaire',
                'question' => $q->question,
                'count' => (clone $base)->count(),
                'avg' => round((float) (clone $base)->avg(DB::raw('CAST(answer AS DECIMAL(10,2))')), 2),
            ];
        });
    }

    private function dates($q, string $column, Request $r)
    {
        return $q->when($r->from, fn ($x, $v) => $x->whereDate($column, '>=', $v))
                 ->when($r->to, fn ($x, $v) => $x->whereDate($column, '<=', $v));
    }
}
