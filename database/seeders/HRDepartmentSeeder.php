<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pizza\HR_Department\Language;
use App\Models\Pizza\HR_Department\RequestType;
use App\Models\Pizza\HR_Department\InventoryType;
use App\Models\Pizza\HR_Department\Unit;
use App\Models\Pizza\HR_Department\FlexShift;

class HRDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding HR Department lookup tables...');

        // Seed Languages
        $this->seedLanguages();
        $this->command->line('✓ Languages seeded');

        // Seed Inventory Types
        $this->seedInventoryTypes();
        $this->command->line('✓ Inventory Types seeded');

        // Seed Units
        $this->seedUnits();
        $this->command->line('✓ Units seeded');

        // Seed Flex Shifts
        $this->seedFlexShifts();
        $this->command->line('✓ Flex Shifts seeded');

        // Seed Request Types (must be after Languages)
        $this->seedRequestTypes();
        $this->command->line('✓ Request Types seeded');

        $this->command->info('✓ All HR Department data seeded successfully!');
    }

    private function seedLanguages(): void
    {
        $languages = [
            ['name' => 'English'],
            ['name' => 'عربي'],
            ['name' => 'Español'],
        ];

        foreach ($languages as $language) {
            Language::create($language);
        }
    }

    private function seedRequestTypes(): void
    {
        $requestTypes = [
            ['name' => 'Store Operations & Responsibilities', 'language_id' => 1],
            ['name' => 'Payroll Requests', 'language_id' => 1],
            ['name' => 'Schedule Adjustment Requests', 'language_id' => 1],
            ['name' => 'Favorable health insurance application', 'language_id' => 1],
            ['name' => 'Feedback or Complaints', 'language_id' => 1],
            ['name' => 'Others', 'language_id' => 1],

            ['name' => 'مهام وأعمال المتجر', 'language_id' => 2],
            ['name' => 'مهام وأعمال المتجر', 'language_id' => 2],
            ['name' => 'طلبات تعديل الجدول', 'language_id' => 2],
            ['name' => 'طلب موفقه تأمين صحي', 'language_id' => 2],
            ['name' => 'الشكاوى أو الملاحظات', 'language_id' => 2],
            ['name' => 'استفسارات أخرى', 'language_id' => 2],

            ['name' => 'Responsabilidades y operaciones de la tienda', 'language_id' => 3],
            ['name' => 'Solicitudes de Nómina', 'language_id' => 3],
            ['name' => 'Solicitudes de ajuste de horario', 'language_id' => 3],
            ['name' => 'Solicitud de seguro de enfermedad', 'language_id' => 3],
            ['name' => 'Comentarios o quejas', 'language_id' => 3],
            ['name' => 'Otras consultas', 'language_id' => 3],
        ];

        foreach ($requestTypes as $type) {
            RequestType::create($type);
        }
    }

    private function seedInventoryTypes(): void
    {
        $inventoryTypes = [
            ['name' => 'daily inventory - جرد يومي'],
            ['name' => 'weekly inventory - جرد اسبوعي'],
            ['name' => 'Period - دوري'],

        ];

        foreach ($inventoryTypes as $type) {
            InventoryType::create($type);
        }
    }

    private function seedUnits(): void
    {
        $units = [
            ['name' => 'Case'],
            ['name' => 'Each'],
            ['name' => 'Each'],
            ['name' => 'Bale'],
            ['name' => 'LB'],
            ['name' => 'Bag'],
            ['name' => 'Order'],
            ['name' => 'pouch'],
            ['name' => 'PACK'],
            ['name' => 'Bottle'],
            ['name' => 'Gallon'],
            ['name' => 'Can'],
            ['name' => 'Jar'],
            ['name' => 'cart'],
            ['name' => 'Sleeve'],
            ['name' => 'Can'],
            ['name' => 'PKG'],
            ['name' => 'Gloves_M'],
            ['name' => 'Gloves_L'],
            ['name' => 'Gloves_XL'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }

    private function seedFlexShifts(): void
    {
        $shifts = [
            ['shift_name' => 'Shift #1'],
            ['shift_name' => 'Shift #2'],
            ['shift_name' => 'Shift #3'],
            ['shift_name' => 'Shift #4'],

        ];

        foreach ($shifts as $shift) {
            FlexShift::create($shift);
        }
    }
}
