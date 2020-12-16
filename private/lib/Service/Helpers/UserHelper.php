<?php

namespace App\Service\Helpers;

use App\Database\Model\User;

class UserHelper
{
    public function loggedInUserHasUpdatePrivilegesForThisUser(User $userThatWillBeModified, User $loggedInUser): bool
    {
        return ($userThatWillBeModified->getPrivilegeLevel() < $loggedInUser->getPrivilegeLevel());
    }
}