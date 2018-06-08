<?php

namespace App\Security\Voter;

use App\Entity\Group;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GroupVoter extends Voter {

    const ADD = 'add_group';
    const EDIT = 'edit';
    const REMOVE = 'remove';
    const MAIL = 'mail';

    private $accessDecisionManager;

    public function __construct(AccessDecisionManagerInterface $accessDecisionManager) {
        $this->accessDecisionManager = $accessDecisionManager;
    }

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject) {
        if($attribute === static::ADD && $subject === null) {
            return true;
        }

        $attributes = [
            static::EDIT,
            static::REMOVE,
            static::MAIL
        ];

        return in_array($attribute, $attributes)
            && $subject instanceof Group;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        switch($attribute) {
            case static::ADD:
                return $this->canAddGroup($token);

            case static::EDIT:
            case static::REMOVE:
                return $this->canEditOrRemoveGroup($subject, $token);

            case static::MAIL:
                return $this->canSendMailToGroup($subject, $token);
        }

        throw new \LogicException('This code should not be executed');
    }

    private function canAddGroup(TokenInterface $token) {
        return $this->accessDecisionManager->decide($token, [ 'ROLE_GROUP_CREATOR' ]);
    }

    private function canEditOrRemoveGroup(Group $group, TokenInterface $token) {
        if($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        $user = $token->getUser();

        if(!$user instanceof User) {
            return false;
        }

        return $this->isUserAdminOfGroup($group, $user);
    }

    private function canSendMailToGroup(Group $group, TokenInterface $token) {
        if($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        $user = $token->getUser();

        if(!$user instanceof User) {
            return false;
        }

        return $this->isUserAdminOfGroup($group, $user);
    }

    private function isUserAdminOfGroup(Group $group, User $user) {
        /** @var User[] $admins */
        $admins = $group->getAdmins();

        foreach($admins as $admin) {
            if($admin->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }
}