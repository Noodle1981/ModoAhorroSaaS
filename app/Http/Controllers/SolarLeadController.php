<?php

namespace App\Http\Controllers;

use App\Models\SolarLead;
use App\Models\Entity;
use Illuminate\Http\Request;

class SolarLeadController extends Controller
{
    public function storePanel(Request $request)
    {
        $request->validate([
            'entity_id' => 'nullable|exists:entities,id',
            'contact_name' => 'nullable|string|max:120',
            'contact_email' => 'nullable|email|max:150',
            'contact_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        SolarLead::create([
            'company_id' => $user->company_id,
            'entity_id' => $request->input('entity_id'),
            'type' => 'panel',
            'contact_name' => $request->input('contact_name') ?: $user->name,
            'contact_email' => $request->input('contact_email') ?: $user->email,
            'contact_phone' => $request->input('contact_phone'),
            'notes' => $request->input('notes'),
        ]);

        return back()->with('success', 'Solicitud enviada. Te contactaremos pronto con un presupuesto estimado.');
    }

    public function storeHeater(Request $request)
    {
        $request->validate([
            'entity_id' => 'nullable|exists:entities,id',
            'contact_name' => 'nullable|string|max:120',
            'contact_email' => 'nullable|email|max:150',
            'contact_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        SolarLead::create([
            'company_id' => $user->company_id,
            'entity_id' => $request->input('entity_id'),
            'type' => 'heater',
            'contact_name' => $request->input('contact_name') ?: $user->name,
            'contact_email' => $request->input('contact_email') ?: $user->email,
            'contact_phone' => $request->input('contact_phone'),
            'notes' => $request->input('notes'),
        ]);

        return back()->with('success', 'Interés registrado. Te enviaremos información y estimaciones del calefón solar.');
    }
}
