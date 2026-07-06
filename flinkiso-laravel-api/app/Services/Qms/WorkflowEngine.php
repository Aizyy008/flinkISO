<?php

namespace App\Services\Qms;

use App\Models\Qms\Capa;
use App\Models\Qms\Workflow;
use App\Models\Qms\WorkflowRun;
use Illuminate\Support\Str;

/**
 * Reusable JSON workflow engine.
 * Any module can fire an event; matching active workflows evaluate their
 * conditions and run their actions. Every firing is logged to qms_workflow_runs.
 *
 * Supported actions (extensible):
 *   - { "type": "notify",     "params": { "user_id": "...", "title": "..." } }
 *   - { "type": "create_capa","params": { "type": "corrective", "title": "..." } }
 */
class WorkflowEngine
{
    public function __construct(
        private Notifier $notifier,
        private AuditTrailService $audit,
    ) {}

    /**
     * @param array $context  flat key/value bag describing the entity (used for conditions + actions)
     */
    public function dispatch(string $event, array $context = []): void
    {
        $workflows = Workflow::where('trigger_event', $event)->where('active', true)->get();

        foreach ($workflows as $wf) {
            if (!$this->conditionsMatch($wf->conditions ?? [], $context)) {
                $this->log($wf, $event, $context, 'skipped', ['reason' => 'conditions not met']);
                continue;
            }

            try {
                $executed = [];
                foreach ($wf->actions as $action) {
                    $executed[] = $this->runAction($action, $context);
                }
                $this->log($wf, $event, $context, 'completed', $executed);
            } catch (\Throwable $e) {
                $this->log($wf, $event, $context, 'failed', null, $e->getMessage());
            }
        }
    }

    private function conditionsMatch(array $conditions, array $ctx): bool
    {
        foreach ($conditions as $c) {
            $field = $c['field'] ?? null;
            $op = $c['op'] ?? '=';
            $val = $c['value'] ?? null;
            $actual = $ctx[$field] ?? null;

            $ok = match ($op) {
                '=', '==' => $actual == $val,
                '!=' => $actual != $val,
                '>' => $actual > $val,
                '>=' => $actual >= $val,
                '<' => $actual < $val,
                '<=' => $actual <= $val,
                'in' => in_array($actual, (array) $val, false),
                default => false,
            };
            if (!$ok) {
                return false;
            }
        }
        return true;
    }

    private function runAction(array $action, array $ctx): array
    {
        $type = $action['type'] ?? '';
        $p = $action['params'] ?? [];

        switch ($type) {
            case 'notify':
                $this->notifier->notify(
                    $p['user_id'] ?? ($ctx['owner_id'] ?? $ctx['created_by'] ?? 'system'),
                    $p['notif_type'] ?? 'workflow',
                    $p['title'] ?? 'Workflow notification',
                    $p['body'] ?? null,
                    $ctx['entity_type'] ?? null,
                    $ctx['entity_id'] ?? null,
                    (bool) ($p['email'] ?? false),
                );
                return ['type' => 'notify', 'ok' => true];

            case 'create_capa':
                $capa = Capa::create([
                    'reference' => 'CAPA-' . date('Y') . '-' . strtoupper(Str::random(6)),
                    'incident_id' => $ctx['entity_id'] ?? null,
                    'type' => $p['type'] ?? 'corrective',
                    'title' => $p['title'] ?? ('Auto CAPA from ' . ($ctx['entity_type'] ?? 'event')),
                    'status' => 'open',
                    'created_by' => $ctx['created_by'] ?? 'system',
                ]);
                $this->audit->record('qms_capa', $capa->id, 'create', [
                    'reason' => 'auto-created by workflow',
                ]);
                return ['type' => 'create_capa', 'ok' => true, 'capa_id' => $capa->id];

            default:
                return ['type' => $type, 'ok' => false, 'error' => 'unknown action'];
        }
    }

    private function log(Workflow $wf, string $event, array $ctx, string $status, ?array $result, ?string $error = null): void
    {
        WorkflowRun::create([
            'workflow_id' => $wf->id,
            'trigger_event' => $event,
            'entity_type' => $ctx['entity_type'] ?? null,
            'entity_id' => $ctx['entity_id'] ?? null,
            'status' => $status,
            'result' => $result,
            'error' => $error,
        ]);
    }
}
