<?php

namespace App\Security\Authentication\Provider;

use App\Entity\User;
use App\Security\Token\CalendarToken;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CalendarTokenProvider implements AuthenticationProviderInterface {

    private $objectManager;

    public function __construct(ObjectManager $objectManager) {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     */
    public function supports(TokenInterface $token) {
        return $token instanceof CalendarToken;
    }

    /**
     * @inheritDoc
     */
    public function authenticate(TokenInterface $token) {
        /** @var User $user */
        $user = $this->objectManager->getRepository(User::class)
            ->findOneByCalendarToken($token->getToken());

        if($user === null) {
            throw new AuthenticationException('Token was not found.');
        }

        return new CalendarToken($token->getToken(), $user, $user->getRoles());
    }
}