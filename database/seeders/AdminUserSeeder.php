<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'James',
                'email' => 'james@kaleidoscope.com.au',
                'password' => 'James0913',
                'role' => 'super_admin',
            ],
            [
                'name' => 'LACS',
                'email' => 'lacs.php@gmail.com',
                'password' => 'lawrence',
                'role' => 'super_admin',
            ],
            [
                'name' => 'Tara',
                'email' => 'tara@kaleidoscope.com.au',
                'password' => 'Tara1003',
                'role' => 'admin',
            ],
            [
                'name' => 'Laurie',
                'email' => 'laurie@kaleidoscope.com.au',
                'password' => 'Laurie1008',
                'role' => 'admin',
            ],
            [
                'name' => 'Ann',
                'email' => 'ann@kaleidoscope.com.au',
                'password' => 'Ann1007',
                'role' => 'admin',
            ],
        ];

        foreach ($users as $user) {
            AdminUser::updateOrCreate(
                ['email' => $user['email']], // condition
                [
                    'name' => $user['name'],
                    'password' => Hash::make($user['password']),
                    'role' => $user['role'],
                ]
            );
        }
    }
}
