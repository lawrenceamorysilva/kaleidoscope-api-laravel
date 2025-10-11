<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortalDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('portal_settings')->updateOrInsert(
            ['key' => 'minimum_order_value'],
            [
                'value' => '60.00',
                'type'  => 'number',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('portal_settings')->updateOrInsert(
            ['key' => 'dropship_fee'],
            [
                'value' => '11.00',
                'type'  => 'number',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('portal_settings')->updateOrInsert(
            ['key' => 'minimum_freight_charge'],
            [
                'value' => '15.00',
                'type'  => 'number',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // Example of a string-based setting (future proof)
        DB::table('portal_settings')->updateOrInsert(
            ['key' => 'support_email'],
            [
                'value' => 'support@kaleidoscope.com.au',
                'type'  => 'string',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        /*// Example of a boolean-based setting (future proof)
        DB::table('portal_settings')->updateOrInsert(
            ['key' => 'enable_dropship_mode'],
            [
                'value' => '1',
                'type'  => 'boolean',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );*/
    }
}
