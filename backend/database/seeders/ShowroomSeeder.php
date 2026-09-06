<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ShowroomSeeder extends Seeder
{
    public function run(): void
    {
        // حساب المعرض
        User::updateOrCreate(
            ['email' => 'showroom@autogallery.local'],
            ['name' => 'أوتو غاليري', 'password' => 'gallery1234'],
        );

        // إعدادات المعرض
        $settings = [
            'showroom_name' => 'أوتو غاليري',
            'phone' => '0599 000 000',
            'whatsapp' => '970599000000',
            'address' => 'شارع المعارض، المدينة',
            'hours' => 'السبت–الخميس · 9ص – 9م',
            'about' => 'معرض سيارات يقدّم تشكيلة مختارة من السيارات المفحوصة بأسعار تنافسية وضمان المعرض.',
        ];
        foreach ($settings as $k => $v) Setting::put($k, $v);

        // سيارات تجريبية
        $cars = [
            ['make' => 'مرسيدس', 'model' => 'GLC 300', 'year' => 2022, 'mileage' => 45000, 'price' => 42500, 'featured' => true,  'imgs' => ['photo-1618843479313-40f8afb4b4d8', 'photo-1616422285623-13ff0162193c', 'photo-1605559424843-9e4c228bf1c2']],
            ['make' => 'BMW', 'model' => 'X5', 'year' => 2021, 'mileage' => 62000, 'price' => 51000, 'featured' => false, 'imgs' => ['photo-1555215695-3004980ad54e', 'photo-1556189250-72ba954cfc2b']],
            ['make' => 'تويوتا', 'model' => 'لاندكروزر', 'year' => 2023, 'mileage' => 20000, 'price' => 68000, 'featured' => true,  'imgs' => ['photo-1533473359331-0135ef1b58bf', 'photo-1519641471654-76ce0107ad1b']],
            ['make' => 'هيونداي', 'model' => 'سوناتا', 'year' => 2022, 'mileage' => 38000, 'price' => 24900, 'featured' => false, 'imgs' => ['photo-1549927681-0b673b8243ab', 'photo-1541899481282-d53bffe3c35d']],
            ['make' => 'لكزس', 'model' => 'ES 350', 'year' => 2021, 'mileage' => 51000, 'price' => 39000, 'featured' => false, 'imgs' => ['photo-1503376780353-7e6692767b70', 'photo-1494976388531-d1058494cdd8']],
            ['make' => 'أودي', 'model' => 'Q7', 'year' => 2022, 'mileage' => 33000, 'price' => 58000, 'featured' => false, 'imgs' => ['photo-1606664515524-ed2f786a0bd6', 'photo-1552519507-da3b142c6e3d']],
        ];

        foreach ($cars as $c) {
            $v = Vehicle::updateOrCreate(
                ['make' => $c['make'], 'model' => $c['model'], 'year' => $c['year']],
                [
                    'mileage' => $c['mileage'], 'fuel' => 'petrol', 'transmission' => 'automatic',
                    'color' => 'أسود', 'drivetrain' => 'دفع رباعي', 'price' => $c['price'],
                    'description' => 'سيارة بحالة ممتازة، فحص كامل، صيانة وكالة.',
                    'features' => ['فتحة سقف', 'مقاعد جلد', 'كاميرا خلفية', 'شاشة لمس', 'حساسات', 'تحكم مثبت سرعة'],
                    'status' => 'available', 'is_featured' => $c['featured'],
                ],
            );

            if ($v->media()->exists()) continue;
            $i = 0;
            foreach ($c['imgs'] as $id) {
                $data = @file_get_contents("https://images.unsplash.com/{$id}?w=1100&q=80&auto=format&fit=crop");
                if (!$data) continue;
                $path = "uploads/demo/{$v->id}_{$i}.jpg";
                Storage::disk('public')->put($path, $data);
                VehicleMedia::create(['vehicle_id' => $v->id, 'file_path' => $path, 'is_cover' => $i === 0, 'sort_order' => $i]);
                $i++;
            }
            $this->command->info("  → {$v->make} {$v->model}: {$i} imgs");
        }
    }
}
