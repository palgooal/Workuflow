<?php

namespace App\Support\Enums;

enum SubscriptionPlan: string
{
    case Free     = 'free';
    case Pro      = 'pro';
    case Business = 'business';

    public function label(): string
    {
        return match($this) {
            self::Free     => 'مجاني',
            self::Pro      => 'Pro',
            self::Business => 'Business',
        };
    }

    public function maxProjects(): int
    {
        return match($this) {
            self::Free     => 3,
            self::Pro      => PHP_INT_MAX,
            self::Business => PHP_INT_MAX,
        };
    }

    public function maxTransactionsPerMonth(): int
    {
        return match($this) {
            self::Free     => 50,
            self::Pro      => 1000,
            self::Business => PHP_INT_MAX,
        };
    }

    public function maxClients(): int
    {
        return match($this) {
            self::Free     => 5,
            self::Pro      => PHP_INT_MAX,
            self::Business => PHP_INT_MAX,
        };
    }

    public function maxInvoicesPerMonth(): int
    {
        return match($this) {
            self::Free     => 5,
            self::Pro      => PHP_INT_MAX,
            self::Business => PHP_INT_MAX,
        };
    }

    public function maxQuotesPerMonth(): int
    {
        return match($this) {
            self::Free     => 3,
            self::Pro      => PHP_INT_MAX,
            self::Business => PHP_INT_MAX,
        };
    }

    public function maxTeamMembers(): int
    {
        return match($this) {
            self::Free     => 0,
            self::Pro      => 1,
            self::Business => 9,
        };
    }

    public function maxStorageMB(): int
    {
        return match($this) {
            self::Free     => 500,
            self::Pro      => 10240,
            self::Business => 102400,
        };
    }

    public function canExport(): bool
    {
        return $this !== self::Free;
    }

    public function canAccessApi(): bool
    {
        return $this === self::Business;
    }

    public function hasAdvancedReports(): bool
    {
        return $this !== self::Free;
    }

    public function can(string $gate): bool
    {
        return match($gate) {
            'export_data'              => $this !== self::Free,
            'advanced_reports'         => $this !== self::Free,
            'send_invoice_email'       => $this !== self::Free,
            'wallets'                  => $this !== self::Free,
            'multi_currency'           => $this !== self::Free,
            'client_portal'            => $this !== self::Free,
            'advanced_crm'             => $this !== self::Free,
            'import_excel'             => $this !== self::Free,
            'recurring_transactions'   => $this !== self::Free,
            'custom_invoice_templates' => $this !== self::Free,
            'recurring_invoices'       => $this !== self::Free,
            'zatca_compliance'         => $this !== self::Free,
            'payment_gateways'         => $this !== self::Free,
            'time_tracking'            => $this !== self::Free,
            'project_profitability'    => $this !== self::Free,
            'two_factor_auth'          => $this !== self::Free,
            'cash_flow_forecast'       => $this !== self::Free,
            'white_label'              => $this === self::Business,
            'team_projects'            => $this === self::Business,
            'milestones'               => $this === self::Business,
            'bulk_operations'          => $this === self::Business,
            'custom_permissions'       => $this === self::Business,
            'activity_log'             => $this === self::Business,
            'api_access'               => $this === self::Business,
            'webhooks'                 => $this === self::Business,
            'automation_rules'         => $this === self::Business,
            'whatsapp_automation'      => $this === self::Business,
            'team_reports'             => $this === self::Business,
            'custom_client_fields'     => $this === self::Business,
            default                    => false,
        };
    }
}
