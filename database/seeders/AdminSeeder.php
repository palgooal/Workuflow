<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Enums\SubscriptionPlan;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء دور super_admin
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // إنشاء أول مستخدم Admin (أو تحديثه إذا كان موجوداً)
        $admin = User::firstOrCreate(
            ['email' => 'admin@workuflow.com'],
            [
                'name'              => 'Admin',
                'password'          => bcrypt('Admin@123'),
                'currency'          => 'SAR',
                'timezone'          => 'Asia/Riyadh',
                'subscription_plan' => SubscriptionPlan::Business,
            ]
        );

        $admin->assignRole($role);

        $this->command->info("✅ Admin user created: admin@workuflow.com / Admin@123");
        $this->command->warn("⚠️  Change the admin password after first login!");
    }
}
