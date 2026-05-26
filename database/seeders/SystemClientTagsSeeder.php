<?php

namespace Database\Seeders;

use App\Modules\CRM\Enums\TagType;
use App\Modules\CRM\Models\ClientTag;
use Illuminate\Database\Seeder;

/**
 * SystemClientTagsSeeder — وسوم النظام الثابتة
 *
 * تُنشئ الوسوم من config/crm.php['system_tags'].
 * user_id = NULL  →  مشتركة بين جميع المستخدمين.
 * type    = system →  لا يمكن حذفها أو تعديل slug/type.
 *
 * آمنة للتشغيل المتعدد (Idempotent) — تستخدم updateOrCreate على أساس slug.
 */
class SystemClientTagsSeeder extends Seeder
{
    public function run(): void
    {
        $tags = config('crm.system_tags', []);

        if (empty($tags)) {
            $this->command->warn('⚠️  system_tags فارغة في config/crm.php — لم يُنشأ أي وسم.');
            return;
        }

        $created = 0;
        $updated = 0;

        foreach ($tags as $tagData) {
            $exists = ClientTag::where('slug', $tagData['slug'])
                               ->whereNull('user_id')
                               ->exists();

            ClientTag::updateOrCreate(
                [
                    'slug'    => $tagData['slug'],
                    'user_id' => null,
                ],
                [
                    'name'      => $tagData['name'],
                    'color'     => $tagData['color'],
                    'icon'      => $tagData['icon'] ?? null,
                    'type'      => TagType::System->value,
                    'is_active' => true,
                    'priority'  => $tagData['priority'],
                ]
            );

            $exists ? $updated++ : $created++;
        }

        $this->command->info(
            "✅ وسوم النظام: {$created} جديد | {$updated} محدَّث | المجموع: " . count($tags)
        );
    }
}
