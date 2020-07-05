<?php

namespace App\Security\Authentication\Provider;

use App\Entity\User;
use App\Security\Token\CalendarToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CalendarTokenProvider implements AuthenticationProviderInterface {

    private $em;

    public function __construct(EntityManagerInterface $manager) {
        $this->em = $manager;
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
        $user = $this->em->getRepository(User::class)
            ->findOneByCalendarToken($token->getToken());

        if($user === null) {
            throw new AuthenticationException('Token was not found.');
        }

        return new CalendarToken($token->getToken(), $user, $user->getRoles());
    }
}