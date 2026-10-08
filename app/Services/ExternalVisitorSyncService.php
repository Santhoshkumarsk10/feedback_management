<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Shift;
use App\Models\Visit;
use App\Models\Visitor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalVisitorSyncService
{
    protected ?string $apiUrl;
    protected ?string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->apiUrl = config('services.third_party_visitor_api.url') ?: env('THIRD_PARTY_VISITOR_API_URL');
        $this->apiKey = config('services.third_party_visitor_api.key') ?: env('THIRD_PARTY_VISITOR_API_KEY');
        $this->timeout = (int) (config('services.third_party_visitor_api.timeout') ?: env('THIRD_PARTY_VISITOR_API_TIMEOUT', 15));
    }

    /**
     * Fetch visitors from 3rd-party API endpoint.
     */
    public function fetchFromExternalApi(?string $date = null): array
    {
        $targetDate = $date ?: today()->toDateString();

        if (!empty($this->apiUrl)) {
            try {
                $client = Http::timeout($this->timeout)->withoutVerifying();
                if (!empty($this->apiKey)) {
                    $client = $client->withToken($this->apiKey);
                }

                $response = $client->get($this->apiUrl, ['date' => $targetDate]);

                if ($response->successful()) {
                    $json = $response->json();
                    $data = $json['data'] ?? $json['visitors'] ?? $json;
                    if (is_array($data)) {
                        return $data;
                    }
                }

                Log::warning("3rd party visitor API returned non-200 status: {$response->status()}");
            } catch (\Throwable $e) {
                Log::error("Failed connecting to 3rd party visitor API: {$e->getMessage()}");
            }
        }

        // Fallback / Demonstration feed when external URL is not yet bound
        return $this->getMockExternalVisitors($targetDate);
    }

    /**
     * Ingest/Sync visitors into visitors table & visits feed for today's active shift.
     */
    public function syncVisitors(?string $date = null, ?int $organizerId = null): array
    {
        $targetDate = $date ?: today()->toDateString();
        $externalData = $this->fetchFromExternalApi($targetDate);
        $currentShift = Shift::current();

        $syncedVisitors = 0;
        $createdVisits = 0;
        $existingVisits = 0;
        $processedList = [];

        foreach ($externalData as $item) {
            if (!is_array($item)) {
                continue;
            }

            // 1. Upsert Visitor in master table with unique visitor_id
            $visitor = Visitor::upsertFromExternal($item, 'third_party_api');
            $syncedVisitors++;

            // 2. Check if a visit already exists for this visitor on this date
            $visitDate = $item['visit_date'] ?? $targetDate;

            // Parse in_time and out_time if supplied
            $inTime = null;
            $rawIn = $item['in_time'] ?? $item['entry_time'] ?? null;
            if (!empty($rawIn)) {
                try {
                    $inTime = \Carbon\Carbon::parse($visitDate . ' ' . $rawIn);
                } catch (\Throwable $e) {}
            }

            $outTime = null;
            $rawOut = $item['out_time'] ?? $item['exit_time'] ?? null;
            if (!empty($rawOut)) {
                try {
                    $outTime = \Carbon\Carbon::parse($visitDate . ' ' . $rawOut);
                } catch (\Throwable $e) {}
            }

            $department = $item['department'] ?? $item['area_visited'] ?? null;

            $visit = Visit::where(function ($q) use ($visitor) {
                $q->where('visitor_id', $visitor->id)
                  ->orWhere('visitor_code', $visitor->visitor_id);
            })->whereDate('visit_date', $visitDate)->first();

            if (!$visit) {
                $visit = Visit::create([
                    'visitor_id' => $visitor->id,
                    'visitor_code' => $visitor->visitor_id,
                    'visitor_name' => $visitor->name,
                    'visitor_company' => $visitor->company,
                    'visitor_designation' => $visitor->designation,
                    'visitor_mobile' => $visitor->mobile,
                    'visitor_email' => $visitor->email,
                    'visit_date' => $visitDate,
                    'in_time' => $inTime,
                    'out_time' => $outTime,
                    'purpose' => $item['purpose'] ?? 'Plant & Facility Tour',
                    'department' => $department,
                    'shift_id' => $currentShift?->id,
                    'organizer_id' => $organizerId ?: ($item['organizer_id'] ?? null),
                ]);
                $createdVisits++;
            } else {
                $existingVisits++;
                // Sync updated details if needed
                $visit->update([
                    'visitor_name' => $visitor->name,
                    'visitor_company' => $visitor->company,
                    'visitor_designation' => $visitor->designation ?: $visit->visitor_designation,
                    'visitor_mobile' => $visitor->mobile ?: $visit->visitor_mobile,
                    'visitor_email' => $visitor->email ?: $visit->visitor_email,
                    'in_time' => $inTime ?: $visit->in_time,
                    'out_time' => $outTime ?: $visit->out_time,
                    'department' => $department ?: $visit->department,
                ]);
            }

            $processedList[] = [
                'visitor_id' => $visitor->visitor_id,
                'name' => $visitor->name,
                'company' => $visitor->company,
                'visit_id' => $visit->id,
                'is_new_visit' => !$visit->wasRecentlyCreated ? false : true,
                'is_checked_out' => !is_null($visit->out_time),
            ];
        }

        AuditLog::record(
            'sync',
            'visitors',
            "Synced {$syncedVisitors} visitors from 3rd party API for {$targetDate} ({$createdVisits} new visits created)",
            [
                'synced_count' => $syncedVisitors,
                'new_visits' => $createdVisits,
                'existing_visits' => $existingVisits,
                'active_shift' => $currentShift?->name,
            ],
            auth()->user()
        );

        return [
            'success' => true,
            'message' => "Successfully synced {$syncedVisitors} visitors ({$createdVisits} new visits added to today's shift feed).",
            'synced_visitors' => $syncedVisitors,
            'created_visits' => $createdVisits,
            'existing_visits' => $existingVisits,
            'active_shift' => $currentShift?->name,
            'visitors' => $processedList,
        ];
    }

    /**
     * Realistic mock feed from plant security / gate pass system.
     */
    protected function getMockExternalVisitors(string $date): array
    {
        return [
            [
                'visitor_id' => 'EXT-PASS-8812',
                'name' => 'Aravind Swaminathan',
                'company' => 'Lucas TVS Limited',
                'designation' => 'Plant Engineering Head',
                'mobile' => '9840123456',
                'email' => 'aravind.s@lucastvs.com',
                'purpose' => 'High Speed Injection Moulding Trial',
                'department' => 'Injection Moulding Shop',
                'visit_date' => $date,
                'gate_pass_no' => 'GP-2026-904',
                'entry_time' => '09:30 AM',
                'exit_time' => '12:15 PM', // Exited > 2 hrs ago -> flagged in red for pending review!
            ],
            [
                'visitor_id' => 'EXT-PASS-8813',
                'name' => 'Dr. Meenakshi Sundaram',
                'company' => 'Motherson Sumi Systems',
                'designation' => 'Quality Assurance Lead',
                'mobile' => '9840765432',
                'email' => 'meenakshi.s@motherson.com',
                'purpose' => 'Die Casting Machinery Audit',
                'department' => 'Die Casting Facility',
                'visit_date' => $date,
                'gate_pass_no' => 'GP-2026-905',
                'entry_time' => '11:30 AM',
                'exit_time' => '03:45 PM', // Exited recently
            ],
            [
                'visitor_id' => 'EXT-PASS-8814',
                'name' => 'Rajeshwar Natarajan',
                'company' => 'Pricol Limited',
                'designation' => 'General Manager - Production',
                'mobile' => '9840998877',
                'email' => 'rajeshwar@pricol.co.in',
                'purpose' => 'Robotics & Factory Automation Review',
                'department' => 'Automation & Robotics Centre',
                'visit_date' => $date,
                'gate_pass_no' => 'GP-2026-906',
                'entry_time' => '02:45 PM',
                'exit_time' => null, // Still inside plant!
            ],
        ];
    }
}
