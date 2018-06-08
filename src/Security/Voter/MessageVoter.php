<?php

namespace App\Security\Voter;

use App\Entity\Message;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessageVoter extends Voter {

    const EDIT = 'edit';
    const REMOVE = 'remove';

    private $accessDecisionManager;

    public function __construct(AccessDecisionManagerInterface $accessDecisionManager) {
        $this->accessDecisionManager = $accessDecisionManager;
    }

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject) {
        $attributes = [
            static::EDIT,
            static::REMOVE
        ];

        return in_array($attribute, $attributes)
            && $subject instanceof Message;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        switch($attribute) {
            case static::EDIT:
            case static::REMOVE:
                return $this->canEditOrRemove($subject, $token);
        }

        throw new \LogicException('This code should not be executed');
    }

    private function canEditOrRemove(Message $message, TokenInterface $token) {
        if($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        /** @var User $user */
        $user = $token->getUser();

        if($user === null || !$user instanceof User) {
            return false;
        }

        return $message->getCreatedBy()->getId() === $user->getId();
    }
}