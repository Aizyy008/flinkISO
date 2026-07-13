<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\Training;
use App\Models\Qms\TrainingRecord;
use App\Services\Qms\AuditTrailService;
use App\Services\Qms\Notifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainingController extends Controller
{
    public function __construct(private AuditTrailService $audit, private Notifier $notifier) {}

    private function user(Request $r): array { return $r->session()->get('flink_user'); }

    private function users()
    {
        return DB::connection('flinkiso')->table('users')->where('soft_delete', 0)->orderBy('name')->get(['id', 'name', 'username']);
    }

    /** Course list + competency matrix (users x trainings). */
    public function index()
    {
        $trainings = Training::orderBy('title')->get();
        $users = $this->users();
        $records = TrainingRecord::all()->groupBy(fn ($r) => $r->user_id . '|' . $r->training_id);
        return view('training.index', compact('trainings', 'users', 'records'));
    }

    public function create()
    {
        return view('training.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:60',
            'validity_months' => 'nullable|integer|min:1',
            'mandatory' => 'nullable|boolean',
        ]);
        $u = $this->user($request);
        $data['reference'] = $this->nextReference();
        $data['mandatory'] = (bool) ($data['mandatory'] ?? false);
        $data['created_by'] = $u['id'];
        $t = Training::create($data);
        $this->audit->record('qms_training', $t->id, 'create', ['user_id' => $u['id'], 'username' => $u['username'], 'changes' => ['new' => $t->only(['reference', 'title'])]]);
        return redirect('/training/' . $t->id)->with('ok', "Training '{$t->title}' created.");
    }

    public function show(string $id)
    {
        $training = Training::findOrFail($id);
        $users = $this->users();
        $records = TrainingRecord::where('training_id', $id)->get();
        return view('training.show', compact('training', 'users', 'records'));
    }

    /** Assign a training requirement to a user. */
    public function assign(Request $request, string $id)
    {
        $data = $request->validate(['user_id' => 'required|string|max:36']);
        $training = Training::findOrFail($id);
        $u = $this->user($request);

        $record = TrainingRecord::firstOrCreate(
            ['training_id' => $training->id, 'user_id' => $data['user_id'], 'status' => 'assigned'],
            ['created_by' => $u['id']]
        );
        $this->notifier->notify($data['user_id'], 'assignment',
            "Training assigned: {$training->title}",
            'Please complete the required training.', 'qms_training', $training->id, true);
        $this->audit->record('qms_training', $training->id, 'assign', ['user_id' => $u['id'], 'username' => $u['username']]);

        return back()->with('ok', 'Training assigned.');
    }

    /** Mark a training record complete; expiry is computed from the retraining cycle. */
    public function complete(Request $request, string $recordId)
    {
        $data = $request->validate([
            'completed_date' => 'required|date',
            'result' => 'nullable|string|max:255',
        ]);
        $record = TrainingRecord::with('training')->findOrFail($recordId);
        $completed = Carbon::parse($data['completed_date']);
        $expiry = $record->training->validity_months
            ? $completed->copy()->addMonths($record->training->validity_months) : null;

        $record->update([
            'status' => 'completed',
            'completed_date' => $completed->toDateString(),
            'expiry_date' => $expiry?->toDateString(),
            'result' => $data['result'] ?? 'Completed',
        ]);
        $u = $this->user($request);
        $this->audit->record('qms_training', $record->training_id, 'training_completed', [
            'user_id' => $u['id'], 'username' => $u['username'],
            'changes' => ['user' => $record->user_id, 'expiry' => $expiry?->toDateString()],
        ]);
        return back()->with('ok', 'Training marked complete.' . ($expiry ? " Valid until {$expiry->format('d M Y')}." : ''));
    }

    private function nextReference(): string
    {
        $year = date('Y');
        $count = Training::where('reference', 'like', "TRN $year %")->count() + 1;
        return sprintf('TRN %s %04d', $year, $count);
    }
}
