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

    /** Map a notification's related entity to its QMS screen. */
    public static function recordLink(?string $type, ?string $id): ?string
    {
        $map = [
            'qms_incident' => '/incidents/', 'qms_capa' => '/capa/', 'qms_risk' => '/risks/',
            'qms_document' => '/documents/', 'qms_training' => '/training/', 'qms_asset' => '/assets/',
            'qms_haccp' => '/haccp/',
        ];
        return ($type && $id && isset($map[$type])) ? $map[$type] . $id : null;
    }

    public function markRead(Request $request, string $id)
    {
        Notification::where('user_id', $this->uid($request))->where('id', $id)->update(['is_read' => true]);
        $n = Notification::find($id);

        // The modal marks read over AJAX; reply with the fresh unread count.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'unread' => Notification::where('user_id', $this->uid($request))->where('is_read', false)->count(),
            ]);
        }

        // No-JS fallback: jump to the related record if there is one.
        if ($n && ($link = self::recordLink($n->related_type, $n->related_id))) {
            return redirect($link);
        }
        return back();
    }

    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $this->uid($request))->where('is_read', false)->update(['is_read' => true]);
        return back()->with('ok', 'All notifications marked as read.');
    }
}
