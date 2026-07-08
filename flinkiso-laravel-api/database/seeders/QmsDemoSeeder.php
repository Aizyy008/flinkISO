<?php

namespace Database\Seeders;

use App\Models\FlinkUser;
use App\Models\Qms\ControlledCopy;
use App\Models\Qms\Document;
use App\Models\Qms\DocumentVersion;
use App\Services\Qms\AuditTrailService;
use Illuminate\Database\Seeder;

/**
 * Sample document workflow for testing Milestone 1.1.
 * Creates one released document that has been through the full lifecycle,
 * with version history, a controlled copy and a complete audit trail.
 * Idempotent: skips if the demo document already exists.
 */
class QmsDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Document::where('doc_number', 'SOP 001')->exists()) {
            $this->command->info('Demo document SOP 001 already exists, skipping.');
            return;
        }

        $audit = app(AuditTrailService::class);
        $user = FlinkUser::where('soft_delete', 0)->first();
        $meta = ['user_id' => $user?->id, 'username' => $user?->username ?? 'system'];

        $doc = Document::create([
            'doc_number' => 'SOP 001',
            'title' => 'Cleaning and Sanitation Procedure',
            'category' => 'SOP',
            'current_version' => 1,
            'status' => 'draft',
            'owner_id' => $user?->id,
            'created_by' => $user?->id,
        ]);
        DocumentVersion::create([
            'document_id' => $doc->id, 'version' => 1,
            'change_summary' => 'Initial version', 'status' => 'draft', 'created_by' => $user?->id,
        ]);
        $audit->record('qms_document', $doc->id, 'create', $meta + ['changes' => ['new' => $doc->only(['doc_number', 'title'])]]);

        // Walk the lifecycle with signatures.
        foreach ([['review', 'reviewed'], ['approved', 'approved'], ['released', 'authorized']] as [$to, $meaning]) {
            $from = $doc->status;
            $doc->update(['status' => $to]);
            if ($to === 'released') {
                DocumentVersion::where('document_id', $doc->id)->where('version', 1)->update(['status' => 'released']);
            }
            $audit->record('qms_document', $doc->id, 'status_change', $meta + [
                'changes' => ['status' => ['old' => $from, 'new' => $to]],
                'reason' => ucfirst($to) . ' (demo)', 'signature_meaning' => $meaning,
            ]);
        }

        ControlledCopy::create([
            'document_id' => $doc->id, 'version' => 1,
            'holder' => 'Production Line A', 'location' => 'Ground Floor', 'issued_by' => $user?->id,
        ]);
        $audit->record('qms_document', $doc->id, 'issue_copy', $meta + ['changes' => ['controlled_copy' => 'Production Line A']]);

        $this->command->info('Seeded demo document SOP 001 (released) with full audit trail.');
    }
}
