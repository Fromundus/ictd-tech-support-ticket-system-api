<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\DeviceHistory;
use App\Models\Payroll\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Desktop',
            'Laptop',
            'Printer',
            'Scanner',
            'Monitor',
            'UPS',
            'Speaker',
            'Keyboard',
            'Mouse',
            'Other',
        ];

        $brands = ['Dell', 'HP', 'Lenovo', 'Asus', 'Acer', 'Epson', 'Canon', 'Logitech'];
        $statuses = ['Active', 'Inactive', 'Under Repair'];
        $locations = ['Office A', 'Office B', 'IT Room', 'Warehouse'];

        $employees = Employee::inRandomOrder()->get();

        if ($employees->isEmpty()) {
            $this->command->warn('No employees found. Seed employees first.');
            return;
        }

        // Create random number of devices (20–50)
        // $deviceCount = rand(20, 50);
        $deviceCount = 100;

        for ($i = 0; $i < $deviceCount; $i++) {

            $employee = $employees->random();
            $type = $types[array_rand($types)];
            $brand = $brands[array_rand($brands)];

            $deviceName = strtoupper($type) . '-' . Str::random(5);

            $employeeFullName = $employee->lastname .', ' . $employee->firstname . ' ' . $employee->middlename ?? null . ' ' . $employee->suffix ?? null;

            $deviceData = [
                'mr_employeeid' => $employee->employeeid,
                'mr_employee_name' => $employeeFullName,
                'user_employeeid' => $employee->employeeid,
                'user_employee_name' => $employeeFullName,
                'name' => $deviceName,
                'type' => $type,
                'brand' => $brand,
                'model' => $brand . ' ' . rand(100, 999),
                'serial_number' => strtoupper(Str::random(10)),
                'status' => $statuses[array_rand($statuses)],
                'location' => $locations[array_rand($locations)],
                'description' => "Company issued {$type}",
                'created_by_employeeid' => $employee->employeeid,
            ];

            // Add specs only if Computer
            if ($type === 'Computer') {
                $deviceData = array_merge($deviceData, [
                    'processor' => collect(['i3', 'i5', 'i7', 'Ryzen 3', 'Ryzen 5'])->random(),
                    'ram' => collect(['4GB', '8GB', '16GB'])->random(),
                    'system_type' => collect(['64-bit', '32-bit'])->random(),
                    'operating_system' => collect(['Windows 10', 'Windows 11', 'Ubuntu'])->random(),
                    'storage' => collect(['256GB SSD', '512GB SSD', '1TB HDD'])->random(),
                    'mac_address' => strtoupper(Str::random(12)),
                ]);
            }

            $device = Device::create($deviceData);

            // Create 1–3 histories per device
            $historyCount = rand(1, 3);

            for ($j = 0; $j < $historyCount; $j++) {

                $performer = $employees->random();

                $performerFullName = $performer->lastname .', ' . $performer->firstname . ' ' . $performer->middlename ?? null . ' ' . $performer->suffix ?? null;

                DeviceHistory::create([
                    'device_id' => $device->id,
                    'date_requested' => now()->subDays(rand(10, 100)),
                    'issue' => collect([
                        'Not turning on',
                        'Slow performance',
                        'Paper jam',
                        'No display',
                        'Strange noise'
                    ])->random(),
                    'action' => collect([
                        'Replaced part',
                        'Cleaned hardware',
                        'Reinstalled OS',
                        'Updated drivers',
                        'Checked connections'
                    ])->random(),
                    'date_performed' => now()->subDays(rand(1, 9)),
                    'performed_by_employeeid' => $performer->employeeid,
                    'performed_by_name' => $performerFullName,
                    'remarks' => 'Issue resolved successfully',
                ]);
            }
        }
    }
}
