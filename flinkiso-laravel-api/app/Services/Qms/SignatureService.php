<?php

namespace App\Services\Qms;

use App\Models\FlinkUser;
use App\Models\Qms\ElectronicSignature;

/**
 * Captures 21 CFR Part 11 electronic signatures: authenticates the signer at the
 * moment of signing, then records who signed, the meaning, the reason, the exact
 * time, and a reference to the precise action/record signed.
 */
class SignatureService
{
    /** Verify the signer's identity with their FlinkISO password (authentication at signing). */
    public function verify(string $userId, ?string $password): bool
    {
        if (!$password) {
            return false;
        }
        $user = FlinkUser::where('id', $userId)->where('soft_delete', 0)->first();
        return $user ? $user->verifyPassword($password) : false;
    }

    /**
     * Record a signature bound to a specific document action.
     *
     * @param array{id:string,username:string,name?:string} $user
     */
    public function sign(string $entityId, ?int $version, string $action, string $meaning, ?string $reason, array $user, ?int $auditSeq = null, ?string $ip = null): ElectronicSignature
    {
        return ElectronicSignature::create([
            'entity_type' => 'qms_document',
            'entity_id' => $entityId,
            'document_version' => $version,
            'action' => $action,
            'meaning' => $meaning,
            'reason' => $reason,
            'signer_id' => $user['id'],
            'signer_name' => $user['name'] ?? $user['username'],
            'signer_username' => $user['username'],
            'record_reference' => "qms_document:{$entityId}:v{$version}:{$action}",
            'audit_seq' => $auditSeq,
            'ip_address' => $ip,
            'signed_at' => now(),
        ]);
    }
}
