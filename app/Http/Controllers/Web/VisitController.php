<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'organizer_id' => 'nullable|integer|exists:users,id',
            'q' => ['nullable', 'string', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
        ]);

        $tab = $request->input('tab', 'all');

        $query = Visit::with(['organizer', 'feedback'])
            ->when($request->organizer_id, fn ($q, $v) => $q->where('organizer_id', $v))
            ->when($request->from, fn ($q, $v) => $q->whereDate('visit_date', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('visit_date', '<=', $v))
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('visitor_name', 'like', "%$v%")
                ->orWhere('visitor_mobile', 'like', "%$v%")
                ->orWhere('visitor_company', 'like', "%$v%")))
            ->when($tab === 'completed', fn ($q) => $q->has('feedback'))
            ->when($tab === 'pending', fn ($q) => $q->doesntHave('feedback'));

        $totalCount = Visit::count();
        $completedCount = Visit::has('feedback')->count();
        $pendingCount = Visit::doesntHave('feedback')->count();

        $visits = $query->latest('visit_date')->latest('id')->paginate(10)->withQueryString();

        $visitSuggestions = Visit::select('id', 'visitor_name', 'visitor_company', 'visitor_mobile')
            ->latest('id')
            ->take(30)
            ->get()
            ->map(function ($v) {
                return [
                    'code' => $v->visitor_company ? substr($v->visitor_company, 0, 8) : null,
                    'name' => $v->visitor_name,
                    'sub' => ($v->visitor_company ? $v->visitor_company . ' • ' : '') . $v->visitor_mobile,
                    'value' => $v->visitor_name,
                ];
            });

        return view('visits.index', [
            'visits' => $visits,
            'organizers' => User::where('role', 'organizer')->orderBy('name')->get(['id', 'name']),
            'tabCounts' => [
                'all' => $totalCount,
                'completed' => $completedCount,
                'pending' => $pendingCount,
            ],
            'currentTab' => $tab,
            'visitSuggestions' => $visitSuggestions,
        ]);
    }
}
