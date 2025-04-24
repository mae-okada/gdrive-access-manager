<?php

use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\Permission as GoogleDrivePermission;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/give-permission', function () {
    // Load the service account credentials
    $client = new GoogleClient;
    $client->setAuthConfig(storage_path('app/google/service-account.json'));
    $client->addScope(GoogleDrive::DRIVE);

    // Create the Drive service
    $driveService = new GoogleDrive($client);

    // The folder ID you want to share
    $folderId = '1KM45PCbg0aDjeRhIIyikLOr6g1PRIJMq';

    // Create a new permission
    $permission = new GoogleDrivePermission([
        'type' => 'user',
        'role' => 'writer', // use 'reader' for view only
        'emailAddress' => 'inimaeokada@gmail.com',
    ]);

    try {
        $driveService->permissions->create(
            $folderId,
            $permission,
            ['sendNotificationEmail' => true] // optional: true = user gets email
        );

        return '✅ Editor access granted to inimaeokada@gmail.com!';
    } catch (\Exception $e) {
        return '❌ Failed: '.$e->getMessage();
    }
});
