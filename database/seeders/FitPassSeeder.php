<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sample data so every owner screen has something to show.
 * Owner login: owner@fitpass.test / password
 *
 * Re-run with:  php artisan migrate:fresh --seed
 */
class FitPassSeeder extends Seeder
{
    private const MEMBER_COUNT = 150;

    private const PRICES = ['Regular' => 700, 'Student' => 600, 'Senior' => 500];

    public function run(): void
    {
        mt_srand(2026); // same data every run

        $owner = User::create([
            'name' => 'Super Admin',
            'email' => 'owner@fitpass.test',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
            'last_login_at' => now(),
        ]);

        $staff = $this->seedStaff();
        $members = $this->seedMembers();
        $this->seedPayments($members);
        $this->seedCheckIns($members, $staff);
        $this->seedAuditLogs($owner);
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function seedStaff()
    {
        // name, role, shift start, shift status, active
        $rows = [
            ['Jaspher Sibayan', 'admin', '06:00', 'on_shift', true],
            ['Ahrone Magtibay', 'admin', '06:00', 'on_shift', true],
            ['Emman Jalal', 'trainer', '06:00', 'on_shift', true],
            ['Fabs Dela Cruz', 'front_desk', '06:00', 'on_shift', true],
            ['Dwayne Tore', 'admin', '06:00', 'on_shift', true],
            ['Emman Pico', 'front_desk', '06:00', 'off_duty', true],
            ['Liza Mendoza', 'front_desk', '14:00', 'on_shift', true],
            ['Carlo Santos', 'trainer', '14:00', 'on_shift', true],
            ['Ana Villanueva', 'trainer', '14:00', 'on_shift', true],
            ['Rico Bautista', 'front_desk', '14:00', 'on_shift', true],
            ['Maria Cruz', 'trainer', '18:00', 'off_duty', true],
            ['John Lim', 'front_desk', '18:00', 'off_duty', true],
            ['Paolo Garcia', 'trainer', '06:00', 'on_leave', true],
            ['Grace Ramos', 'front_desk', '14:00', 'on_leave', true],
            ['Mark Reyes', 'front_desk', '06:00', 'off_duty', false],
        ];

        // Rico and Grace joined this month ("2 added this month").
        $recent = ['Rico Bautista', 'Grace Ramos'];

        return collect($rows)->map(function ($row) use ($recent) {
            [$name, $role, $shift, $status, $active] = $row;

            $user = User::create([
                'name' => $name,
                'email' => Str::slug($name, '.').'@fitpass.test',
                'password' => 'password',
                'role' => $role,
                'shift_start' => $shift,
                'shift_status' => $status,
                'is_active' => $active,
                'last_login_at' => $active ? now()->subMinutes(mt_rand(20, 600)) : now()->subDays(12),
            ]);

            $user->forceFill([
                'created_at' => in_array($name, $recent, true) ? now()->subDays(mt_rand(1, 5)) : now()->subMonths(mt_rand(2, 14)),
            ])->saveQuietly();

            return $user;
        });
    }

    /** @return \Illuminate\Support\Collection<int, Member> */
    private function seedMembers()
    {
        $first = ['Jaspher', 'Ahrone', 'Emman', 'Fabs', 'Dwayne', 'Liza', 'Carlo', 'Ana', 'Rico', 'Maria', 'John', 'Paolo',
            'Grace', 'Mark', 'Angela', 'Miguel', 'Bianca', 'Rafael', 'Camille', 'Nathan', 'Sofia', 'Gabriel', 'Isabel', 'Joshua'];
        $last = ['Sibayan', 'Magtibay', 'Jalal', 'Dela Cruz', 'Tore', 'Mendoza', 'Santos', 'Villanueva', 'Bautista', 'Cruz',
            'Lim', 'Garcia', 'Ramos', 'Reyes', 'Torres', 'Navarro', 'Aquino', 'Castillo', 'Flores', 'Domingo'];

        $members = collect();

        for ($i = 0; $i < self::MEMBER_COUNT; $i++) {
            // 60% Regular, 28% Student, 12% Senior
            $roll = mt_rand(1, 100);
            $type = $roll <= 60 ? 'Regular' : ($roll <= 88 ? 'Student' : 'Senior');
            $plan = mt_rand(1, 100) <= 70 ? 'Monthly Unlimited' : 'Quarterly';

            // 78% active, 10% expiring within a week, 12% expired
            $state = mt_rand(1, 100);
            $expires = $state <= 78
                ? today()->addDays(mt_rand(8, $plan === 'Quarterly' ? 85 : 30))
                : ($state <= 88 ? today()->addDays(mt_rand(0, 7)) : today()->subDays(mt_rand(1, 60)));

            $span = $plan === 'Quarterly' ? 90 : 30;
            $joinedDaysAgo = min(mt_rand($span, 180), 180);
            $joined = today()->subDays($joinedDaysAgo);
            if ($joined->gte($expires)) {
                $joined = $expires->copy()->subDays($span);
            }

            $name = $first[$i % count($first)].' '.$last[intdiv($i, count($first)) % count($last)];

            $members->push(Member::create([
                'member_code' => 'FTS-'.str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                'name' => $name,
                'email' => Str::slug($name, '.').($i + 1).'@example.com',
                'type' => $type,
                'plan' => $plan,
                'joined_at' => $joined->toDateString(),
                'expires_at' => $expires->toDateString(),
            ]));
        }

        return $members;
    }

    private function seedPayments($members): void
    {
        $rows = [];
        $stamp = now()->toDateTimeString();

        foreach ($members as $member) {
            $monthly = self::PRICES[$member->type];
            $amount = $member->plan === 'Quarterly' ? (int) round($monthly * 3 * 0.9) : $monthly;
            $step = $member->plan === 'Quarterly' ? 90 : 30;

            // One payment per billing cycle from joining until today.
            for ($date = $member->joined_at->copy(); $date->lte(today()); $date->addDays($step)) {
                $rows[] = [
                    'member_id' => $member->id,
                    'payer_name' => $member->name,
                    'kind' => 'monthly',
                    'amount' => $amount,
                    'method' => mt_rand(1, 100) <= 70 ? 'gcash' : 'cash',
                    'paid_at' => $date->copy()->setTime(mt_rand(6, 20), mt_rand(0, 59))->toDateTimeString(),
                    'created_at' => $stamp,
                    'updated_at' => $stamp,
                ];
            }
        }

        // Walk-in day passes: 80 pesos, a few per day for the last 40 days.
        $walkIns = ['Juan Tamad', 'Michael Jackson', 'Chris Bumstead', 'David Laid', 'Liam Agustin', 'Kyle Ocampo'];
        for ($d = 0; $d < 40; $d++) {
            for ($n = mt_rand(2, 6); $n > 0; $n--) {
                $at = today()->subDays($d)->setTime(mt_rand(6, 20), mt_rand(0, 59));
                if ($at->gt(now())) {
                    continue;
                }

                $rows[] = [
                    'member_id' => null,
                    'payer_name' => $walkIns[array_rand($walkIns)],
                    'kind' => 'walk_in',
                    'amount' => 80,
                    'method' => mt_rand(1, 100) <= 40 ? 'gcash' : 'cash',
                    'paid_at' => $at->toDateTimeString(),
                    'created_at' => $stamp,
                    'updated_at' => $stamp,
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            Payment::insert($chunk);
        }
    }

    private function seedCheckIns($members, $staff): void
    {
        // Busiest early morning and after work, quiet midday.
        $hourWeights = [5 => 2, 6 => 10, 7 => 10, 8 => 6, 9 => 3, 10 => 2, 11 => 2, 12 => 2, 13 => 2, 14 => 2,
            15 => 3, 16 => 5, 17 => 9, 18 => 10, 19 => 9, 20 => 6, 21 => 3, 22 => 1];
        $pool = [];
        foreach ($hourWeights as $hour => $weight) {
            $pool = array_merge($pool, array_fill(0, $weight, $hour));
        }

        $deskStaff = $staff->where('is_active', true)->pluck('id')->all();
        $now = now();
        $stamp = $now->toDateTimeString();

        for ($d = 59; $d >= 0; $d--) {
            $day = today()->subDays($d);
            $eligible = $members->filter(fn ($m) => $m->joined_at->lte($day))->values();

            if ($eligible->isEmpty()) {
                continue;
            }

            $perDay = $day->isWeekend() ? mt_rand(25, 40) : mt_rand(40, 70);
            $rows = [];

            for ($n = 0; $n < $perDay; $n++) {
                $timeIn = $day->copy()->setTime($pool[array_rand($pool)], mt_rand(0, 59), mt_rand(0, 59));
                if ($timeIn->gt($now)) {
                    continue;
                }

                $manual = mt_rand(1, 100) <= 18;
                $timeOut = $timeIn->copy()->addMinutes(mt_rand(40, 120));

                $rows[] = [
                    'member_id' => $eligible->random()->id,
                    'time_in' => $timeIn->toDateTimeString(),
                    // Anyone whose session hasn't ended yet is still in the gym.
                    'time_out' => $timeOut->gt($now) ? null : $timeOut->toDateTimeString(),
                    'method' => $manual ? 'manual' : 'qr',
                    'recorded_by' => $manual ? $deskStaff[array_rand($deskStaff)] : null,
                    'created_at' => $stamp,
                    'updated_at' => $stamp,
                ];
            }

            CheckIn::insert($rows);
        }
    }

    private function seedAuditLogs(User $owner): void
    {
        // minutes ago => message
        $entries = [
            [25, 'Jaspher Sibayan updated the Student plan price to 600.'],
            [95, 'Ahrone Magtibay logged in as Admin'],
            [210, 'System completed the nightly database backup'],
            [380, 'Fabs Dela Cruz manually checked in Rico Bautista'],
            [1500, 'Dwayne Tore deactivated staff account Mark Reyes'],
            [1800, 'Emman Pico added new member Kyle Ocampo'],
        ];

        foreach (array_reverse($entries) as [$minutes, $message]) {
            $log = AuditLog::record($message);
            $log->forceFill(['created_at' => now()->subMinutes($minutes), 'updated_at' => now()->subMinutes($minutes)])->saveQuietly();
        }
    }
}
