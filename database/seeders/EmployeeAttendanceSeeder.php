<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\PayrollGroup;
use App\Models\Schedule;
use App\Models\Site;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EmployeeAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filippinoFirstNames = [
            'Juan', 'Jose', 'Maria', 'Ana', 'Ricardo', 'Liza', 'Teresa', 'Ferdinand', 'Corazon', 'Benigno',
            'Gloria', 'Rodrigo', 'Bongbong', 'Leni', 'Leila', 'Antonio', 'Francisco', 'Gregorio', 'Melchora', 'Andres'
        ];

        $filippinoLastNames = [
            'Dela Cruz', 'Santos', 'Reyes', 'Bautista', 'Garcia', 'Pascual', 'Mendoza', 'Torres', 'Tomas', 'Aquino',
            'Marcos', 'Duterte', 'Robredo', 'Trillanes', 'De Lima', 'Luna', 'del Pilar', 'Bonifacio', 'Silang', 'Mabini'
        ];

        $positions = [
            'Software Engineer', 'Senior Developer', 'Project Manager', 'QA Specialist', 'HR Officer',
            'Accountant', 'Data Analyst', 'UI/UX Designer', 'System Administrator', 'Marketing Specialist'
        ];

        // 1. Ensure a site and payroll group exist
        $site = Site::firstOrCreate(['name' => 'Main Office'], ['location' => 'Manila']);
        $group = PayrollGroup::firstOrCreate(['name' => 'Monthly Staff'], ['description' => 'Regular monthly employees']);

        // 2. Ensure a schedule exists for the group
        $groupSchedule = Schedule::updateOrCreate(
            ['payroll_group_id' => $group->id],
            [
                'name' => 'Standard Shift',
                'time_in' => '08:00:00',
                'time_out' => '17:00:00',
                'days' => ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"]
            ]
        );

        $payrollService = new PayrollService();
        $startDate = Carbon::create(2026, 4, 1);
        $endDate = Carbon::create(2026, 4, 30);

        for ($i = 0; $i < 20; $i++) {
            $firstName = $filippinoFirstNames[$i];
            $lastName = $filippinoLastNames[$i];
            $employeeId = 'EMP-' . str_pad($i + 10, 3, '0', STR_PAD_LEFT);

            // 3. Create Employee
            $employee = Employee::updateOrCreate(
                ['employee_id' => $employeeId],
                [
                    'site_id' => $site->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => strtolower($firstName . '.' . str_replace(' ', '', $lastName)) . '@example.com',
                    'position' => $positions[array_rand($positions)],
                    'daily_rate' => rand(600, 1500),
                    'status' => 'active',
                    'payroll_group_id' => $group->id,
                    'date_employed' => Carbon::now()->subMonths(rand(1, 24))->format('Y-m-d'),
                    'gender' => rand(0, 1) ? 'Male' : 'Female',
                    'civil_status' => 'Single',
                    'citizenship' => 'Filipino',
                ]
            );

            // 4. Create Attendance for April 2026 (weekdays only)
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                if ($currentDate->isWeekday()) {
                    // Random attendance logic
                    $rand = rand(1, 100);

                    if ($rand <= 5) {
                        // 5% chance of being absent (no record)
                        $currentDate->addDay();
                        continue;
                    }

                    // Base shift
                    $shiftStart = Carbon::parse('08:00:00');
                    $shiftEnd = Carbon::parse('17:00:00');

                    if ($rand <= 20) {
                        // 15% chance of being late
                        $lateMinutes = rand(5, 60);
                        $checkIn = $shiftStart->copy()->addMinutes($lateMinutes);
                        $checkOut = $shiftEnd;
                    } elseif ($rand <= 30) {
                        // 10% chance of undertime
                        $undertimeMinutes = rand(5, 60);
                        $checkIn = $shiftStart;
                        $checkOut = $shiftEnd->copy()->subMinutes($undertimeMinutes);
                    } elseif ($rand <= 40) {
                        // 10% chance of overtime
                        $overtimeMinutes = rand(30, 120);
                        $checkIn = $shiftStart;
                        $checkOut = $shiftEnd->copy()->addMinutes($overtimeMinutes);
                    } else {
                        // 60% chance of being on time
                        $checkIn = $shiftStart;
                        $checkOut = $shiftEnd;
                    }

                    $stats = $payrollService->calculateAttendanceStats(
                        $checkIn->format('H:i:s'),
                        $checkOut->format('H:i:s'),
                        $employee->id,
                        $currentDate->format('Y-m-d')
                    );

                    Attendance::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'date' => $currentDate->format('Y-m-d'),
                        ],
                        array_merge([
                            'time_in' => $checkIn->format('H:i:s'),
                            'time_out' => $checkOut->format('H:i:s'),
                        ], $stats)
                    );
                }
                $currentDate->addDay();
            }
        }
    }
}
