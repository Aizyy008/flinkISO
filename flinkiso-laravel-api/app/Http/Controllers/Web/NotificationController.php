<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Qms\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private function uid(Request $r): string { return $r->session()->get('flink_user')['id']; }

    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $this->uid($request))->latest()->paginate(30);
        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, string $id)
    {
        Notification::where('user_id', $this->uid($request))->where('id', $id)->update(['is_read' => true]);
        $n = Notification::find($id);
        // Jump to the related record if there is one.
        if ($n && $n->related_type && $n->related_id) {
            $map = ['qms_incident' => '/incidents/', 'qms_capa' => '/capa/', 'qms_risk' => '/risks/', 'qms_document' => '/documents/'];
            if (isset($map[$n->related_type])) {
                return redirect($map[$n->related_type] . $n->related_id);
            }
        }
        return back();
    }

    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $this->uid($request))->where('is_read', false)->update(['is_read' => true]);
        return back()->with('ok', 'All notifications marked as read.');
    }
}
