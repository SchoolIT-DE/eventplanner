<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\Group;
use App\Entity\User;
use SchulIT\CommonBundle\Helper\DateHelper;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventVoter extends Voter {

    const ADD = 'add_event';
    const EDIT = 'edit';
    const REMOVE = 'remove';
    const VIEW = 'view';
    const CHANGE_STATUS = 'change_status';

    private $accessDecisionManager;
    private $dateHelper;

    public function __construct(AccessDecisionManagerInterface $accessDecisionManager, DateHelper $dateHelper) {
        $this->accessDecisionManager = $accessDecisionManager;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject) {
        $attributes = [
            static::ADD,
            static::EDIT,
            static::REMOVE,
            static::VIEW,
            static::CHANGE_STATUS
        ];

        if(!in_array($attribute, $attributes)) {
            return false;
        }

        return $subject instanceof Event
            || ($subject === null && $attribute === static::ADD);
    }

    /**
     * @param string $attribute
     * @param Event|Group $subject
     * @param TokenInterface $token
     * @return boolean
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        switch($attribute) {
            case static::ADD:
                return $this->canAdd($subject, $token);

            case static::EDIT:
            case static::REMOVE:
                return $this->canEditOrRemove($subject, $token);

            case static::VIEW:
                return $this->canView($subject, $token);

            case static::CHANGE_STATUS:
                return $this->canChangeStatus($subject, $token);
        }

        throw new \LogicException('This code should not be executed');
    }

    private function canAdd(?Event $event, TokenInterface $token) {
        if($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        $user = $token->getUser();

        if(!$user instanceof User) {
            return false;
        }

        return $this->isUserAdminOfAnyGroup($user, $event === null ? $event->getGroups() : null);
    }

    private function canEditOrRemove(Event $event, TokenInterface $token) {
        if($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        /** @var User $user */
        $user = $token->getUser();

        if($user === null || !$user instanceof User) {
            return false;
        }

        return $event->getCreatedBy()->getId() === $user->getId();
    }

    private function canView(Event $event, TokenInterface $token) {
        if($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        /** @var User $user */
        $user = $token->getUser();

        if($user === null || !$user instanceof User) {
            return false;
        }

        foreach($event->getGroups() as $group) {
            foreach($group->getMembers() as $member) {
                if($member->getId() === $user->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    private function canChangeStatus(Event $event, TokenInterface $token) {
        if($event->getStart() < $this->dateHelper->getToday()) {
            return false;
        }

        /** @var User $user */
        $user = $token->getUser();

        if($user === null) {
            return false;
        }

        /** @var Group[] $groups */
        $groups = $event->getGroups();

        foreach($groups as $group) {
            /** @var User[] $members */
            $members = $group->getMembers();

            foreach($members as $member) {
                if($member->getId() === $user->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param User $user
     * @param Group[] $groups
     * @return bool
     */
    private function isUserAdminOfAnyGroup(User $user, ?array $groups) {
        if($groups === null) {
            $groups = $user->getGroups();
        }

        foreach($groups as $group) {
            if($this->isUserAdminOfGroup($group, $user)) {
                return true;
            }
        }

        return false;
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