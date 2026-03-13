<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_clients_page_supports_editing_and_shows_delete_warning(): void
    {
        $user = User::factory()->create();
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme',
            'notes' => 'Initial note',
        ]);

        $response = $this->actingAs($user)->get(route('clients.index'));

        $response->assertOk();
        $response->assertSee(route('clients.update', $client->id), false);
        $response->assertSee('All related projects and timer entries will be deleted permanently.');

        $this->actingAs($user)->patch(route('clients.update', $client->id), [
            'name' => 'Acme Updated',
            'notes' => 'Updated note',
        ])->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Acme Updated',
            'notes' => 'Updated note',
        ]);
    }

    public function test_deleting_client_removes_related_projects_and_time_entries(): void
    {
        $user = User::factory()->create();
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme',
        ]);
        $project = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Website Revamp',
        ]);
        $entry = TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'started_at' => now()->subHour(),
            'ended_at' => now(),
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        $this->actingAs($user)->delete(route('clients.destroy', $client->id))->assertRedirect();

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
    }

    public function test_projects_page_supports_edit_and_delete_with_cascade_to_time_entries(): void
    {
        $user = User::factory()->create();
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme',
        ]);
        $project = Project::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Website Revamp',
            'notes' => 'Initial notes',
        ]);
        $entry = TimeEntry::query()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'started_at' => now()->subHour(),
            'ended_at' => now(),
            'duration_seconds' => 3600,
            'is_billable' => true,
        ]);

        $response = $this->actingAs($user)->get(route('projects.index'));

        $response->assertOk();
        $response->assertSee(route('projects.update', $project->id), false);
        $response->assertSee(route('projects.destroy', $project->id), false);
        $response->assertSee('All related timer entries will be deleted permanently.');

        $this->actingAs($user)->patch(route('projects.update', $project->id), [
            'name' => 'Website Revamp Updated',
            'client_id' => '',
            'notes' => 'Updated notes',
        ])->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Website Revamp Updated',
            'client_id' => null,
            'notes' => 'Updated notes',
        ]);

        $this->actingAs($user)->delete(route('projects.destroy', $project->id))->assertRedirect();

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
    }

    public function test_starter_user_cannot_create_more_than_five_projects(): void
    {
        $user = User::factory()->create([
            'plan' => User::PLAN_STARTER,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            Project::query()->create([
                'user_id' => $user->id,
                'name' => "Project {$i}",
            ]);
        }

        $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'Project 6',
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('projects', 5);
    }


}
