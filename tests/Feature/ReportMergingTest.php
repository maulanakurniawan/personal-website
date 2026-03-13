<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportMergingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_reports_can_merge_entries_by_task(): void
    {
        $user = User::factory()->create();

        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme',
        ]);

        $project = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Website',
        ]);

        $firstStart = Carbon::parse('2026-03-01 08:00:00');
        $firstEnd = Carbon::parse('2026-03-01 09:00:00');
        $secondStart = Carbon::parse('2026-03-02 10:00:00');
        $secondEnd = Carbon::parse('2026-03-02 12:00:00');

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Build API',
            'started_at' => $firstStart,
            'ended_at' => $firstEnd,
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Build API',
            'started_at' => $secondStart,
            'ended_at' => $secondEnd,
            'duration_seconds' => 7200,
            'is_billable' => true,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index', ['merge_by_task' => 1]));

        $response->assertOk();

        $entries = collect($response->viewData('entries')->items());
        $merged = $entries->first(fn ($entry) => $entry->task === 'Build API');

        $this->assertNotNull($merged);
        $this->assertSame(10800, (int) $merged->duration_seconds);
        $this->assertSame($firstStart->timestamp, Carbon::parse($merged->started_at)->timestamp);
        $this->assertSame($secondEnd->timestamp, Carbon::parse($merged->ended_at)->timestamp);
    }


    public function test_report_list_view_shows_client_and_project_names(): void
    {
        $user = User::factory()->create();

        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme',
        ]);

        $project = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Website',
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Build API',
            'started_at' => Carbon::parse('2026-03-01 08:00:00'),
            'ended_at' => Carbon::parse('2026-03-01 09:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        $regularResponse = $this->actingAs($user)->get(route('reports.index'));
        $regularResponse->assertOk();
        $regularResponse->assertSee('Acme', false);
        $regularResponse->assertSee('Website', false);

        $mergedResponse = $this->actingAs($user)->get(route('reports.index', ['merge_by_task' => 1]));
        $mergedResponse->assertOk();
        $mergedResponse->assertSee('Acme', false);
        $mergedResponse->assertSee('Website', false);
    }

    public function test_export_can_merge_entries_by_task(): void
    {
        $user = User::factory()->create();

        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme',
        ]);

        $project = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Website',
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Build API',
            'started_at' => Carbon::parse('2026-03-01 08:00:00'),
            'ended_at' => Carbon::parse('2026-03-01 09:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => ' Build API ',
            'started_at' => Carbon::parse('2026-03-02 10:00:00'),
            'ended_at' => Carbon::parse('2026-03-02 12:00:00'),
            'duration_seconds' => 7200,
            'is_billable' => false,
        ]);

        $response = $this->actingAs($user)->get(route('reports.export', [
            'merge_by_task' => 1,
        ]));

        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertSame(1, substr_count($csv, 'Build API'));
        $this->assertStringContainsString('Acme,Website,"Build API",3', $csv);
    }

    public function test_export_merge_by_task_splits_same_task_for_different_client_or_project(): void
    {
        $user = User::factory()->create();

        $clientA = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme',
        ]);

        $clientB = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Globex',
        ]);

        $projectA = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $clientA->id,
            'name' => 'Website',
        ]);

        $projectB = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $clientB->id,
            'name' => 'Mobile App',
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $projectA->id,
            'task' => 'Design Review',
            'started_at' => Carbon::parse('2026-03-01 08:00:00'),
            'ended_at' => Carbon::parse('2026-03-01 09:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $projectB->id,
            'task' => 'Design Review',
            'started_at' => Carbon::parse('2026-03-01 10:00:00'),
            'ended_at' => Carbon::parse('2026-03-01 12:00:00'),
            'duration_seconds' => 7200,
            'is_billable' => true,
        ]);

        $response = $this->actingAs($user)->get(route('reports.export', [
            'merge_by_task' => 1,
        ]));

        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertSame(2, substr_count($csv, 'Design Review'));
        $this->assertStringContainsString('Acme,Website,"Design Review",1', $csv);
        $this->assertStringContainsString('Globex,"Mobile App","Design Review",2', $csv);
    }


    public function test_export_always_includes_client_and_project_values(): void
    {
        $user = User::factory()->create();

        $project = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => null,
            'name' => 'Internal',
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Admin',
            'started_at' => Carbon::parse('2026-03-05 09:00:00'),
            'ended_at' => Carbon::parse('2026-03-05 10:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => false,
        ]);

        $mergedCsv = $this->actingAs($user)
            ->get(route('reports.export', ['merge_by_task' => 1]))
            ->streamedContent();

        $regularCsv = $this->actingAs($user)
            ->get(route('reports.export'))
            ->streamedContent();

        $this->assertStringContainsString(',-,Internal,Admin,1,No,Non-billable', $mergedCsv);
        $this->assertStringContainsString(',-,Internal,Admin,1,No,Non-billable', $regularCsv);
    }

    public function test_export_with_billable_and_invoiced_filters_only_contains_matching_rows(): void
    {
        $user = User::factory()->create();

        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme',
        ]);

        $project = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Website',
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Invoiced billable work',
            'started_at' => Carbon::parse('2026-03-03 09:00:00'),
            'ended_at' => Carbon::parse('2026-03-03 10:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => true,
            'invoiced_at' => Carbon::parse('2026-03-04 12:00:00'),
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Uninvoiced billable work',
            'started_at' => Carbon::parse('2026-03-03 11:00:00'),
            'ended_at' => Carbon::parse('2026-03-03 12:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Non-billable work',
            'started_at' => Carbon::parse('2026-03-03 13:00:00'),
            'ended_at' => Carbon::parse('2026-03-03 14:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => false,
        ]);

        $response = $this->actingAs($user)->get(route('reports.export', [
            'billable' => 1,
            'invoiced' => 1,
        ]));

        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Invoiced billable work', $csv);
        $this->assertStringNotContainsString('Uninvoiced billable work', $csv);
        $this->assertStringNotContainsString('Non-billable work', $csv);
    }

    public function test_export_without_billable_or_invoiced_filters_contains_all_rows(): void
    {
        $user = User::factory()->create();

        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme',
        ]);

        $project = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Website',
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Invoiced billable work',
            'started_at' => Carbon::parse('2026-03-03 09:00:00'),
            'ended_at' => Carbon::parse('2026-03-03 10:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => true,
            'invoiced_at' => Carbon::parse('2026-03-04 12:00:00'),
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Uninvoiced billable work',
            'started_at' => Carbon::parse('2026-03-03 11:00:00'),
            'ended_at' => Carbon::parse('2026-03-03 12:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Non-billable work',
            'started_at' => Carbon::parse('2026-03-03 13:00:00'),
            'ended_at' => Carbon::parse('2026-03-03 14:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => false,
        ]);

        $response = $this->actingAs($user)->get(route('reports.export'));

        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Invoiced billable work', $csv);
        $this->assertStringContainsString('Uninvoiced billable work', $csv);
        $this->assertStringContainsString('Non-billable work', $csv);
    }

    public function test_export_can_filter_uninvoiced_entries(): void
    {
        $user = User::factory()->create();

        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme',
        ]);

        $project = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Website',
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Invoiced task',
            'started_at' => Carbon::parse('2026-03-03 09:00:00'),
            'ended_at' => Carbon::parse('2026-03-03 10:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => true,
            'invoiced_at' => Carbon::parse('2026-03-04 12:00:00'),
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Uninvoiced task',
            'started_at' => Carbon::parse('2026-03-03 11:00:00'),
            'ended_at' => Carbon::parse('2026-03-03 12:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        $response = $this->actingAs($user)->get(route('reports.export', [
            'invoiced' => '0',
        ]));

        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Uninvoiced task', $csv);
        $this->assertStringNotContainsString('Invoiced task', $csv);
    }

    public function test_export_can_filter_unbillable_entries(): void
    {
        $user = User::factory()->create();

        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme',
        ]);

        $project = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Website',
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Billable task',
            'started_at' => Carbon::parse('2026-03-03 09:00:00'),
            'ended_at' => Carbon::parse('2026-03-03 10:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Unbillable task',
            'started_at' => Carbon::parse('2026-03-03 11:00:00'),
            'ended_at' => Carbon::parse('2026-03-03 12:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => false,
        ]);

        $response = $this->actingAs($user)->get(route('reports.export', [
            'billable' => '0',
        ]));

        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Unbillable task', $csv);
        $this->assertStringNotContainsString('Billable task', $csv);
    }

    public function test_preset_overrides_existing_date_range_inputs(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-18 12:00:00'));

        $user = User::factory()->create();

        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme',
        ]);

        $project = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Website',
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Current week work',
            'started_at' => Carbon::parse('2026-03-16 09:00:00'),
            'ended_at' => Carbon::parse('2026-03-16 10:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Old work',
            'started_at' => Carbon::parse('2026-02-03 09:00:00'),
            'ended_at' => Carbon::parse('2026-02-03 10:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index', [
            'from' => '2026-02-01',
            'to' => '2026-02-28',
            'preset' => 'this-week',
        ]));

        $response->assertOk();
        $response->assertSee('2026-03-16', false);
        $response->assertDontSee('2026-02-03', false);

        Carbon::setTestNow();
    }

    public function test_export_uses_active_preset_filter(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-18 12:00:00'));

        $user = User::factory()->create();

        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme',
        ]);

        $project = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Website',
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Current week work',
            'started_at' => Carbon::parse('2026-03-16 09:00:00'),
            'ended_at' => Carbon::parse('2026-03-16 10:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Old work',
            'started_at' => Carbon::parse('2026-02-03 09:00:00'),
            'ended_at' => Carbon::parse('2026-02-03 10:00:00'),
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        $response = $this->actingAs($user)->get(route('reports.export', [
            'preset' => 'this-week',
        ]));

        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Current week work', $csv);
        $this->assertStringNotContainsString('Old work', $csv);

        Carbon::setTestNow();
    }
}
