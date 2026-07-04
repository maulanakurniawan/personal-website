<?php

namespace Tests\Feature\ValidationApi;

use App\Models\AdminUser;
use App\Models\ValidationLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationLeadAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['admin-hub.enabled' => true]);
    }

    public function test_validation_leads_are_visible_in_admin_hub_resources(): void
    {
        ValidationLead::create([
            'product_key' => 'keepbydate',
            'product_name' => 'KeepByDate',
            'email' => 'user@example.com',
            'target_category' => 'food',
            'price_interest' => 'maybe',
            'status' => 'new',
            'submission_count' => 1,
        ]);

        $this->actingAsAdmin()
            ->get('/admin/maulanakurniawan/resources/validation_leads')
            ->assertOk()
            ->assertSee('Validation Leads')
            ->assertSee('user@example.com')
            ->assertSee('keepbydate');
    }

    public function test_validation_lead_review_fields_can_be_edited_from_admin_hub(): void
    {
        $lead = ValidationLead::create([
            'product_key' => 'keepbydate',
            'product_name' => 'KeepByDate',
            'email' => 'user@example.com',
            'status' => 'new',
            'submission_count' => 1,
        ]);

        $this->actingAsAdmin()
            ->patch('/admin/maulanakurniawan/resources/validation_leads/'.$lead->id, [
                'status' => 'reviewed',
                'notes' => 'Looks promising.',
                'target_category' => 'freezer',
                'price_interest' => 'yes',
            ])
            ->assertRedirect('/admin/maulanakurniawan/resources/validation_leads/'.$lead->id);

        $this->assertDatabaseHas('validation_leads', [
            'id' => $lead->id,
            'status' => 'reviewed',
            'notes' => 'Looks promising.',
            'target_category' => 'freezer',
            'price_interest' => 'yes',
        ]);
    }

    private function actingAsAdmin(): self
    {
        $admin = AdminUser::create(['name' => 'Admin', 'email' => uniqid().'@example.com', 'password' => 'secret-password']);

        return $this->actingAs($admin, 'admin');
    }
}
