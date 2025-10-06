<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Locality;
use App\Models\Plan;
use App\Models\Province;
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
     * Muestra la vista de registro, pasando los datos necesarios para los dropdowns.
     */
    public function create(): View
    {
        // Obtenemos los datos para los menús desplegables.
        $provinces = Province::orderBy('name')->get();
        $localities = Locality::orderBy('name')->get();

        // Pasamos ambas colecciones a la vista.
        return view('auth.register', compact('provinces', 'localities'));
    }

    /**
     * Maneja una solicitud de registro entrante.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // 1. Validación de todos los campos del formulario.
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'tax_id' => ['required', 'string', 'max:20', 'unique:'.Company::class],
            'company_name' => ['nullable', 'string', 'max:255'],
            'is_particular' => ['nullable', 'boolean'],
            'province_id' => ['required', 'exists:provinces,id'],
            'locality_id' => ['required', 'exists:localities,id'],
            'terms' => ['required', 'accepted'],
        ]);

        // 2. Transacción de Base de Datos para asegurar la integridad.
        $user = DB::transaction(function () use ($request) {
            
            // Determinamos el nombre de la compañía.
            $companyName = $request->input('is_particular') ? $request->name . ' (Empresa)' : $request->company_name;

            // 2a. Crear la Compañía con todos sus datos.
            $company = Company::create([
                'name' => $companyName,
                'tax_id' => $request->tax_id,
                'province_id' => $request->province_id,
                'locality_id' => $request->locality_id,
            ]);

            // 2b. Crear el Usuario y asociarlo a la Compañía.
            $newUser = User::create([
                'company_id' => $company->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                // Ya no necesitamos 'role' en nuestro modelo simplificado.
            ]);

            // 2c. Asignar el Plan Gratuito.
            $freePlan = Plan::where('name', 'Gratuito')->firstOrFail();

            Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $freePlan->id,
                'status' => 'active',
                'starts_at' => now(),
            ]);
            
            return $newUser;
        });

        // 3. Autenticar al Usuario.
        Auth::login($user);

        // 4. Disparar el evento de registro (para emails de bienvenida, etc.).
        event(new Registered($user));

        // 5. Redirección al Dashboard.
        return redirect(route('dashboard'));
    }
}