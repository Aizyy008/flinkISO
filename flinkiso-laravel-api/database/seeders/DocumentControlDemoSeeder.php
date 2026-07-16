<?php

namespace Database\Seeders;

use App\Models\Qms\ControlledCopy;
use App\Models\Qms\Document;
use App\Models\Qms\DocumentVersion;
use App\Models\Qms\ElectronicSignature;
use App\Services\Qms\AuditTrailService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sample Document Control workflow for Milestone 1.1 acceptance testing.
 * Idempotent. Creates:
 *   - DEMO SOP 001 — a fresh DRAFT for reviewers to run through the full lifecycle.
 *   - DEMO SOP 002 — a completed, RELEASED example with electronic signatures and a
 *     controlled copy, so the finished state is visible immediately.
 */
class DocumentControlDemoSeeder extends Seeder
{
    public function run(): void
    {
        $audit = app(AuditTrailService::class);
        $u = DB::connection('flinkiso')->table('users')->where('soft_delete', 0)->first();
        if (!$u) {
            $this->command->warn('No FlinkISO user found — skipping demo seed.');
            return;
        }
        $user = ['id' => $u->id, 'username' => $u->username, 'name' => $u->name];

        // 1) Fresh DRAFT for the reviewer to drive through the lifecycle.
        if (!Document::where('doc_number', 'DEMO SOP 001')->exists()) {
            $draft = Document::create([
                'doc_number' => 'DEMO SOP 001', 'title' => 'Equipment Cleaning Procedure',
                'category' => 'SOP', 'current_version' => 1, 'issue_number' => 1, 'revision_number' => 0,
                'status' => 'draft', 'owner_id' => $u->id, 'created_by' => $u->id,
            ]);
            DocumentVersion::create(['document_id' => $draft->id, 'version' => 1, 'change_summary' => 'Initial draft', 'status' => 'draft', 'created_by' => $u->id]);
            $audit->record('qms_document', $draft->id, 'create', ['user_id' => $u->id, 'username' => $u->username, 'changes' => ['new' => ['doc_number' => 'DEMO SOP 001']]]);
            $this->command->info('Seeded DEMO SOP 001 (draft — run the full lifecycle on this one).');
        }

        // 2) Completed RELEASED example with signatures + controlled copy.
        if (!Document::where('doc_number', 'DEMO SOP 002')->exists()) {
            $rel = Document::create([
                'doc_number' => 'DEMO SOP 002', 'title' => 'Incoming Goods Inspection',
                'category' => 'SOP', 'current_version' => 1, 'issue_number' => 1, 'revision_number' => 0,
                'status' => 'released', 'owner_id' => $u->id, 'created_by' => $u->id,
                'effective_date' => now()->toDateString(), 'review_due_date' => now()->addYear()->toDateString(),
            ]);
            DocumentVersion::create(['document_id' => $rel->id, 'version' => 1, 'change_summary' => 'Initial version', 'status' => 'released', 'created_by' => $u->id]);

            foreach ([['reviewed', 'reviewed', 'Reviewed for accuracy'], ['approved', 'approved', 'Approved by QA Manager'], ['released', 'authorized', 'Authorized for use']] as [$action, $meaning, $reason]) {
                $row = $audit->record('qms_document', $rel->id, 'status_change', ['user_id' => $u->id, 'username' => $u->username, 'changes' => ['status' => ['new' => $action]], 'reason' => $reason, 'signature_meaning' => $meaning]);
                ElectronicSignature::create([
                    'entity_type' => 'qms_document', 'entity_id' => $rel->id, 'document_version' => 1,
                    'action' => $action, 'meaning' => $meaning, 'reason' => $reason,
                    'signer_id' => $u->id, 'signer_name' => $u->name, 'signer_username' => $u->username,
                    'record_reference' => "qms_document:{$rel->id}:v1:{$action}", 'audit_seq' => $row->seq ?? null,
                    'signed_at' => now(),
                ]);
            }
            ControlledCopy::create(['document_id' => $rel->id, 'version' => 1, 'holder' => 'Goods-In Station', 'location' => 'Warehouse', 'issued_by' => $u->id, 'issued_at' => now()]);
            $this->command->info('Seeded DEMO SOP 002 (released, signed, 1 controlled copy).');
        }
    }
}
