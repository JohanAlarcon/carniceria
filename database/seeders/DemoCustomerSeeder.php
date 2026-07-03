<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'cliente@demo.test'],
            [
                'name' => 'Juan Pérez',
                'password' => Hash::make('password'),
                'is_staff' => false,
                'phone' => '(212) 555-0142',
                'email_verified_at' => now(),
            ]
        );

        Customer::updateOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => 'Taquería El Demo',
                'contact_name' => 'Juan Pérez',
                'phone' => '(212) 555-0142',
                'address_line1' => '123 Roosevelt Ave',
                'address_line2' => null,
                'city' => 'Queens',
                'state' => 'NY',
                'zip' => '11372',
                'price_adjustment_pct' => -5.00,
                'is_approved' => true,
                'tax_exempt' => true,
                'resale_certificate' => 'NY-RESALE-DEMO',
                'notes' => 'Cliente de demostración (aprobado, -5%).',
            ]
        );
    }
}
