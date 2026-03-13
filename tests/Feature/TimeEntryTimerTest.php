<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryTimerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_time_entry_page_renders_timer_start_experience_with_project_client_mapping(): void
    {
        $user = $this->createSubscribedUser();
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme Corp',
        ]);

        Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Website Revamp',
        ]);

        $response = $this->actingAs($user)->get(route('time-entries.index'));

        $response->assertOk();
        $response->assertSee('id="timer-initial-display"', false);
        $response->assertSee('id="timer-task-input"', false);
        $response->assertDontSee('id="timer-project-search"', false);
        $response->assertSee('data-client="'.$client->name.'"', false);
        $response->assertSee('aria-label="Add client"', false);
        $response->assertSee('id="add-project-modal"', false);
        $response->assertSee('id="add-client-modal"', false);
    }

    public function test_time_entries_list_shows_task_description(): void
    {
        $user = $this->createSubscribedUser();
        $project = Project::query()->create([
            'user_id' => $user->id,
            'name' => 'Website Revamp',
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Create onboarding wireframes',
            'started_at' => now()->subHour(),
            'ended_at' => now(),
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        $response = $this->actingAs($user)->get(route('time-entries.index'));

        $response->assertOk();
        $response->assertSee('Create onboarding wireframes');
    }

    public function test_continue_timer_creates_new_entry_from_existing_entry(): void
    {
        $user = $this->createSubscribedUser();
        $project = Project::query()->create([
            'user_id' => $user->id,
            'name' => 'Website Revamp',
        ]);

        $existingEntry = TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Create onboarding wireframes',
            'started_at' => now()->subHour(),
            'ended_at' => now()->subMinutes(15),
            'duration_seconds' => 2700,
            'is_billable' => false,
        ]);

        $response = $this->actingAs($user)->post(route('timer.continue', $existingEntry->id));

        $response->assertRedirect();

        $this->assertDatabaseCount('time_entries', 2);
        $this->assertDatabaseHas('time_entries', [
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Create onboarding wireframes',
            'ended_at' => null,
            'duration_seconds' => 0,
            'is_billable' => false,
        ]);
    }

    public function test_start_timer_uses_custom_initial_seconds(): void
    {
        $user = $this->createSubscribedUser();
        $project = Project::query()->create([
            'user_id' => $user->id,
            'name' => 'Website Revamp',
        ]);

        $this->actingAs($user)->post(route('timer.start'), [
            'project_id' => $project->id,
            'task' => 'Kickoff call',
            'initial_seconds' => 3661,
            'is_billable' => 0,
        ])->assertRedirect();

        $entry = TimeEntry::query()->where('user_id', $user->id)->latest('id')->first();

        $this->assertNotNull($entry);
        $this->assertSame('Kickoff call', $entry->task);
        $this->assertFalse($entry->is_billable);
        $elapsed = $entry->started_at->diffInSeconds(now());
        $this->assertGreaterThanOrEqual(3660, $elapsed);
        $this->assertLessThanOrEqual(3662, $elapsed);
    }

    public function test_start_timer_validates_custom_initial_seconds(): void
    {
        $user = $this->createSubscribedUser();
        $project = Project::query()->create([
            'user_id' => $user->id,
            'name' => 'Website Revamp',
        ]);

        $this->actingAs($user)->post(route('timer.start'), [
            'project_id' => $project->id,
            'initial_seconds' => -1,
        ])->assertSessionHasErrors('initial_seconds');

        $this->assertDatabaseCount('time_entries', 0);
    }
    public function test_start_timer_returns_json_for_ajax_requests(): void
    {
        $user = $this->createSubscribedUser();
        $project = Project::query()->create([
            'user_id' => $user->id,
            'name' => 'Website Revamp',
        ]);

        $response = $this->actingAs($user)->postJson(route('timer.start'), [
            'project_id' => $project->id,
            'initial_seconds' => 120,
        ]);

        $response->assertOk()->assertJsonStructure(['started_at']);
    }

    public function test_stop_timer_returns_json_for_ajax_requests(): void
    {
        $user = $this->createSubscribedUser();
        $project = Project::query()->create([
            'user_id' => $user->id,
            'name' => 'Website Revamp',
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Active timer',
            'started_at' => now()->subMinutes(10),
            'is_billable' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('timer.stop'));

        $response->assertOk()->assertJsonStructure(['stopped_at', 'duration_seconds']);
    }

    public function test_time_entry_can_be_edited_with_new_dates_and_flags(): void
    {
        $user = $this->createSubscribedUser();
        $project = Project::query()->create([
            'user_id' => $user->id,
            'name' => 'Website Revamp',
        ]);

        $entry = TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Initial task',
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
            'duration_seconds' => 3600,
            'is_billable' => true,
            'invoiced_at' => null,
        ]);

        $startedAt = now()->subHours(3)->startOfMinute();
        $endedAt = $startedAt->clone()->addMinutes(90);

        $this->actingAs($user)->patch(route('time-entries.update', $entry->id), [
            'task' => 'Updated task',
            'started_at' => $startedAt->toDateTimeString(),
            'ended_at' => $endedAt->toDateTimeString(),
            'is_billable' => 0,
            'invoiced' => 1,
        ])->assertRedirect();

        $entry->refresh();

        $this->assertSame('Updated task', $entry->task);
        $this->assertFalse($entry->is_billable);
        $this->assertNotNull($entry->invoiced_at);
        $this->assertSame(5400, $entry->duration_seconds);
    }

    public function test_task_suggestions_require_at_least_two_characters(): void
    {
        $user = $this->createSubscribedUser();

        $response = $this->actingAs($user)->getJson(route('timer.task-suggestions', ['q' => 'a']));

        $response->assertOk()->assertExactJson(['tasks' => []]);
    }

    public function test_task_suggestions_return_matching_entries_with_project_and_client(): void
    {
        $user = $this->createSubscribedUser();
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme Corp',
        ]);
        $project = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Website Revamp',
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Standup sync',
            'started_at' => now()->subHour(),
            'ended_at' => now()->subMinutes(10),
            'duration_seconds' => 3000,
            'is_billable' => true,
        ]);

        $response = $this->actingAs($user)->getJson(route('timer.task-suggestions', ['q' => 'stand']));

        $response->assertOk();
        $response->assertJsonPath('tasks.0.task', 'Standup sync');
        $response->assertJsonPath('tasks.0.project_id', $project->id);
        $response->assertJsonPath('tasks.0.client_name', 'Acme Corp');
        $response->assertJsonPath('tasks.0.is_billable', true);
    }

    public function test_time_entry_page_keeps_form_visible_but_locked_when_timer_is_active(): void
    {
        $user = $this->createSubscribedUser();
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme Corp',
        ]);
        $project = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Website Revamp',
        ]);

        TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'task' => 'Active task',
            'started_at' => now()->subMinutes(5),
            'is_billable' => true,
        ]);

        $response = $this->actingAs($user)->get(route('time-entries.index'));

        $response->assertOk();
        $response->assertSee('id="timer-task-input"', false);
        $response->assertSee('btn-error', false);
        $response->assertSee('>Stop<', false);
    }

    public function test_start_and_stop_timer_set_success_flash_messages_for_toasts(): void
    {
        $user = $this->createSubscribedUser();
        $project = Project::query()->create([
            'user_id' => $user->id,
            'name' => 'Website Revamp',
        ]);

        $this->actingAs($user)->post(route('timer.start'), [
            'project_id' => $project->id,
            'task' => 'Kickoff call',
        ])->assertSessionHas('success', 'Timer started.');

        $this->actingAs($user)->post(route('timer.stop'))
            ->assertSessionHas('success', 'Timer stopped.');
    }


    public function test_unsubscribed_user_cannot_start_timer_and_sees_disabled_controls(): void
    {
        $user = User::factory()->create();
        $project = Project::query()->create([
            'user_id' => $user->id,
            'name' => 'Website Revamp',
        ]);

        $response = $this->actingAs($user)->get(route('time-entries.index'));

        $response->assertOk();
        $response->assertSee('Time tracking is disabled on your account.');
        $response->assertSee('id="timer-submit-button"', false);
        $response->assertSee('disabled', false);

        $this->actingAs($user)->post(route('timer.start'), [
            'project_id' => $project->id,
        ])->assertSessionHas('error', 'Time tracking is available with an active subscription.');

        $this->assertDatabaseCount('time_entries', 0);
    }


    private function createSubscribedUser(): User
    {
        $user = User::factory()->create();

        Subscription::query()->create([
            'user_id' => $user->id,
            'plan' => User::PLAN_STARTER,
            'paddle_subscription_id' => 'sub_'.$user->id,
            'paddle_price_id' => 'price_starter',
            'status' => 'active',
            'renews_at' => now()->addMonth(),
        ]);

        return $user;
    }


}
