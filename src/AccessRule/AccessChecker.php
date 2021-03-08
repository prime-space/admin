<?php namespace App\AccessRule;

use App\Entity\User;
use LogicException;

class AccessChecker
{
    public function isGranted(AccessRuleInterface $accessRule, User $user, int $id = null): bool
    {
        $accessRightKey = array_search($accessRule->getId(), array_column($user->accessRights, 'id'));

        if ($accessRightKey === false) {
            return false;
        }
        if ($accessRule instanceof AccessRuleListInterface) {
            if ($id === null) {
                throw new LogicException('Id cannot be null');
            }
            return in_array($id, $user->accessRights[$accessRightKey]['list'], true);
        } else {
            return true;
        }
    }
}
