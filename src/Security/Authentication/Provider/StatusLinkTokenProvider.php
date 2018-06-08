<?php

namespace App\Security\Authentication\Provider;

use App\Entity\ParticipationStatus;
use App\Security\Token\StatusLinkToken;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class StatusLinkTokenProvider implements AuthenticationProviderInterface {

    private $objectManager;

    public function __construct(ObjectManager $objectManager) {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     */
    public function authenticate(TokenInterface $token) {
        /** @var ParticipationStatus $status */
        $status = $this->objectManager
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