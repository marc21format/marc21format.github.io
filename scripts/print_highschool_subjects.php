<?php
$projectRoot = dirname(__DIR__);
require $projectRoot . '/vendor/autoload.php';
$app = require $projectRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$items = App\Models\HighschoolSubject::orderBy('subject_name')->get()->toArray();
echo json_encode($items, JSON_PRETTY_PRINT);
