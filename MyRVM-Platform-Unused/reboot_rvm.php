<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$controller = app()->make('App\Http\Controllers\Admin\EnhancedRemoteCommandsController');
$request = new \Illuminate\Http\Request();
$request->replace([
    'command_type' => 'system',
    'command_name' => 'reboot_system',
    'command_payload' => []
]);
$response = $controller->executeCommand($request, 1);
logger($response->getContent());