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

        // Usamos una transacción para asegurar que ambas operaciones (crear usuario y suscripción) 
        // se completen exitosamente. Si algo falla, se revierte todo.
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company_id' => 1, // Assign default company_id
                'role' => 'user', // Assign default role
            ]);

            // --- LÓGICA DE SUSCRIPCIÓN AÑADIDA ---
            // Buscamos el plan gratuito por su nombre.
            $freePlan = Plan::where('name', 'Gratuito')->firstOrFail();

            // Creamos la suscripción para la compañía del usuario.
            // Usamos firstOrCreate para no crear una suscripción duplicada si ya existiera por alguna razón.
            Subscription::firstOrCreate(
                ['company_id' => $user->company_id],
                [
                    'plan_id' => $freePlan->id,
                    'starts_at' => now(),
                    'status' => 'active',
                ]
            );
            // --- FIN DE LA LÓGICA AÑADIDA ---

            event(new Registered($user));

            Auth::login($user);
        });

        return redirect(route('dashboard', absolute: false));
    }
}