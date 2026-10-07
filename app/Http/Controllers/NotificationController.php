<?php

namespace App\Http\Controllers;

use App\Models\GrowthNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $business = $this->business();
        $type = $request->query('type');

        return view('notifications.index', [
            'business' => $business,
            'notifications' => $business->notifications()->when($type, fn ($q) => $q->where('type', $type))->get(),
            'type' => $type,
            'counts' => $business->notifications()->unread()->selectRaw('type, count(*) as c')->groupBy('type')->pluck('c', 'type'),
        ]);
    }

    public function read(GrowthNotification $notification)
    {
        $this->authorizeOwn($notification);
        $notification->update(['read_at' => $notification->read_at ?? now()]);

        return $notification->action_url ? redirect($notification->action_url) : back();
    }

    public function readAll()
    {
        $this->business()->notifications()->unread()->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
