<?php
// database/seeders/AdminSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Super admin user (you)
        DB::table('users')->insertOrIgnore([
            'name'       => 'Akif',
            'email'      => 'akif@bluearrow.ae',
            'password'   => Hash::make('change_me_on_first_login'),
            'role'       => 'super_admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Demo tenant so you can see the client portal immediately
        $tenantId = DB::table('tenants')->insertGetId([
            'name'          => 'Blue Arrow RE',
            'slug'          => 'ba',              // bluearrow.ae/ba
            'primary_color' => '#1a56db',
            'contact_email' => 'akif@bluearrow.ae',
            'is_active'     => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $this->command->info('Admin user created: akif@bluearrow.ae');
        $this->command->info('Demo tenant: bluearrow.ae/ba');
        $this->command->warn('Change your password immediately after first login!');
    }
}
