<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\Workflow;
use App\Models\Qms\WorkflowRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Workflow::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|string|max:80',
            'conditions' => 'nullable|array',
            'actions' => 'required|array|min:1',
            'active' => 'boolean',
        ]);

        $user = $request->attributes->get('flink_user');
        $data['created_by'] = $user['sub'] ?? null;

        return response()->json(Workflow::create($data), 201);
    }

    /** Execution history of a workflow. */
    public function runs(string $id): JsonResponse
    {
        return response()->json(
            WorkflowRun::where('workflow_id', $id)->latest('created_at')->paginate(50)
        );
    }
}
