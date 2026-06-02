<?php

namespace App\Console\Commands;

use App\Mail\InvoiceReminderMail;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\InvoiceDueSoonNotification;
use App\Notifications\InvoiceOverdueNotification;
use App\Support\Enums\InvoiceStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendInvoiceReminders extends Command
{
    protected $signature   = 'invoices:send-reminders {--user= : تشغيل لمستخدم محدد بالـ ID}';
    protected $description = 'إرسال تذكيرات الفواتير المستحقة والمتأخرة (إيميل + تسجيل واتساب)';

    public function handle(): int
    {
        $userId = $this->option('user');
        $users  = $userId ? User::where('id', $userId)->get() : User::all();

        if ($users->isEmpty()) {
            $this->warn('لا يوجد مستخدمون.');
            return self::SUCCESS;
        }

        $emailSent   = 0;
        $whatsappLog = 0;

        foreach ($users as $user) {
            // ── 1. تذكير قبل يومين من الاستحقاق ───────────────────────
            $beforeDue = Invoice::where('user_id', $user->id)
                ->whereIn('status', [InvoiceStatus::Draft->value, InvoiceStatus::Sent->value])
                ->whereNotNull('due_date')
                ->whereDate('due_date', now()->addDays(2)->toDateString())
                ->whereNotExists(function ($q) {
                    $q->from('invoice_reminder_logs')
                      ->whereColumn('invoice_id', 'invoices.id')
                      ->where('type', 'before_due');
                })
                ->with('client')
                ->get();

            foreach ($beforeDue as $invoice) {
                $this->sendReminder($invoice, 'before_due', $emailSent, $whatsappLog);
            }

            // ── 2. تذكير بعد تجاوز الاستحقاق ──────────────────────────
            $overdue = Invoice::where('user_id', $user->id)
                ->whereIn('status', [InvoiceStatus::Draft->value, InvoiceStatus::Sent->value, InvoiceStatus::Overdue->value])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->toDateString())
                ->whereNotExists(function ($q) {
                    $q->from('invoice_reminder_logs')
                      ->whereColumn('invoice_id', 'invoices.id')
                      ->where('type', 'overdue');
                })
                ->with('client')
                ->get();

            foreach ($overdue as $invoice) {
                // تحديث حالة الفاتورة إلى Overdue تلقائياً
                if ($invoice->status !== InvoiceStatus::Overdue) {
                    $invoice->update(['status' => InvoiceStatus::Overdue->value]);
                }
                $this->sendReminder($invoice, 'overdue', $emailSent, $whatsappLog);
            }
        }

        $this->info("✅ تم: {$emailSent} إيميل، {$whatsappLog} تذكير واتساب مسجّل.");
        return self::SUCCESS;
    }

    private function sendReminder(Invoice $invoice, string $type, int &$emailSent, int &$whatsappLog): void
    {
        $client = $invoice->client;
        $user   = User::find($invoice->user_id);

        // ── إشعار داخلي في /notifications ─────────────────────────────
        if ($user) {
            $notification = $type === 'before_due'
                ? new InvoiceDueSoonNotification($invoice)
                : new InvoiceOverdueNotification($invoice);
            $user->notify($notification);
        }

        // ── إرسال الإيميل ──────────────────────────────────────────────
        if (!empty($client->email)) {
            try {
                Mail::to($client->email)->send(new InvoiceReminderMail($invoice, $type));
                $this->logReminder($invoice, $type, 'email');
                $emailSent++;
                $this->line("  📧 إيميل → {$client->name} ({$invoice->number})");
            } catch (\Throwable $e) {
                $this->error("  ❌ فشل إيميل {$invoice->number}: {$e->getMessage()}");
            }
        }

        // ── تسجيل تذكير واتساب (للعرض في لوحة التحكم) ────────────────
        if (!empty($client->phone)) {
            $this->logReminder($invoice, $type, 'whatsapp');
            $whatsappLog++;
            $this->line("  💬 واتساب مسجّل → {$client->name} ({$invoice->number})");
        }
    }

    private function logReminder(Invoice $invoice, string $type, string $channel): void
    {
        DB::table('invoice_reminder_logs')->insertOrIgnore([
            'invoice_id' => $invoice->id,
            'user_id'    => $invoice->user_id,
            'type'       => $type,
            'channel'    => $channel,
            'sent_at'    => now(),
        ]);
    }
}
