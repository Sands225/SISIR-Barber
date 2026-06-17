<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    /**
     * Handle phone-based login / registration.
     * POST /login
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'min:7', 'max:15'],
        ]);

        // Normalize: strip leading zeros, ensure E.164 sans +
        $raw   = preg_replace('/\D/', '', $request->phone);
        $phone = '62' . ltrim($raw, '0');

        // Find or create customer record
        $customer = Customer::firstOrCreate(
            ['phone' => $phone],
            [
                'name'              => 'Pelanggan ' . substr($phone, -4),
                'wa_id'             => $phone,
                'conversation_state' => 'idle',
            ]
        );

        // Store in session
        session([
            'customer_id'   => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone'=> $customer->phone,
        ]);

        return redirect()->route('sisir.dashboard');
    }

    /**
     * Logout / clear session.
     * POST /logout
     */
    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['customer_id', 'customer_name', 'customer_phone']);

        return redirect()->route('sisir.login');
    }
}
