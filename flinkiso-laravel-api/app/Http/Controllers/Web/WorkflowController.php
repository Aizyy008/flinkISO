<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\Workflow;
use App\Models\Qms\WorkflowRun;
use App\Services\Qms\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowController extends Controller
{
    public function __construct(private AuditTrailService $audit) {}

    private function users()
    {
        return DB::connection('flinkiso')->table('users')->where('soft_delete', 0)->orderBy('name')->get(['id', 'name', 'username']);
    }

    public function index()
    {
        $workflows = Workflow::latest()->get();
        $runs = WorkflowRun::latest('created_at')->limit(30)->get();
        return view('workflows.index', compact('workflows', 'runs'));
    }

    public function create()
    {
        return view('workflows.create', ['users' => $this->users()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|string|max:80',
            'cond_field' => 'nullable|string|max:60',
            'cond_op' => 'nullable|in:=,!=,>,>=,<,<=,in',
            'cond_value' => 'nullable|string|max:120',
            'action_create_capa' => 'nullable|boolean',
            'capa_title' => 'nullable|string|max:255',
            'action_notify' => 'nullable|boolean',
            'notify_title' => 'nullable|string|max:255',
            'notify_email' => 'nullable|boolean',
            'action_assign_task' => 'nullable|boolean',
            'task_title' => 'nullable|string|max:255',
            'task_user_id' => 'nullable|string|max:36',
            'action_request_approval' => 'nullable|boolean',
            'approval_title' => 'nullable|string|max:255',
            'approver_id' => 'nullable|string|max:36',
        ]);

        $conditions = [];
        if (!empty($data['cond_field']) && !empty($data['cond_value'])) {
            $conditions[] = ['field' => $data['cond_field'], 'op' => $data['cond_op'] ?? '=', 'value' => $data['cond_value']];
        }

        $actions = [];
        if (!empty($data['action_create_capa'])) {
            $actions[] = ['type' => 'create_capa', 'params' => ['title' => $data['capa_title'] ?: 'Auto CAPA from workflow']];
        }
        if (!empty($data['action_notify'])) {
            $actions[] = ['type' => 'notify', 'params' => ['title' => $data['notify_title'] ?: 'Workflow notification', 'email' => (bool) ($data['notify_email'] ?? false)]];
        }
        if (!empty($data['action_assign_task'])) {
            $actions[] = ['type' => 'assign_task', 'params' => array_filter(['title' => $data['task_title'] ?: 'Task assigned by workflow', 'user_id' => $data['task_user_id'] ?? null])];
        }
        if (!empty($data['action_request_approval'])) {
            $actions[] = ['type' => 'request_approval', 'params' => array_filter(['title' => $data['approval_title'] ?: 'Approval requested', 'approver_id' => $data['approver_id'] ?? null])];
        }
        if (empty($actions)) {
            return back()->withErrors(['action' => 'Choose at least one action.'])->withInput();
        }

        $u = $request->session()->get('flink_user');
        $wf = Workflow::create([
            'name' => $data['name'],
            'trigger_event' => $data['trigger_event'],
            'conditions' => $conditions,
            'actions' => $actions,
            'active' => true,
            'created_by' => $u['id'],
        ]);
        $this->audit->record('qms_workflow', $wf->id, 'create', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['new' => ['name' => $wf->name, 'trigger' => $wf->trigger_event, 'actions' => array_column($actions, 'type')]],
        ]);

        return redirect('/workflows')->with('ok', 'Workflow rule created and active.');
    }

    public function toggle(Request $request, string $id)
    {
        $wf = Workflow::findOrFail($id);
        $wf->update(['active' => !$wf->active]);
        $u = $request->session()->get('flink_user');
        $this->audit->record('qms_workflow', $wf->id, 'toggle', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['active' => $wf->active],
        ]);
        return back()->with('ok', 'Workflow ' . ($wf->active ? 'activated' : 'deactivated') . '.');
    }
}
