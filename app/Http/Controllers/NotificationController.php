<?php

namespace App\Http\Controllers;

use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $service,
    ) {}

    public function index(Request $request): View
    {
        // توليد تنبيهات الديون عند فتح الصفحة
        $this->service->generateDebtAlerts(auth()->user());

        $notifications = $this->service->getPaginated(auth()->user());

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $this->service->markAsRead(auth()->user(), $id);

        // إذا جاء الطلب من إشعار معين، redirect لرابطه
        $notification = auth()->user()->notifications()->where('id', $id)->first();
        $link = $notification ? (json_decode($notification->data, true)['link'] ?? null) : null;

        return $link
            ? redirect($link)
            : back()->with('success', 'تم تحديد الإشعار كمقروء.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        $this->service->markAllAsRead(auth()->user());

        return back()->with('success', 'تم تحديد جميع الإشعارات كمقروءة.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->service->delete(auth()->user(), $id);

        return back()->with('success', 'تم حذف الإشعار.');
    }

    /**
     * API endpoint للـ dropdown في الـ header — يعيد عدد غير المقروءة
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => $this->service->getUnreadCount(auth()->user()),
        ]);
    }
}
