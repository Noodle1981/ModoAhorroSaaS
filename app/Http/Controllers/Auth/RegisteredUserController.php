<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Province;
use App\Models\Locality;
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
        $provinces = Province::orderBy('name')->get();
        // Eager load provinces to avoid N+1 issues in the view if we were to display province name
        $localities = Locality::with('province')->orderBy('name')->get();
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
            'tax_id' => ['required', 'string', 'max:255', 'unique:companies,tax_id'],
            'province_id' => ['required', 'exists:provinces,id'],
            'locality_id' => ['required', 'exists:localities,id'],
            'is_particular' => ['nullable', 'boolean'],
            'company_name' => ['required_if:is_particular,null', 'nullable', 'string', 'max:255'],
            'terms' => ['required', 'accepted'],
        ]);

        $isParticular = $request->boolean('is_particular');
        $companyName = $isParticular ? $request->tax_id : $request->company_name;

        $company = Company::create([
            'name' => $companyName,
            'tax_id' => $request->tax_id,
            'province_id' => $request->province_id,
            'locality_id' => $request->locality_id,
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => $company->id,
        ]);

        // Find or create the free plan
        $freePlan = Plan::firstOrCreate(
            ['name' => 'Gratis'],
            [
                'price' => 0,
                'max_entities' => 5,
                'max_users_per_entity' => 1,
                'features' => ['basic_reports', 'limited_support'],
            ]
        );

        // Create the subscription for the company
        Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $freePlan->id,
            'starts_at' => now(),
            'status' => 'active',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
