<?php

namespace App\Services;

use App\Models\Member;

class DrivePermissionService
{
    public function remove(Member $member)
    {
        // Call external API to remove permission
        // GoogleDrive::removePermission($member->email); (example)
    }
}
