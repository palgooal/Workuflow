<?php

namespace App\Services\DataExport;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Client;
use App\Models\Debt;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use App\Models\ProjectServiceMember;
use App\Models\ProjectServicePivot;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\RecurringTransaction;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransfer;
use App\Modules\CRM\Models\ClientActivity;
use App\Modules\CRM\Models\ClientAttachment;
use App\Modules\CRM\Models\ClientFollowUp;
use App\Modules\CRM\Models\ClientTag;
use App\Modules\CRM\Models\ClientTagAssignment;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * UserDataExportService — يبني أرشيف ZIP يحتوي بيانات مستخدم واحد فقط.
 *
 * ⚠️ قواعد أمان صارمة (راجع docs/DATA-EXPORT.md قبل التعديل):
 *  - كل استعلام هنا يستخدم where('user_id', $userId) صراحةً — حتى للموديلات
 *    التي تملك BelongsToUser أصلاً (تعليمات صريحة: لا اعتماد على Global Scope
 *    وحده، لأن هذا الكود قد يعمل في سياق Job بدون auth() نشط).
 *  - ممنوع منعاً باتاً: كلمات المرور، tokens (بما فيها Quote::token الصريح)،
 *    مفاتيح/أسرار بوابات الدفع، أرقام بطاقات، الـ IDs الداخلية المتسلسلة
 *    (auto-increment) — تُستبدَل بمفاتيح طبيعية (ULID/slug) أو مراجع محلية
 *    مولَّدة لهذا التصدير فقط (SVC-1, PS-1...) بدل الأرقام التسلسلية الحقيقية.
 */
class UserDataExportService
{
    private string $workDir;

    /** array<string, array<int, array<string,mixed>>> */
    private array $dataset = [];

    public function build(User $user): string
    {
        $userId = $user->id;

        $this->workDir = storage_path('app/private/tmp/user-data-export-'.Str::ulid());
        File::ensureDirectoryExists($this->workDir.'/data');
        File::ensureDirectoryExists($this->workDir.'/attachments');

        try {
            $this->buildAccount($user);
            $this->buildSettings($userId);

            $clientPublicIds = $this->buildClients($userId);
            $this->buildClientTags($userId);
            $this->buildClientTagAssignments($userId, $clientPublicIds);
            $this->buildClientFollowUps($userId);
            $this->buildClientActivities($userId);
            $this->buildClientAttachments($userId);

            $this->buildProjects($userId);
            $svcRefMap = $this->buildServices($userId);
            $psRefMap  = $this->buildProjectServices($userId, $svcRefMap);
            $this->buildProjectServiceMembers($psRefMap);
            $this->buildTeamMembers($userId);

            $this->buildTransactions($userId);
            $this->buildCategories($userId);
            $this->buildWallets($userId);
            $this->buildWalletTransfers($userId);
            $this->buildDebts($userId);
            $this->buildRecurringTransactions($userId);
            $this->buildBudgets($userId);

            $this->buildInvoices($userId);
            $this->buildQuotes($userId);

            $this->writeJsonBundle();
            $this->writeReadme($user);

            return $this->zipAndStore($user);
        } finally {
            File::deleteDirectory($this->workDir);
        }
    }

    // ==================== الحساب والإعدادات ====================

    private function buildAccount(User $user): void
    {
        $this->writeCsv('account', [[
            'name'               => $user->name,
            'email'              => $user->email,
            'phone'              => $user->phone,
            'currency'           => $user->currency,
            'timezone'           => $user->timezone,
            'target_margin_pct'  => $user->target_margin_pct,
            'subscription_plan'  => $user->subscription_plan?->value,
            'billing_city'       => $user->billing_city,
            'billing_country'    => $user->billing_country,
            'member_since'       => optional($user->created_at)->toDateString(),
        ]]);
    }

    private function buildSettings(int $userId): void
    {
        // فقط إعدادات تخصيص الفاتورة الخاصة بهذا المستخدم — لا شيء من مجموعة
        // "payment" (تحتوي togo_api_key وأسرار بوابة الدفع) يُقرأ هنا إطلاقاً.
        $keys = [
            "invoice_color_{$userId}"        => 'invoice_color',
            "invoice_company_name_{$userId}" => 'invoice_company_name',
            "invoice_company_info_{$userId}" => 'invoice_company_info',
            "invoice_footer_{$userId}"       => 'invoice_footer',
        ];

        $rows = [];
        foreach ($keys as $rawKey => $label) {
            $value = Setting::get($rawKey);
            if ($value !== null && $value !== '') {
                $rows[] = ['setting' => $label, 'value' => $value];
            }
        }

        $this->writeCsv('settings', $rows);
    }

    // ==================== العملاء ====================

    /** @return array<int,string> map: internal client id => public_id */
    private function buildClients(int $userId): array
    {
        $clients = Client::query()->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->get();

        $map = [];
        $rows = [];
        foreach ($clients as $client) {
            $map[$client->id] = $client->public_id;
            $rows[] = [
                'id'               => $client->public_id,
                'name'             => $client->name,
                'payment_name'     => $client->payment_name,
                'phone'            => $client->phone,
                'email'            => $client->email,
                'company'          => $client->company,
                'position'         => $client->position,
                'website'          => $client->website,
                'address'          => $client->address,
                'city'             => $client->city,
                'country'          => $client->country,
                'notes'            => $client->notes,
                'is_active'        => $client->is_active,
                'status'           => $client->status?->value,
                'source'           => $client->source?->value,
                'is_archived'      => $client->is_archived,
                'total_revenue'    => $client->total_revenue,
                'total_paid'       => $client->total_paid,
                'invoice_count'    => $client->invoice_count,
                'health_score'     => $client->health_score,
                'last_payment_at'  => optional($client->last_payment_at)->toDateTimeString(),
                'last_contact_at'  => optional($client->last_contact_at)->toDateTimeString(),
                'created_at'       => optional($client->created_at)->toDateTimeString(),
            ];
        }

        $this->writeCsv('clients', $rows);
        $this->clientMap = $map;

        return $map;
    }

    /** @var array<int,string> */
    private array $clientMap = [];

    private function buildClientTags(int $userId): void
    {
        $tags = ClientTag::query()->where('user_id', $userId)->get();

        $rows = $tags->map(fn (ClientTag $tag) => [
            'slug'      => $tag->slug,
            'name'      => $tag->name,
            'color'     => $tag->color,
            'type'      => $tag->type?->value,
            'is_active' => $tag->is_active,
            'priority'  => $tag->priority,
        ])->all();

        $this->writeCsv('client_tags', $rows);
    }

    private function buildClientTagAssignments(int $userId, array $clientMap): void
    {
        if (empty($clientMap)) {
            $this->writeCsv('client_tag_assignments', []);
            return;
        }

        $assignments = ClientTagAssignment::query()
            ->whereIn('client_id', array_keys($clientMap))
            ->with('tag:id,slug')
            ->get();

        $rows = $assignments->map(fn ($a) => [
            'client_id'   => $clientMap[$a->client_id] ?? null,
            'tag_slug'    => $a->tag?->slug,
            'assigned_at' => optional($a->assigned_at)->toDateTimeString(),
        ])->all();

        $this->writeCsv('client_tag_assignments', $rows);
    }

    private function buildClientFollowUps(int $userId): void
    {
        $rows = ClientFollowUp::query()
            ->where('user_id', $userId)
            ->with('client:id,public_id')
            ->get()
            ->map(fn (ClientFollowUp $f) => [
                'id'           => $f->id,
                'client_id'    => $f->client?->public_id,
                'type'         => $f->type,
                'title'        => $f->title,
                'status'       => $f->status?->value,
                'due_at'       => optional($f->due_at)->toDateTimeString(),
                'completed_at' => optional($f->completed_at)->toDateTimeString(),
                'priority'     => $f->priority,
                'notes'        => $f->notes,
                'created_at'   => optional($f->created_at)->toDateTimeString(),
            ])->all();

        $this->writeCsv('client_follow_ups', $rows);
    }

    private function buildClientActivities(int $userId): void
    {
        $rows = ClientActivity::query()
            ->where('user_id', $userId)
            ->with('client:id,public_id')
            ->get()
            ->map(fn (ClientActivity $a) => [
                'client_id'   => $a->client?->public_id,
                'type'        => $a->type?->value,
                'description' => $a->description,
                'occurred_at' => optional($a->occurred_at)->toDateTimeString(),
            ])->all();

        $this->writeCsv('client_activities', $rows);
    }

    private function buildClientAttachments(int $userId): void
    {
        $attachments = ClientAttachment::query()
            ->where('user_id', $userId)
            ->with('client:id,public_id')
            ->get();

        $rows = [];
        foreach ($attachments as $att) {
            $exportedName = null;

            try {
                if (Storage::disk($att->disk)->exists($att->path)) {
                    $clientDir = $this->workDir.'/attachments/'.($att->client?->public_id ?? 'unknown');
                    File::ensureDirectoryExists($clientDir);

                    $safeName    = $att->id.'-'.preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $att->filename ?? 'file');
                    $destination = $clientDir.'/'.$safeName;

                    File::put($destination, Storage::disk($att->disk)->get($att->path));
                    $exportedName = $safeName;
                }
            } catch (\Throwable) {
                // ملف مفقود على القرص — نتجاهله في الأرشيف مع تسجيله في CSV كـ "غير متوفر"
                $exportedName = null;
            }

            $rows[] = [
                'id'          => $att->id,
                'client_id'   => $att->client?->public_id,
                'filename'    => $att->filename,
                'mime_type'   => $att->mime_type,
                'size_bytes'  => $att->size_bytes,
                'description' => $att->description,
                'exported_as' => $exportedName ? 'attachments/'.($att->client?->public_id ?? 'unknown').'/'.$exportedName : 'غير متوفر',
                'created_at'  => optional($att->created_at)->toDateTimeString(),
            ];
        }

        $this->writeCsv('client_attachments', $rows);
    }

    // ==================== المشاريع والخدمات وأعضاء الفريق ====================

    private function buildProjects(int $userId): void
    {
        $rows = Project::query()->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->with('client:id,public_id')
            ->get()
            ->map(fn (Project $p) => [
                'id'             => $p->id,
                'client_id'      => $p->client?->public_id,
                'name'           => $p->name,
                'description'    => $p->description,
                'currency'       => $p->currency,
                'type'           => $p->type?->value,
                'status'         => $p->status?->value,
                'contract_value' => $p->contract_value,
                'expense_budget' => $p->expense_budget,
                'created_at'     => optional($p->created_at)->toDateTimeString(),
            ])->all();

        $this->writeCsv('projects', $rows);
    }

    /** @return array<int,string> map: service internal id => local export ref (SVC-n) لخدمات المستخدم غير العامة فقط */
    private function buildServices(int $userId): array
    {
        $services = Service::query()->where('user_id', $userId)->where('is_global', false)->get();

        $map  = [];
        $rows = [];
        $i    = 1;
        foreach ($services as $service) {
            $ref = 'SVC-'.$i++;
            $map[$service->id] = $ref;
            $rows[] = [
                'id'          => $ref,
                'name'        => $service->name,
                'name_ar'     => $service->name_ar,
                'description' => $service->description,
                'is_active'   => $service->is_active,
            ];
        }

        $this->writeCsv('services', $rows);

        return $map;
    }

    /**
     * @return array<int,string> map: project_service pivot internal id => local ref (PS-n)
     *
     * ⚠️ ProjectServicePivot لا يملك علاقات project()/service() مباشرة (فقط
     * members())، لذا نمرّ عبر Project::services() بدلاً من استعلام الـ pivot
     * مباشرة — نفس البيانات، دون افتراض علاقات غير موجودة في الموديل.
     */
    private function buildProjectServices(int $userId, array $svcRefMap): array
    {
        $projects = Project::query()->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->with('services')
            ->get();

        $map  = [];
        $rows = [];
        $i    = 1;
        foreach ($projects as $project) {
            foreach ($project->services as $service) {
                /** @var ProjectServicePivot $pivot */
                $pivot = $service->pivot;
                $ref   = 'PS-'.$i++;
                $map[$pivot->id] = $ref;

                $serviceLabel = $service->is_global
                    ? ($service->name_ar ?? $service->name)
                    : ($svcRefMap[$service->id] ?? ($service->name_ar ?? $service->name));

                $rows[] = [
                    'id'                 => $ref,
                    'project_id'         => $project->id,
                    'service'            => $serviceLabel,
                    'client_id'          => $this->clientMap[$pivot->client_id] ?? null,
                    'amount'             => $pivot->amount,
                    'type'               => $pivot->type,
                    'notes'              => $pivot->notes,
                    'target_margin_pct'  => $pivot->target_margin_pct,
                ];
            }
        }

        $this->writeCsv('project_services', $rows);

        return $map;
    }

    private function buildProjectServiceMembers(array $psRefMap): void
    {
        if (empty($psRefMap)) {
            $this->writeCsv('project_service_members', []);
            return;
        }

        $members = ProjectServiceMember::query()
            ->whereIn('project_service_id', array_keys($psRefMap))
            ->with('teamMember:id,name')
            ->get();

        $rows = $members->map(fn (ProjectServiceMember $m) => [
            'project_service_id' => $psRefMap[$m->project_service_id] ?? null,
            'team_member'        => $m->teamMember?->name,
            'team_member_id'     => $m->team_member_id,
            'team_cost'          => $m->team_cost,
            'team_cost_paid'     => $m->team_cost_paid,
        ])->all();

        $this->writeCsv('project_service_members', $rows);
    }

    private function buildTeamMembers(int $userId): void
    {
        $rows = TeamMember::query()->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->get()
            ->map(fn (TeamMember $t) => [
                'id'           => $t->id,
                'name'         => $t->name,
                'type'         => $t->type,
                'specialty'    => $t->specialty,
                'phone'        => $t->phone,
                'email'        => $t->email,
                'default_rate' => $t->default_rate,
                'notes'        => $t->notes,
                'is_active'    => $t->is_active,
            ])->all();

        $this->writeCsv('team_members', $rows);
    }

    // ==================== المعاملات والفئات والصناديق ====================

    private function buildTransactions(int $userId): void
    {
        Transaction::query()->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->with(['project:id', 'category:id,name', 'wallet:id,name'])
            ->chunk(1000, function ($chunk) {
                $rows = $chunk->map(fn (Transaction $t) => [
                    'id'               => $t->id,
                    'project_id'       => $t->project_id,
                    'wallet'           => $t->wallet?->name,
                    'category'         => $t->category?->name,
                    'type'             => $t->type?->value,
                    'amount'           => $t->amount,
                    'currency'         => $t->currency,
                    'description'      => $t->description,
                    'payee'            => $t->payee,
                    'notes'            => $t->notes,
                    'transaction_date' => optional($t->transaction_date)->toDateString(),
                    'reference'        => $t->reference,
                ])->all();

                $this->appendCsv('transactions', $rows);
            });
    }

    private function buildCategories(int $userId): void
    {
        $rows = Category::query()->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->get()
            ->map(fn (Category $c) => [
                'id'         => $c->id,
                'name'       => $c->name,
                'type'       => $c->type?->value,
                'is_default' => $c->is_default,
            ])->all();

        $this->writeCsv('categories', $rows);
    }

    private function buildWallets(int $userId): void
    {
        $rows = Wallet::query()->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->get()
            ->map(fn (Wallet $w) => [
                'id'              => $w->id,
                'name'            => $w->name,
                'type'            => $w->type?->value,
                'currency'        => $w->currency,
                'initial_balance' => $w->initial_balance,
                'description'     => $w->description,
                'is_active'       => $w->is_active,
                'current_balance' => $w->balance(),
            ])->all();

        $this->writeCsv('wallets', $rows);
    }

    private function buildWalletTransfers(int $userId): void
    {
        $rows = WalletTransfer::query()->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->with(['fromWallet:id,name', 'toWallet:id,name'])
            ->get()
            ->map(fn (WalletTransfer $wt) => [
                'id'             => $wt->id,
                'from_wallet'    => $wt->fromWallet?->name,
                'to_wallet'      => $wt->toWallet?->name,
                'amount'         => $wt->amount,
                'fee'            => $wt->fee,
                'description'    => $wt->description,
                'reference'      => $wt->reference,
                'transferred_at' => optional($wt->transferred_at)->toDateString(),
            ])->all();

        $this->writeCsv('wallet_transfers', $rows);
    }

    private function buildDebts(int $userId): void
    {
        $rows = Debt::query()->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->get()
            ->map(fn (Debt $d) => [
                'id'                => $d->id,
                'project_id'        => $d->project_id,
                'type'              => $d->type?->value,
                'party_name'        => $d->party_name,
                'amount'            => $d->amount,
                'remaining_amount'  => $d->remaining_amount,
                'currency'          => $d->currency,
                'due_date'          => optional($d->due_date)->toDateString(),
                'status'            => $d->status?->value,
                'notes'             => $d->notes,
                'created_at'        => optional($d->created_at)->toDateTimeString(),
            ])->all();

        $this->writeCsv('debts', $rows);
    }

    private function buildRecurringTransactions(int $userId): void
    {
        $rows = RecurringTransaction::query()->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->with(['category:id,name'])
            ->get()
            ->map(fn (RecurringTransaction $r) => [
                'id'            => $r->id,
                'project_id'    => $r->project_id,
                'category'      => $r->category?->name,
                'type'          => $r->type?->value,
                'amount'        => $r->amount,
                'currency'      => $r->currency,
                'description'   => $r->description,
                'frequency'     => $r->frequency?->value,
                'start_date'    => optional($r->start_date)->toDateString(),
                'next_due_date' => optional($r->next_due_date)->toDateString(),
                'end_date'      => optional($r->end_date)->toDateString(),
                'is_active'     => $r->is_active,
            ])->all();

        $this->writeCsv('recurring_transactions', $rows);
    }

    private function buildBudgets(int $userId): void
    {
        $rows = Budget::query()->withoutGlobalScope('user')
            ->where('user_id', $userId)
            ->with(['category:id,name'])
            ->get()
            ->map(fn (Budget $b) => [
                'id'         => $b->id,
                'project_id' => $b->project_id,
                'category'   => $b->category?->name,
                'amount'     => $b->amount,
                'period'     => $b->period,
                'month'      => $b->month,
                'year'       => $b->year,
            ])->all();

        $this->writeCsv('budgets', $rows);
    }

    // ==================== الفواتير وعروض الأسعار ====================

    private function buildInvoices(int $userId): void
    {
        $invoices = Invoice::query()
            ->where('user_id', $userId)
            ->with('client:id,public_id')
            ->get();

        $rows = $invoices->map(fn (Invoice $inv) => [
            'id'            => $inv->ulid,
            'client_id'     => $inv->client?->public_id,
            'project_id'    => $inv->project_id,
            'number'        => $inv->number,
            'status'        => $inv->status?->value,
            'title'         => $inv->title,
            'issue_date'    => optional($inv->issue_date)->toDateString(),
            'due_date'      => optional($inv->due_date)->toDateString(),
            'subtotal'      => $inv->subtotal,
            'tax_rate'      => $inv->tax_rate,
            'tax_amount'    => $inv->tax_amount,
            'discount'      => $inv->discount,
            'discount_type' => $inv->discount_type,
            'total'         => $inv->total,
            'currency'      => $inv->currency,
            'notes'         => $inv->notes,
            'terms'         => $inv->terms,
            'sent_at'       => optional($inv->sent_at)->toDateTimeString(),
            'paid_at'       => optional($inv->paid_at)->toDateTimeString(),
            'created_at'    => optional($inv->created_at)->toDateTimeString(),
        ])->all();
        $this->writeCsv('invoices', $rows);

        $itemRows = InvoiceItem::query()
            ->whereIn('invoice_id', $invoices->pluck('id'))
            ->get()
            ->map(function (InvoiceItem $item) use ($invoices) {
                $invoice = $invoices->firstWhere('id', $item->invoice_id);
                return [
                    'invoice_number' => $invoice?->number,
                    'description'    => $item->description,
                    'quantity'       => $item->quantity,
                    'unit_price'     => $item->unit_price,
                    'total'          => $item->total,
                    'sort_order'     => $item->sort_order,
                ];
            })->all();
        $this->writeCsv('invoice_items', $itemRows);
    }

    private function buildQuotes(int $userId): void
    {
        $quotes = Quote::query()
            ->where('user_id', $userId)
            ->with('client:id,public_id')
            ->get();

        // ⚠️ Quote::token مستبعد صراحةً — نص صريح غير مُجزَّأ يُستخدم كرابط عام
        // لبوابة العميل، يُعامَل كمفتاح وصول (راجع قرار الموافقة على هذه المهمة).
        $rows = $quotes->map(fn (Quote $q) => [
            'id'            => $q->ulid,
            'client_id'     => $q->client?->public_id,
            'project_id'    => $q->project_id,
            'number'        => $q->number,
            'title'         => $q->title,
            'status'        => $q->status?->value,
            'issue_date'    => optional($q->issue_date)->toDateString(),
            'valid_until'   => optional($q->valid_until)->toDateString(),
            'subtotal'      => $q->subtotal,
            'tax_rate'      => $q->tax_rate,
            'tax_amount'    => $q->tax_amount,
            'discount'      => $q->discount,
            'discount_type' => $q->discount_type,
            'total'         => $q->total,
            'currency'      => $q->currency,
            'notes'         => $q->notes,
            'terms'         => $q->terms,
            'sent_at'       => optional($q->sent_at)->toDateTimeString(),
            'viewed_at'     => optional($q->viewed_at)->toDateTimeString(),
            'accepted_at'   => optional($q->accepted_at)->toDateTimeString(),
            'rejected_at'   => optional($q->rejected_at)->toDateTimeString(),
            'converted_at'  => optional($q->converted_at)->toDateTimeString(),
            'created_at'    => optional($q->created_at)->toDateTimeString(),
        ])->all();
        $this->writeCsv('quotes', $rows);

        $itemRows = QuoteItem::query()
            ->whereIn('quote_id', $quotes->pluck('id'))
            ->get()
            ->map(function (QuoteItem $item) use ($quotes) {
                $quote = $quotes->firstWhere('id', $item->quote_id);
                return [
                    'quote_number' => $quote?->number,
                    'description'  => $item->description,
                    'quantity'     => $item->quantity,
                    'unit_price'   => $item->unit_price,
                    'total'        => $item->total,
                    'sort_order'   => $item->sort_order,
                ];
            })->all();
        $this->writeCsv('quote_items', $itemRows);
    }

    // ==================== الكتابة (CSV / JSON / README / ZIP) ====================

    private function writeCsv(string $entity, array $rows): void
    {
        $this->dataset[$entity] = $rows;
        $this->appendCsv($entity, $rows, truncate: true);
    }

    private function appendCsv(string $entity, array $rows, bool $truncate = false): void
    {
        if ($truncate || ! isset($this->dataset[$entity])) {
            $this->dataset[$entity] = $rows;
        } else {
            $this->dataset[$entity] = array_merge($this->dataset[$entity], $rows);
        }

        $path   = $this->workDir."/data/{$entity}.csv";
        $exists = File::exists($path);

        $handle = fopen($path, $exists && ! $truncate ? 'a' : 'w');

        if (! $exists || $truncate) {
            if (empty($rows)) {
                fclose($handle);
                if ($truncate) {
                    // ملف فارغ لكن موجود — أفضل من غيابه تماماً لضمان اتساق البنية
                    File::put($path, "\xEF\xBB\xBF");
                }
                return;
            }
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, array_keys($rows[0]));
        }

        foreach ($rows as $row) {
            fputcsv($handle, array_map(
                fn ($v) => is_bool($v) ? ($v ? '1' : '0') : $v,
                $row
            ));
        }

        fclose($handle);
    }

    private function writeJsonBundle(): void
    {
        File::put(
            $this->workDir.'/data.json',
            json_encode($this->dataset, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    private function writeReadme(User $user): void
    {
        $now = now()->translatedFormat('Y-m-d H:i');

        $content = <<<MD
# نسخة بيانات حسابك — دراهم (Workuflow)

تاريخ الإنشاء: {$now}
الحساب: {$user->email}

## ما هذه النسخة؟

هذه نسخة من **بياناتك أنت فقط** كما هي مخزَّنة في منصة دراهم، بصيغة يمكنك
فتحها وقراءتها دون الحاجة لأي برنامج خاص (CSV/JSON عاديّين).

## ما هذه النسخة **ليست**:

- **ليست نسخة كاملة من قاعدة البيانات.**
- **ليست أداة استعادة (Restore)** — لا يمكن رفعها لاستعادة حسابك تلقائياً.
- **لا تحتوي** كلمة مرورك، أو أي رمز دخول (token)، أو مفاتيح/أسرار بوابات
  الدفع، أو أي بيانات بطاقات بنكية.
- **لا تحتوي** رابط بوابة عرض السعر العام (Quote token) — لأن هذا الرابط
  يُعامَل كمفتاح وصول ولا يجوز مشاركته خارج حسابك.

## محتويات الأرشيف

- `data/*.csv` — ملفات CSV منفصلة لكل نوع بيانات (يمكن فتحها في Excel أو
  Google Sheets مباشرة).
- `data.json` — كل البيانات نفسها مجمَّعة في ملف JSON واحد، لتسهيل نقلها
  لأي نظام آخر مستقبلاً.
- `attachments/` — نسخة من مرفقات عملائك (إن وُجدت)، مرتّبة في مجلد فرعي
  لكل عميل.

## ملاحظات

- بعض الحقول الداخلية (كأرقام تعريف تسلسلية) استُبدلت بمعرّفات غير حسّاسة
  لحماية خصوصيتك.
- إن أردت استعادة عملك داخل دراهم، تواصل مع الدعم — هذه النسخة للاطّلاع
  والاحتفاظ الشخصي فقط.
MD;

        File::put($this->workDir.'/README.md', $content);
    }

    private function zipAndStore(User $user): string
    {
        $disk = config('backups.user_export.disk');
        $dir  = config('backups.user_export.path');

        $zipName = 'export-'.$user->id.'-'.now()->format('Ymd-His').'-'.Str::random(6).'.zip';
        $zipPath = $this->workDir.'.zip';

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $this->addDirToZip($zip, $this->workDir, '');
        $zip->close();

        $storagePath = trim($dir, '/').'/'.$zipName;
        Storage::disk($disk)->put($storagePath, File::get($zipPath));
        File::delete($zipPath);

        return $storagePath;
    }

    private function addDirToZip(ZipArchive $zip, string $dir, string $prefix): void
    {
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath  = $dir.'/'.$item;
            $localPath = $prefix === '' ? $item : $prefix.'/'.$item;

            if (is_dir($fullPath)) {
                $this->addDirToZip($zip, $fullPath, $localPath);
            } else {
                $zip->addFile($fullPath, $localPath);
            }
        }
    }
}
