<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Province;
use App\Models\Locality; // Import the Locality model
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $provinces = Province::all();
        $localities = Locality::all();
        return view('auth.register', compact('provinces', 'localities'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'province_id' => ['required', 'exists:provinces,id'],
            'locality_id' => ['required', 'exists:localities,id'],
            'tax_id' => ['required', 'string', 'max:255', 'unique:companies,tax_id'],
        ]);

        // Create a new company for the user
        $company = Company::create([
            'name' => $request->name . '\'s Company', // Default company name
            'province_id' => $request->province_id,
            'locality_id' => $request->locality_id,
            'tax_id' => $request->tax_id,
            // You might want to add default values for tax_id, address, phone here
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => $company->id, // Associate user with the new company
        ]);

        // Assign the free plan by default to the company
        $freePlan = Plan::where('name', 'Gratis')->first();
        if ($freePlan) {
            Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $freePlan->id,
                'starts_at' => now(),
                'status' => 'active',
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
