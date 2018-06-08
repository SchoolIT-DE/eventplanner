<?php

namespace App\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

class StatusLinkToken extends AbstractToken {

    private $token;

    public function __construct($token, UserInterface $user = null, array $roles = []) {
        parent::__construct($roles);

        $this->token = $token;

        if($user !== null) {
            $this->setUser($user);
        }

        $this->setAuthenticated(count($roles) > 0);
    }

    public function getToken() {
        return $this->token;
    }

    /**
     * @inheritDoc
     */
    public function getCredentials() {
        return '';
    }
}