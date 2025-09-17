<?php

// El namespace debe ser el correcto para que Laravel encuentre el archivo.
namespace App\Http\Controllers;

// Importaciones (use statements) para todas las clases que vamos a usar.
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

// Definición de la clase. Todo el código debe ir DENTRO de estas llaves.
class RegisteredUserController extends Controller
{
    /**
     * Muestra la vista de registro.
     * Es bueno tener este método también.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Maneja una solicitud de registro entrante.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // --- 1. Validación ---
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // --- 2. Transacción de Base de Datos (¡CRÍTICO!) ---
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
                'role' => 'admin', // El primer usuario es el admin de su compañía
            ]);

            // 2c. Asignar el Plan Gratuito
            $freePlan = Plan::where('name', 'Gratuito')->firstOrFail();

            Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $freePlan->id,
                'status' => 'active',
                'starts_at' => now(),
            ]);
            
            return $newUser;
        });

        // --- 3. Autenticar al Usuario ---
        Auth::login($user);

        // --- 4. Evento de Registro ---
        event(new Registered($user));

        // --- 5. Redirección ---
        return redirect(route('dashboard'));
    }

}