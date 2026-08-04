<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\Task;
use App\Services\Qms\AuditTrailService;
use Illuminate\Http\Request;

/**
 * "My Tasks" — the real tasks assigned to the logged-in user by the workflow
 * engine's "assign a task" / "request an approval" actions. Users see their open
 * tasks and mark them done (a task is not just a notification).
 */
class TaskController extends Controller
{
    public function __construct(private AuditTrailService $audit) {}

    public function index(Request $request)
    {
        $me = $request->session()->get('flink_user')['id'];
        $tasks = Task::where('assigned_to', $me)
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->orderByDesc('created_at')
            ->get();

        return view('tasks.index', ['tasks' => $tasks]);
    }

    public function complete(Request $request, string $id)
    {
        $u = $request->session()->get('flink_user');
        $task = Task::findOrFail($id);
        // Only the assignee can complete their own task.
        abort_unless($task->assigned_to === $u['id'], 403, 'This task is not assigned to you.');

        if ($task->status !== 'done') {
            $task->update([
                'status' => 'done',
                'completed_at' => now(),
                'completed_by' => $u['id'],
            ]);
            $this->audit->record('qms_task', $task->id, 'task_completed', [
                'user_id' => $u['id'], 'username' => $u['username'],
                'changes' => ['status' => ['old' => 'open', 'new' => 'done']],
            ]);
        }

        return back()->with('ok', 'Task marked as done.');
    }

    /** Approve or reject an approval task (records an explicit decision). */
    public function decide(Request $request, string $id)
    {
        $data = $request->validate(['decision' => 'required|in:approve,reject']);
        $u = $request->session()->get('flink_user');
        $task = Task::findOrFail($id);
        abort_unless($task->assigned_to === $u['id'], 403, 'This task is not assigned to you.');

        if ($task->status !== 'done') {
            $outcome = $data['decision'] === 'approve' ? 'approved' : 'rejected';
            $task->update([
                'status' => 'done',
                'outcome' => $outcome,
                'completed_at' => now(),
                'completed_by' => $u['id'],
            ]);
            $this->audit->record('qms_task', $task->id, 'approval_' . $outcome, [
                'user_id' => $u['id'], 'username' => $u['username'],
                'changes' => ['outcome' => ['old' => null, 'new' => $outcome]],
            ]);
        }

        return back()->with('ok', 'Approval ' . $task->outcome . '.');
    }
}
