<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB; // قد تحتاج إلى هذا لو لم تكن تستخدم الـ Model

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = new Filesystem();
        // قراءة محتوى ملف الـ JSON المحلي
        $jsonString = $file->get(database_path('data/currencies.json'));
        $currencies = json_decode($jsonString, true);

        // التأكد من أن البيانات هي مصفوفة يمكن التكرار عليها
        if (!is_array($currencies)) {
            throw new \Exception("Failed to decode currencies JSON file.");
        }

        foreach ($currencies as $code => $details) {
            Currency::updateOrCreate(
                ['code' => $details['alphabeticCode']],
                [
                    'name' => $details['currency'], // اسم العملة
                    'symbol' => $details['alphabeticCode'], // استخدم الرمز الأبجدي كرمز مؤقت

                    // أضف هنا أي حقول أخرى في جدول الـ currencies الخاص بك
                ]
            );
        }
        echo "Currencies imported successfully using local JSON.\n";
    }
}
