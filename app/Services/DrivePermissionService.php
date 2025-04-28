<?php

namespace App\Services;

use App\Models\Member;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Illuminate\Support\Facades\Log;

class DrivePermissionService
{
    public function remove(Member $member): void
    {
        $folderId = '12X-Z1IuzuFJbOd1Uzo_xFcQGFr8WHBc8'; // Target master folder
        $targetEmail = $member->email;

        $client = new GoogleClient;
        $client->setAuthConfig(storage_path('app/google/service-account.json'));
        $client->addScope(GoogleDrive::DRIVE);

        $driveService = new GoogleDrive($client);

        try {
            // Start with the given folder ID and traverse its children
            $this->removePermissionsFromFolder($driveService, $folderId, $targetEmail);

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
