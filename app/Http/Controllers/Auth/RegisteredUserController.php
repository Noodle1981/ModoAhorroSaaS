<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
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
        ]);

        // Usamos una transacción para asegurar que todas las operaciones se completen exitosamente.
        // Si algo falla, se revierte todo automáticamente.
        DB::transaction(function () use ($request) {
            // 1. Crear una Company (cuenta) única para este usuario
            // Generamos un tax_id temporal usando el timestamp y un número aleatorio
            $company = \App\Models\Company::create([
                'name' => $request->name . "'s Account", // Ej: "Juan Pérez's Account"
                'tax_id' => 'TEMP-' . time() . '-' . rand(1000, 9999), // Tax ID temporal
            ]);

            // 2. Crear el usuario asociado a su company
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company_id' => $company->id, // Asignar la company recién creada
                'role' => 'user', // Rol por defecto
            ]);

            // 3. Buscar el plan gratuito
            $freePlan = Plan::where('name', 'Gratuito')->firstOrFail();

            // 4. Crear la suscripción gratuita para la company
            Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $freePlan->id,
                'starts_at' => now(),
                'status' => 'active',
            ]);

            event(new Registered($user));

            Auth::login($user);
        });

        return redirect(route('dashboard', absolute: false));
    }
}