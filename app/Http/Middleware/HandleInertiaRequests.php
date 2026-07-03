<?php

namespace App\Http\Middleware;

use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $customer = $user?->customer;
        $settings = BusinessSetting::current();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_staff' => (bool) $user->is_staff,
                ] : null,
                'customer' => $customer ? [
                    'business_name' => $customer->business_name,
                    'contact_name' => $customer->contact_name,
                    'phone' => $customer->phone,
                    'is_approved' => (bool) $customer->is_approved,
                    'price_adjustment_pct' => (float) $customer->price_adjustment_pct,
                    'address_line1' => $customer->address_line1,
                    'address_line2' => $customer->address_line2,
                    'city' => $customer->city,
                    'state' => $customer->state,
                    'zip' => $customer->zip,
                ] : null,
            ],
            'business' => [
                'name' => $settings->business_name,
                'currency' => $settings->currency,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
