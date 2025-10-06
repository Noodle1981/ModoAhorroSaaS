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
            'province_id' => ['nullable', 'exists:provinces,id'],
            'locality_id' => ['nullable', 'exists:localities,id'],
            'tax_id' => ['nullable', 'string', 'max:255', 'unique:companies,tax_id'],
        ]);

        // Conditional company creation based on user input
        if ($request->filled('tax_id')) {
            // User is registering a formal company
            $company = Company::create([
                'name' => $request->company_name ?? $request->name . '\'s Company', // Use a dedicated company name field if available
                'province_id' => $request->province_id,
                'locality_id' => $request->locality_id,
                'tax_id' => $request->tax_id,
            ]);
        } else {
            // User is registering as an individual, create a personal company
            $company = Company::create([
                'name' => $request->name . '\'s Personal Account',
                // tax_id, province_id, and locality_id will be null
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => $company->id, // Associate user with the new company
        ]);

        // Assign the free plan by default to the newly created company
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
