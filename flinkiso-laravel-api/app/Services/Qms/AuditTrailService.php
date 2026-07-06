<?php

namespace App\Services\Qms;

use App\Models\Qms\AuditTrail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Immutable, hash-chained audit trail (FDA 21 CFR Part 11).
 *
 * Each record's hash = sha256(prev_hash + canonical(record)). Altering or deleting
 * any record breaks every subsequent hash, so tampering is detectable via verifyChain().
 */
class AuditTrailService
{
    /**
     * Append one audit record. Returns the created row.
     *
     * @param array{user_id?:?string,username?:?string,changes?:?array,reason?:?string,signature_meaning?:?string} $meta
     */
    public function record(string $entityType, ?string $entityId, string $action, array $meta = []): AuditTrail
    {
        // Serialize appends so the chain can't fork under concurrency.
        return DB::transaction(function () use ($entityType, $entityId, $action, $meta) {
            $prev = AuditTrail::orderByDesc('seq')->lockForUpdate()->first();
            $prevHash = $prev->hash ?? str_repeat('0', 64); // genesis

            $row = [
                'id' => (string) Str::uuid(),
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'action' => $action,
                'user_id' => $meta['user_id'] ?? null,
                'username' => $meta['username'] ?? null,
                'changes' => isset($meta['changes']) ? json_encode($meta['changes']) : null,
                'reason' => $meta['reason'] ?? null,
                'signature_meaning' => $meta['signature_meaning'] ?? null,
                'prev_hash' => $prevHash,
                'created_at' => now()->toDateTimeString(),
            ];

            $row['hash'] = $this->computeHash($row, $prevHash);

            $model = new AuditTrail();
            $model->setRawAttributes($row);
            $model->save();

            return $model;
        });
    }

    /** Canonical hash of a record given the previous hash. */
    private function computeHash(array $row, string $prevHash): string
    {
        $canonical = implode('|', [
            $prevHash,
            $row['entity_type'],
            $row['entity_id'] ?? '',
            $row['action'],
            $row['user_id'] ?? '',
            $row['changes'] ?? '',
            $row['reason'] ?? '',
            $row['signature_meaning'] ?? '',
            $row['created_at'],
        ]);

        return hash('sha256', $canonical);
    }

    /**
     * Verify the whole chain. Returns ['valid'=>bool, 'checked'=>int, 'broken_at'=>?seq].
     */
    public function verifyChain(): array
    {
        $prevHash = str_repeat('0', 64);
        $checked = 0;

        foreach (AuditTrail::orderBy('seq')->cursor() as $r) {
            $expected = $this->computeHash([
                'entity_type' => $r->entity_type,
                'entity_id' => $r->entity_id,
                'action' => $r->action,
                'user_id' => $r->user_id,
                'changes' => $r->getRawOriginal('changes'),
                'reason' => $r->reason,
                'signature_meaning' => $r->signature_meaning,
                'created_at' => $r->created_at->toDateTimeString(),
            ], $prevHash);

            if ($r->prev_hash !== $prevHash || $r->hash !== $expected) {
                return ['valid' => false, 'checked' => $checked, 'broken_at' => $r->seq];
            }

            $prevHash = $r->hash;
            $checked++;
        }

        return ['valid' => true, 'checked' => $checked, 'broken_at' => null];
    }
}
