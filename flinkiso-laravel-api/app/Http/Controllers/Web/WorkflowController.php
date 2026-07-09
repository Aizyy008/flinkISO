<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\Workflow;
use App\Models\Qms\WorkflowRun;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function index()
    {
        $workflows = Workflow::latest()->get();
        $runs = WorkflowRun::latest('created_at')->limit(30)->get();
        return view('workflows.index', compact('workflows', 'runs'));
    }

    public function create()
    {
        return view('workflows.create');
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
        if (empty($actions)) {
            return back()->withErrors(['action' => 'Choose at least one action.'])->withInput();
        }

        Workflow::create([
            'name' => $data['name'],
            'trigger_event' => $data['trigger_event'],
            'conditions' => $conditions,
            'actions' => $actions,
            'active' => true,
            'created_by' => $request->session()->get('flink_user')['id'],
        ]);

        return redirect('/workflows')->with('ok', 'Workflow rule created and active.');
    }

    public function toggle(string $id)
    {
        $wf = Workflow::findOrFail($id);
        $wf->update(['active' => !$wf->active]);
        return back()->with('ok', 'Workflow ' . ($wf->active ? 'activated' : 'deactivated') . '.');
    }
}
