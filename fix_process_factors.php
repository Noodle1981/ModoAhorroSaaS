<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProcessFactor;
use Illuminate\Support\Str;

$map = [
  'Motor' => ['factor_carga'=>0.75, 'eficiencia'=>0.80],
  'Resistencia' => ['factor_carga'=>1.00, 'eficiencia'=>1.00],
  'Electrónico' => ['factor_carga'=>0.80, 'eficiencia'=>0.85],
  'Motor & Resistencia' => ['factor_carga'=>0.85, 'eficiencia'=>0.85],
  'Magnetrón' => ['factor_carga'=>1.00, 'eficiencia'=>0.65],
  'Electroluminiscencia' => ['factor_carga'=>1.00, 'eficiencia'=>0.90],
];

foreach ($map as $tipo => $vals) {
  $pf = ProcessFactor::firstOrNew(['tipo_de_proceso'=>$tipo]);
  $pf->factor_carga = $vals['factor_carga'];
  $pf->eficiencia = $vals['eficiencia'];
  $pf->save();
  echo "✓ Actualizado: {$tipo} → FC={$pf->factor_carga}, Eff={$pf->eficiencia}\n";
}

echo "\nListo. Ahora podés (opcional) actualizar equipos existentes a estos defaults si no tenés overrides manuales.\n";
