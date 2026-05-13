<?php

namespace App\Console\Commands;

use App\Modules\Recurring\Services\RecurringService;
use Illuminate\Console\Command;

class ProcessRecurringTransactions extends Command
{
    protected $signature   = 'recurring:process';
    protected $description = 'معالجة الالتزامات المتكررة المستحقة اليوم وتحويلها إلى معاملات فعلية';

    public function __construct(private readonly RecurringService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('⚙️  بدء معالجة الالتزامات المتكررة...');

        $count = $this->service->processDueForAll();

        $this->info("✅  تم معالجة {$count} التزام متكرر.");

        return self::SUCCESS;
    }
}
