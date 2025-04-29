<?php

use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\Permission as GoogleDrivePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/give-permission', function () {
    $email = 'maeisfront@gmail.com';

    // Load the service account credentials
    $client = new GoogleClient;
    $client->setAuthConfig(storage_path(config('google.service_account')));
    $client->addScope(GoogleDrive::DRIVE);

    // Create the Drive service
    $driveService = new GoogleDrive($client);

    // The folder ID you want to share
    $folderId = config('google.master_folder');

    // Create a new permission
    $permission = new GoogleDrivePermission([
        'type' => 'user',
        'role' => 'writer', // use 'reader' for view only
        'emailAddress' => $email,
    ]);

    try {
        $driveService->permissions->create(
            $folderId,
            $permission,
            ['sendNotificationEmail' => false] // optional: true = user gets email
        );

        return '✅ Editor access granted to: '.$email;
    } catch (\Exception $e) {
        return '❌ Failed: '.$e->getMessage();
    }
})
    ->name('drive.permission.assign');

Route::get('/remove-permission', function () {
    // Load the service account credentials
    $client = new GoogleClient;
    $client->setAuthConfig(storage_path(config('google.service_account')));
    $client->addScope(GoogleDrive::DRIVE);

    // Create the Drive service
    $driveService = new GoogleDrive($client);

    $folderId = config('google.master_folder');
    $targetEmail = 'inimaeokada@gmail.com'; // Email of the user you want to remove access permission

    try {
        // Get all permissions of the folder
        $permissions = $driveService
            ->permissions
            ->listPermissions(
                $folderId,
                [
                    'fields' => 'permissions(id,emailAddress)',
                ]
            );

        // Find the permission ID for the email
        $permission = collect($permissions->getPermissions())
            ->first(
                function ($userPermission) use ($targetEmail) {
                    return $userPermission->getEmailAddress() === $targetEmail;
                }
            );

        if ($permission) {
            $driveService->permissions->delete($folderId, $permission->getId());

            return "✅ Removed access for $targetEmail";
        } else {
            return "❌ No permission found for $targetEmail";
        }
    } catch (\Exception $e) {
        return '❌ Failed: '.$e->getMessage();
    }
});
