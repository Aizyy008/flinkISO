<?php

namespace App\Http\Controllers\Api\Qms;

use App\Http\Controllers\Controller;
use App\Models\Qms\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Current user's notifications. */
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('flink_user');
        $q = Notification::where('user_id', $user['sub'] ?? '')->latest();
        if ($request->boolean('unread')) $q->where('is_read', false);

        return response()->json($q->paginate(30));
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->attributes->get('flink_user');
        $n = Notification::where('user_id', $user['sub'] ?? '')->findOrFail($id);
        $n->update(['is_read' => true]);

        return response()->json($n);
    }
}
