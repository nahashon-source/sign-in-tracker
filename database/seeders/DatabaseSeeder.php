<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\InteractiveSignIn;
use Illuminate\Support\Str;
use Carbon\Carbon;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 307; $i++) {
            $userId = Str::uuid()->toString();

            User::create([
                'id' => $userId,
                'userPrincipalName' => "user{$i}@example.com",
                'displayName' => "User {$i}",
                'surname' => "Surname{$i}",
                'mail' => "user{$i}@example.com",
                'givenName' => "Given{$i}",
                'userType' => 'Member',
                'jobTitle' => 'Employee',
                'department' => 'IT',
                'accountEnabled' => true,
                'usageLocation' => 'US',
                'createdDateTime' => Carbon::now()->subDays(rand(1, 100)),
            ]);

            if ($i <= 200) {
                for ($j = 1; $j <= rand(1, 5); $j++) {
                    InteractiveSignIn::create([
                        'user_id' => $userId,
                        'date_utc' => Carbon::now()->subDays(rand(1, 30))->subHours(rand(1, 24)),
                        'status' => 'Success',
                        'ip_address' => "192.168.1." . rand(1, 255),
                        'user' => "User {$i}",
                        'username' => "user{$i}@example.com",
                        'application' => 'Web App',
                        'browser' => 'Chrome',
                        'operating_system' => 'Windows',
                    ]);
                }
            }
        }
    }
}
