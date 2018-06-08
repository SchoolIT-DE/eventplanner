<?php

namespace App\Security\Voter;

use App\Entity\Comment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter {

    const ADD = 'add';
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
            static::ADD,
            static::REMOVE
        ];

        return in_array($attribute, $attributes)
            && $subject instanceof Comment;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        switch($attribute) {
            case static::ADD:
                return $this->canAdd($subject, $token);

            case static::REMOVE:
                return $this->canRemove($subject, $token);
        }

        throw new \LogicException('This code should not be executed');
    }

    private function canAdd(Comment $comment, TokenInterface $token) {
        return $this->accessDecisionManager->decide($token, [ EventVoter::VIEW ], $comment->getEvent());
    }

    private function canRemove(Comment $comment, TokenInterface $token) {
        if($this->accessDecisionManager->decide($token, [ 'ROLE_ADMIN' ])) {
            return true;
        }

        return $comment->getCreatedBy()->getId() === $token->getUser()->getId();
    }
}