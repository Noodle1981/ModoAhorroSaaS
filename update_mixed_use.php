<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$entity = App\Models\Entity::find(1);
$details = $entity->details;
$details['mixed_use'] = true;
$entity->details = $details;
$entity->save();

echo "Entidad actualizada con mixed_use = true\n";
print_r($entity->details);
