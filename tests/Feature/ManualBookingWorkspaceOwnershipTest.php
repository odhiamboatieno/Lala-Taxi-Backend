<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ManualBookingDraft;
use App\Models\ManualBookingTemplate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Modules\ManualBooking\Http\Controllers\ManualBookingController;
use Tests\TestCase;

class ManualBookingWorkspaceOwnershipTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_08_16_000068_create_manual_booking_drafts_table.php'))->up();
        (require database_path('migrations/2026_08_16_000069_create_manual_booking_templates_table.php'))->up();
        $actor = new Admin;
        $actor->id = 7;
        $actor->status = 'Active';
        $this->actingAs($actor, 'admin');
    }

    private function request(array $input): Request
    {
        $request = Request::create('/admin/manual_booking/workspace', 'POST', $input);
        $this->app->instance('request', $request);
        return $request;
    }

    public function test_draft_and_template_create_then_update_only_the_authenticated_owner(): void
    {
        $controller = app(ManualBookingController::class);
        $controller->saveDraft($this->request(['name' => 'Own draft', 'payload' => ['payment_method' => 'Cash']]));
        $controller->saveTemplate($this->request(['name' => 'Own template', 'payload' => ['payment_method' => 'Cash']]));
        $draft = ManualBookingDraft::first();
        $template = ManualBookingTemplate::first();
        $this->assertSame(7, $draft->dispatcher_id);
        $this->assertSame(7, $template->dispatcher_id);
        $controller->saveDraft($this->request(['draft_id' => $draft->id, 'name' => 'Updated draft']));
        $controller->saveTemplate($this->request(['template_id' => $template->id, 'name' => 'Updated template']));
        $this->assertSame('Updated draft', $draft->fresh()->name);
        $this->assertSame('Updated template', $template->fresh()->name);
        $this->assertSame(1, ManualBookingDraft::count());
        $this->assertSame(1, ManualBookingTemplate::count());
    }

    public function test_forged_draft_or_template_id_cannot_reassign_or_overwrite_another_owner(): void
    {
        $draft = ManualBookingDraft::create(['dispatcher_type' => 'admin', 'dispatcher_id' => 8, 'name' => 'Other draft', 'status' => 'Active', 'payload' => []]);
        $template = ManualBookingTemplate::create(['company_id' => 2, 'dispatcher_type' => 'company', 'dispatcher_id' => 2, 'name' => 'Other template', 'sharing_scope' => 'company', 'status' => 'Active', 'payload' => []]);
        $controller = app(ManualBookingController::class);
        foreach ([['saveDraft', ['draft_id' => $draft->id]], ['saveTemplate', ['template_id' => $template->id]]] as [$method, $input]) {
            try { $controller->$method($this->request($input + ['name' => 'Stolen'])); $this->fail('Foreign record must fail closed.'); }
            catch (ModelNotFoundException) { $this->addToAssertionCount(1); }
        }
        $this->assertSame('Other draft', $draft->fresh()->name);
        $this->assertSame(8, $draft->fresh()->dispatcher_id);
        $this->assertSame('Other template', $template->fresh()->name);
        $this->assertSame(2, $template->fresh()->company_id);
    }

    public function test_unsupported_payment_is_rejected_before_persisting_workspace_payload(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        try { app(ManualBookingController::class)->saveDraft($this->request(['payload' => ['payment_method' => 'Card']])); }
        finally { $this->assertSame(0, ManualBookingDraft::count()); }
    }
}
