<?php

namespace App\Services;

use App\Models\Gdrive;
use App\Models\Member;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\Permission as GoogleDrivePermission;
use Illuminate\Support\Facades\Log;

class DrivePermissionService
{
    public function assign(Member $member): void
    {
        $folderIds = Gdrive::where('division_id', $member->division_id)->pluck('unique_id');
        $targetEmail = $member->email;

        $client = new GoogleClient;
        $client->setAuthConfig(storage_path(config('google.service_account')));
        $client->addScope(GoogleDrive::DRIVE);

        $driveService = new GoogleDrive($client);

        $permission = new GoogleDrivePermission([
            'type' => 'user',
            'role' => 'writer', // use 'reader' for view only
            'emailAddress' => $targetEmail,
        ]);

        foreach ($folderIds as $folderId) {
            try {
                $driveService->permissions->create(
                    $folderId,
                    $permission,
                    ['sendNotificationEmail' => false] // optional: true = user gets email
                );
            } catch (\Throwable $e) {
                Log::error('Failed to assign drive access permission for target email', [
                    'member_email' => $targetEmail,
                    'folder_id' => $folderId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

    }

    public function remove(Member $member): void
    {
        $folderId = config('google.master_folder');
        $targetEmail = $member->email;

        $client = new GoogleClient;
        $client->setAuthConfig(storage_path(config('google.service_account')));
        $client->addScope(GoogleDrive::DRIVE);

        $driveService = new GoogleDrive($client);

        try {
            // remove access from files & folders inside the master folder
            $this->removePermissionsFromFolder($driveService, $folderId, $targetEmail);

            // remove permission from master folder itself
            $permissions = $driveService
                ->permissions
                ->listPermissions(
                    $folderId,
                    [
                        'fields' => 'permissions(id,emailAddress)',
                    ]
                );

            $permission = collect($permissions->getPermissions())
                ->first(
                    function ($userPermission) use ($targetEmail) {
                        return $userPermission->getEmailAddress() === $targetEmail;
                    }
                );

            if ($permission) {
                $driveService->permissions->delete($folderId, $permission->getId());
            }
        } catch (\Throwable $e) {
            Log::error('Failed to remove drive access for target email', [
                'member_email' => $targetEmail,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function removePermissionsFromFolder(GoogleDrive $driveService, string $folderId, string $targetEmail): void
    {
        $pageToken = null;
        do {
            // List all files and folders in the current folder
            $response = $driveService->files->listFiles([
                'q' => "'$folderId' in parents and trashed = false",
                'fields' => 'nextPageToken, files(id, name, mimeType)',
                'pageToken' => $pageToken,
            ]);

            foreach ($response->files as $file) {
                if ($file->mimeType === 'application/vnd.google-apps.folder') {
                    // It's a folder — first, remove permission
                    $permissions = $driveService
                        ->permissions
                        ->listPermissions(
                            $file->id,
                            [
                                'fields' => 'permissions(id,emailAddress)',
                            ]
                        );

                    $permission = collect($permissions->getPermissions())
                        ->first(
                            function ($userPermission) use ($targetEmail) {
                                return $userPermission->getEmailAddress() === $targetEmail;
                            }
                        );

                    if ($permission) {
                        $driveService->permissions->delete($file->id, $permission->getId());
                    }

                    // It's a folder — recurse into it
                    $this->removePermissionsFromFolder($driveService, $file->id, $targetEmail);
                } else {
                    // It's a file — remove the targeted email's permissions
                    $this->removeEmailPermissionFromFile($driveService, $file->id, $targetEmail);
                }
            }

            // Move to the next page of results if available
            $pageToken = $response->getNextPageToken();
        } while ($pageToken !== null);
    }

    private function removeEmailPermissionFromFile(GoogleDrive $driveService, string $fileId, string $targetEmail): void
    {
        try {
            // Get all permissions of the file
            $permissions = $driveService->permissions->listPermissions(
                $fileId,
                ['fields' => 'permissions(id,emailAddress)']
            );

            // Find and remove permission for the targeted email
            foreach ($permissions->getPermissions() as $permission) {
                if ($permission->getEmailAddress() === $targetEmail) {
                    // Remove the permission for the targeted email
                    $driveService->permissions->delete($fileId, $permission->getId());
                    Log::info("Removed permission for email: {$targetEmail} from file: {$fileId}");
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to remove permission for targeted email', [
                'file_id' => $fileId,
                'member_email' => $targetEmail,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
