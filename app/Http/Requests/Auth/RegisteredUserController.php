<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Province; // Importa el modelo Province
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Muestra la vista de registro.
     */
    public function create(): View
    {
        // 1. Obtenemos todas las provincias de la base de datos.
        $provinces = Province::orderBy('name')->get();

        // 2. Pasamos la colección de provincias a la vista.
        return view('auth.register', compact('provinces'));
    }

    /**
     * Maneja una solicitud de registro entrante.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // 1. Validación (incluyendo los nuevos campos si los quieres guardar aquí)
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'province_id' => ['required', 'exists:provinces,id'],
            // 'locality_id' => ['required', 'exists:localities,id'], // Si añades localidades
        ]);

        // 2. Transacción de Base de Datos
        $user = DB::transaction(function () use ($request) {
            
            // 2a. Crear la Compañía
            $company = Company::create([
                'name' => $request->name . ' (Empresa)',
                'tax_id' => '00-00000000-0', // Genérico, se puede actualizar después
            ]);

            // 2b. Crear el Usuario y Asociarlo a la Compañía
            $newUser = User::create([
                'company_id' => $company->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'admin',
            ]);

            // 2c. Asignar el Plan Gratuito
            $freePlan = Plan::where('name', 'Gratuito')->firstOrFail();

            Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $freePlan->id,
                'status' => 'active',
                'starts_at' => now(),
            ]);
            
            // Aquí podrías guardar la provincia/localidad en el perfil del usuario
            // o en la compañía si has añadido esas columnas a las tablas.
            // Ejemplo:
            // $company->update(['province_id' => $request->province_id]);
            
            return $newUser;
        });

        // 3. Autenticar al Usuario
        Auth::login($user);

        // 4. Evento de Registro
        event(new Registered($user));

        // 5. Redirección
        return redirect(route('dashboard'));
    }
}