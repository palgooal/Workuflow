<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name'    => 'Brand Identity Design',
                'name_ar' => 'تصميم هوية براند',
                'icon'    => 'sparkles',
                'color'   => '#8b5cf6',
            ],
            [
                'name'    => 'Logo Design',
                'name_ar' => 'تصميم شعار',
                'icon'    => 'star',
                'color'   => '#f59e0b',
            ],
            [
                'name'    => 'Brand Strategy',
                'name_ar' => 'تصميم براند',
                'icon'    => 'light-bulb',
                'color'   => '#6366f1',
            ],
            [
                'name'    => 'SEO',
                'name_ar' => 'سيو - تحسين محركات البحث',
                'icon'    => 'magnifying-glass',
                'color'   => '#10b981',
            ],
            [
                'name'    => 'Digital Marketing',
                'name_ar' => 'تسويق رقمي',
                'icon'    => 'megaphone',
                'color'   => '#3b82f6',
            ],
            [
                'name'    => 'Motion Graphics',
                'name_ar' => 'موشن جرافيك',
                'icon'    => 'film',
                'color'   => '#ec4899',
            ],
            [
                'name'    => 'Social Media Management',
                'name_ar' => 'إدارة السوشيال ميديا',
                'icon'    => 'share',
                'color'   => '#0ea5e9',
            ],
            [
                'name'    => 'Web Design',
                'name_ar' => 'تصميم مواقع',
                'icon'    => 'computer-desktop',
                'color'   => '#14b8a6',
            ],
            [
                'name'    => 'UI/UX Design',
                'name_ar' => 'تصميم واجهات',
                'icon'    => 'device-phone-mobile',
                'color'   => '#f97316',
            ],
            [
                'name'    => 'Photography',
                'name_ar' => 'تصوير',
                'icon'    => 'camera',
                'color'   => '#84cc16',
            ],
            [
                'name'    => 'Video Production',
                'name_ar' => 'إنتاج فيديو',
                'icon'    => 'video-camera',
                'color'   => '#ef4444',
            ],
            [
                'name'    => 'Copywriting',
                'name_ar' => 'كتابة محتوى',
                'icon'    => 'pencil',
                'color'   => '#a855f7',
            ],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(
                ['name' => $service['name'], 'is_global' => true],
                array_merge($service, [
                    'user_id'   => null,
                    'is_global' => true,
                    'is_active' => true,
                ])
            );
        }
    }
}
