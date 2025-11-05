<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        // Implementar actualización de perfil
        return redirect()->route('profile.edit');
    }

    public function destroy(Request $request)
    {
        // Implementar eliminación de cuenta
        return redirect()->route('welcome');
    }
}
