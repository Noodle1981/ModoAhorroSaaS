<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar que la entidad tenga al menos una factura cargada
 * antes de permitir ciertas acciones (carga de equipos, ajustes, recomendaciones).
 */
class EnsureEntityHasInvoice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $entity = $request->route('entity');
        
        if (!$entity) {
            // Si no hay entidad en la ruta, dejar pasar (otra validación se encargará)
            return $next($request);
        }
        
        // Verificar si la entidad tiene al menos una factura
        $hasInvoice = $entity->supplies()
            ->whereHas('contracts.invoices')
            ->exists();
        
        if (!$hasInvoice) {
            return redirect()
                ->route('contracts.invoices.create', ['contract' => $entity->supplies->first()?->contracts->first()])
                ->with('warning', '⚠️ Primero debés cargar al menos una factura para poder gestionar equipos y ver análisis.');
        }
        
        return $next($request);
    }
}
