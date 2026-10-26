<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Plant;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DynamicPerPageAndCappedEntriesTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('role', 'superadmin')->first()
            ?? User::where('role', 'admin')->first()
            ?? User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->staff = User::whereIn('role', ['staff', 'organizer'])->first()
            ?? User::factory()->create(['role' => 'staff', 'is_active' => true]);
    }

    public function test_dynamic_per_page_options_on_visits_index(): void
    {
        // 1. Default per page (10)
        $res10 = $this->actingAs($this->admin)->get(route('visits.index'));
        $res10->assertOk();
        $this->assertEquals(10, $res10->viewData('visits')->perPage());

        // 2. Dynamic 25 per page
        $res25 = $this->actingAs($this->admin)->get(route('visits.index', ['per_page' => 25]));
        $res25->assertOk();
        $this->assertEquals(25, $res25->viewData('visits')->perPage());

        // 3. Dynamic 50 per page
        $res50 = $this->actingAs($this->admin)->get(route('visits.index', ['per_page' => 50]));
        $res50->assertOk();
        $this->assertEquals(50, $res50->viewData('visits')->perPage());

        // 4. Dynamic 100 per page
        $res100 = $this->actingAs($this->admin)->get(route('visits.index', ['per_page' => 100]));
        $res100->assertOk();
        $this->assertEquals(100, $res100->viewData('visits')->perPage());

        // 5. Invalid per page falls back to 10
        $resInvalid = $this->actingAs($this->admin)->get(route('visits.index', ['per_page' => 9999]));
        $resInvalid->assertOk();
        $this->assertEquals(10, $resInvalid->viewData('visits')->perPage());
    }

    public function test_per_page_options_across_other_modules(): void
    {
        // Feedbacks
        $fbRes = $this->actingAs($this->admin)->get(route('feedbacks.index', ['per_page' => 50]));
        $fbRes->assertOk();
        $this->assertEquals(50, $fbRes->viewData('feedbacks')->perPage());

        // Audit Logs
        $auditRes = $this->actingAs($this->admin)->get(route('audit-logs.index', ['per_page' => 25]));
        $auditRes->assertOk();
        $this->assertEquals(25, $auditRes->viewData('logs')->perPage());

        // Users
        $usersRes = $this->actingAs($this->admin)->get(route('users.index', ['per_page' => 100]));
        $usersRes->assertOk();
        $this->assertEquals(100, $usersRes->viewData('users')->perPage());
    }

    public function test_pagination_capped_at_recent_1000_entries(): void
    {
        // Query builder macro paginateCapped caps total at maxEntries (1000)
        $paginator = Visit::latest('id')->paginateCapped(50, 1000);
        $this->assertLessThanOrEqual(1000, $paginator->total());
        $this->assertEquals(50, $paginator->perPage());
    }

    public function test_reports_cap_at_recent_1000_entries(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.index', ['report_tab' => 'low_rating']));
        $response->assertOk();
        $lowRatings = $response->viewData('lowRatings');
        $this->assertLessThanOrEqual(1000, $lowRatings->count());

        $pendingVisits = $response->viewData('pendingVisits');
        $this->assertLessThanOrEqual(1000, $pendingVisits->count());
    }
}
