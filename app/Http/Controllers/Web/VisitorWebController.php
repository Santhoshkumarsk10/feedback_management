<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Company;
use App\Models\Plant;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitorWebController extends Controller
{
    /**
     * Display the Visitor Feedback Home Screen (Kiosk, Tablet, and Web).
     */
    public function index(Request $request)
    {
        $company = Company::current();
        $banners = Banner::active()->ordered()->get();
        $plants = Plant::where('is_active', true)->orderBy('name')->get();

        // Retrieve today's visits for immediate kiosk check-in / feedback
        $todayVisits = Visit::with(['plant', 'organizer', 'feedback'])
            ->whereDate('visit_date', today())
            ->latest('id')
            ->get();

        $searchQuery = trim($request->input('q', ''));
        $searchResult = null;
        $searchError = null;

        if ($searchQuery !== '') {
            $matchedVisit = Visit::with(['plant', 'organizer', 'feedback', 'visitor'])
                ->whereDate('visit_date', today())
                ->where(function ($q) use ($searchQuery) {
                    $q->where('visitor_code', $searchQuery)
                      ->orWhere('visitor_code', 'LIKE', "%{$searchQuery}%")
                      ->orWhereHas('visitor', function ($vq) use ($searchQuery) {
                          $vq->where('mobile', $searchQuery)
                             ->orWhere('name', 'LIKE', "%{$searchQuery}%")
                             ->orWhere('company', 'LIKE', "%{$searchQuery}%");
                      })
                      ->orWhere('id', is_numeric($searchQuery) ? (int)$searchQuery : 0);
                })
                ->latest('id')
                ->first();

            if ($matchedVisit) {
                // If visit has not submitted feedback and not expired, redirect directly to feedback form
                if (!$matchedVisit->feedback && !$matchedVisit->is_feedback_expired) {
                    return redirect()->route('visits.feedback.create', $matchedVisit);
                }
                $searchResult = $matchedVisit;
            } else {
                $searchError = "No scheduled visit found for today matching \"{$searchQuery}\". Please check your visitor pass code or phone number.";
            }
        }

        return view('visitor.home', compact('company', 'banners', 'plants', 'todayVisits', 'searchQuery', 'searchResult', 'searchError'));
    }

    /**
     * Process quick search / QR barcode lookup from the Visitor Home Screen.
     */
    public function lookup(Request $request)
    {
        $query = trim($request->input('code') ?? $request->input('visitor_code') ?? $request->input('mobile') ?? '');

        if (empty($query)) {
            return redirect()->route('visitor.home')->with('error', 'Please enter your Visitor Code or registered Mobile Number.');
        }

        if (mb_strlen($query) > 50) {
            return redirect()->route('visitor.home')->with('error', 'Visitor search query cannot exceed 50 characters.');
        }

        $matchedVisit = Visit::with(['plant', 'organizer', 'feedback', 'visitor'])
            ->whereDate('visit_date', today())
            ->where(function ($q) use ($query) {
                $q->where('visitor_code', $query)
                  ->orWhere('visitor_code', 'LIKE', "%{$query}%")
                  ->orWhereHas('visitor', function ($vq) use ($query) {
                      $vq->where('mobile', $query)
                         ->orWhere('name', 'LIKE', "%{$query}%")
                         ->orWhere('company', 'LIKE', "%{$query}%");
                  })
                  ->orWhere('id', is_numeric($query) ? (int)$query : 0);
            })
            ->latest('id')
            ->first();

        if ($matchedVisit && !$matchedVisit->feedback && !$matchedVisit->is_feedback_expired) {
            return redirect()->route('visits.feedback.create', $matchedVisit);
        }

        return redirect()->route('visitor.home', ['q' => $query]);
    }
}
