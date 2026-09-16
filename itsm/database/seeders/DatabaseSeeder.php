<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\Category;
use App\Models\Priority;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'head.it@amarinshipmgmt.com'],
            [
                'name' => 'Head IT',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'department' => 'IT',
                'position' => 'Head of IT',
                'source' => 'employee',
                'is_active' => true,
            ]
        );

        // Create technician user
        User::firstOrCreate(
            ['email' => 'it.support@amarinshipmgmt.com'],
            [
                'name' => 'IT Support',
                'password' => Hash::make('tech123'),
                'role' => 'technician',
                'department' => 'IT',
                'position' => 'IT Support Staff',
                'source' => 'employee',
                'is_active' => true,
            ]
        );

        // Create priorities
        $priorities = [
            ['name' => 'Critical', 'slug' => 'critical', 'color' => '#DC2626', 'sla_hours' => 4, 'response_hours' => 1, 'sort_order' => 1],
            ['name' => 'High', 'slug' => 'high', 'color' => '#F97316', 'sla_hours' => 8, 'response_hours' => 2, 'sort_order' => 2],
            ['name' => 'Medium', 'slug' => 'medium', 'color' => '#F59E0B', 'sla_hours' => 24, 'response_hours' => 4, 'sort_order' => 3],
            ['name' => 'Low', 'slug' => 'low', 'color' => '#6B7280', 'sla_hours' => 72, 'response_hours' => 8, 'sort_order' => 4],
        ];
        foreach ($priorities as $p) {
            Priority::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // Create categories
        $categories = [
            ['name' => 'Hardware', 'slug' => 'hardware', 'icon' => 'fas fa-desktop', 'color' => '#3B82F6',
             'subs' => ['Desktop/PC', 'Laptop', 'Printer', 'Scanner', 'Monitor', 'Keyboard/Mouse', 'UPS', 'Lainnya']],
            ['name' => 'Software', 'slug' => 'software', 'icon' => 'fas fa-code', 'color' => '#10B981',
             'subs' => ['OS Windows', 'Microsoft Office', 'Email', 'Antivirus', 'ERP/Application', 'Browser', 'Lainnya']],
            ['name' => 'Network', 'slug' => 'network', 'icon' => 'fas fa-network-wired', 'color' => '#8B5CF6',
             'subs' => ['Internet', 'LAN/WiFi', 'VPN', 'Firewall', 'DNS', 'Lainnya']],
            ['name' => 'Account & Access', 'slug' => 'account-access', 'icon' => 'fas fa-key', 'color' => '#F59E0B',
             'subs' => ['New Account', 'Reset Password', 'Permission Change', 'Account Disable', 'Lainnya']],
            ['name' => 'Server & Cloud', 'slug' => 'server-cloud', 'icon' => 'fas fa-server', 'color' => '#EF4444',
             'subs' => ['Web Server', 'Database Server', 'File Server', 'Cloud Service', 'Backup', 'Lainnya']],
            ['name' => 'Telekomunikasi', 'slug' => 'telekomunikasi', 'icon' => 'fas fa-phone', 'color' => '#06B6D4',
             'subs' => ['Telepon', 'VSAT', 'Radio', 'Intercom', 'Lainnya']],
        ];

        foreach ($categories as $catData) {
            $subs = $catData['subs'];
            unset($catData['subs']);
            
            $category = Category::firstOrCreate(
                ['slug' => $catData['slug']],
                array_merge($catData, ['is_active' => true])
            );

            foreach ($subs as $i => $sub) {
                $subSlug = Str::slug($category->name . '-' . $sub);
                SubCategory::firstOrCreate(
                    ['slug' => $subSlug],
                    [
                        'category_id' => $category->id,
                        'name' => $sub,
                        'is_active' => true,
                        'sort_order' => $i,
                    ]
                );
            }
        }

        // Create asset categories
        $assetCategories = [
            ['name' => 'Laptop', 'slug' => 'laptop', 'icon' => 'laptop', 'description' => 'Laptop dan Notebook'],
            ['name' => 'Desktop', 'slug' => 'desktop', 'icon' => 'desktop', 'description' => 'PC Desktop'],
            ['name' => 'Printer', 'slug' => 'printer', 'icon' => 'print', 'description' => 'Printer dan Scanner'],
            ['name' => 'Network Device', 'slug' => 'network-device', 'icon' => 'network-wired', 'description' => 'Router, Switch, Access Point'],
            ['name' => 'Server', 'slug' => 'server', 'icon' => 'server', 'description' => 'Server Fisik dan Virtual'],
            ['name' => 'Monitor', 'slug' => 'monitor', 'icon' => 'tv', 'description' => 'Monitor dan Display'],
            ['name' => 'Telepon/Radio', 'slug' => 'telepon-radio', 'icon' => 'phone', 'description' => 'Telepon, Radio, dan alat komunikasi'],
            ['name' => 'Lainnya', 'slug' => 'lainnya', 'icon' => 'cube', 'description' => 'Perangkat lainnya'],
        ];
        foreach ($assetCategories as $ac) {
            AssetCategory::firstOrCreate(
                ['slug' => $ac['slug']],
                array_merge($ac, ['is_active' => true])
            );
        }
    }
}