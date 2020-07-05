<?php

namespace App\Security\Authentication\Provider;

use App\Entity\ParticipationStatus;
use App\Security\Token\StatusLinkToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class StatusLinkTokenProvider implements AuthenticationProviderInterface {

    private $em;

    public function __construct(EntityManagerInterface $manager) {
        $this->em = $manager;
    }

    /**
     * @inheritDoc
     */
    public function authenticate(TokenInterface $token) {
        /** @var ParticipationStatus $status */
        $status = $this->em
            ->getRepository(ParticipationStatus::class)
            ->findOneByLinkToken($token->getToken());

        if($status === null) {
            throw new AuthenticationException('Token was not found');
        }

        $user = $status->getUser();

        return new StatusLinkToken($token->getToken(), $user, $user->getRoles());
    }

    /**
     * @inheritDoc
     */
    public function supports(TokenInterface $token) {
        return $token instanceof StatusLinkToken;
    }
}