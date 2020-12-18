<?php

namespace App\Service\Helpers;

use App\Database\Model\User;

class UserHelper
{
    public function userHasEditPrivilegesForTargetUser(User $user, User $targetUser): bool
    {
        // if logged in user has higher privilege level than target user
        return ($user->getPrivilegeLevel() < $targetUser->getPrivilegeLevel());
    }
}