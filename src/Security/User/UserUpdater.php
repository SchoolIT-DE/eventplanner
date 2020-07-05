<?php

namespace App\Security\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SchulIT\CommonBundle\Security\AuthenticationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserUpdater implements EventSubscriberInterface {

    /** @var EntityManagerInterface */
    private $em;

    /** @var UserMapper */
    private $userMapper;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(EntityManagerInterface $em, UserMapper $userMapper, LoggerInterface $logger) {
        $this->em = $em;
        $this->userMapper = $userMapper;
        $this->logger = $logger;
    }

    public function onAuthentication(AuthenticationEvent $event) {
        $token = $event->getToken();
        $user = $event->getUser();

        if($token === null) {
            $this->logger
                ->debug('Token is null, cannot update users');
            return;
        }

        if($user === null) {
            $this->logger
                ->debug('User is null, cannot update users');
            return;
        }

        if(!$user instanceof User) {
            $this->logger
                ->debug(sprintf('User is not of type "%s" ("%s" given), cannot update users', User::class, get_class($user)));
        }

        $response = $token->getResponse();

        $user = $this->userMapper->mapUser($user, $response);
        $this->em->persist($user);
        $this->em->flush();

        $this->logger
            ->debug('User updated from SAML response');
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents() {
        return [
            AuthenticationEvent::class => 'onAuthentication'
        ];
    }
}