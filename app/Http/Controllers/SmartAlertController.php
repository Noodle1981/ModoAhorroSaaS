<?php

namespace App\Http\Controllers;

use App\Models\SmartAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmartAlertController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user()->load('company.entities');
        $entityIds = $user->company ? $user->company->entities->pluck('id') : collect([]);

        $query = SmartAlert::with('entity')
            ->whereIn('entity_id', $entityIds);

        $filter = $request->get('filter', 'active');
        if ($filter === 'active') {
            $query->active();
        } elseif ($filter === 'unread') {
            $query->unread();
        }

        $alerts = $query->latest()->paginate(12)->appends(['filter' => $filter]);

        $stats = [
            'total' => SmartAlert::whereIn('entity_id', $entityIds)->count(),
            'active' => SmartAlert::whereIn('entity_id', $entityIds)->active()->count(),
            'unread' => SmartAlert::whereIn('entity_id', $entityIds)->unread()->count(),
        ];

        return view('alerts.index', compact('alerts','stats','filter'));
    }

    public function markAsRead(SmartAlert $alert)
    {
        $alert->markAsRead();
        return redirect()->back()->with('success', 'Alerta marcada como leída.');
        
    }

    public function dismiss(SmartAlert $alert)
    {
        $alert->dismiss();
        return redirect()->back()->with('success', 'Alerta descartada.');
    }
}
