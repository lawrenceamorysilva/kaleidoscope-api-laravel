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
        AdminUser::create([
            'name' => 'LACS',
            'email' => 'lacs.php@gmail.com',
            'password' => Hash::make('lawrence'),
            'role' => 'super_admin',
        ]);

        AdminUser::create([
            'name' => 'James',
            'email' => 'james@kaleidoscope.com.au',
            'password' => Hash::make('James0913'),
            'role' => 'super_admin',
        ]);
    }
}
