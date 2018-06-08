<?php

namespace App\Security\User;

use App\Entity\User;
use LightSaml\ClaimTypes;
use LightSaml\Model\Protocol\Response;

class UserMapper {
    const ROLES_ASSERTION_NAME = 'urn:roles';
    const PHONE_ASSERTION_NAME = 'telephoneNumber';
    const MOBILE_ASSERTION_NAME = 'mobilePhone';
    const ADDRESS_ASSERTION_NAME = 'address';

    public function mapUser(User $user, Response $response) {
        $firstname = $this->getValue($response, ClaimTypes::GIVEN_NAME);
        $lastname = $this->getValue($response, ClaimTypes::SURNAME);
        $email = $this->getValue($response, ClaimTypes::EMAIL_ADDRESS);
        $roles = $this->getValues($response, static::ROLES_ASSERTION_NAME);

        if(!is_array($roles)) {
            $roles = [ $roles ];
        }

        if(count($roles) === 0) {
            $roles = [ 'ROLE_USER' ];
        }

        $user->setFirstname($firstname)
            ->setLastname($lastname)
            ->setEmail($email)
            ->setRoles($roles);

        return $user;
    }

    private function getValue(Response $response, $attributeName) {
        return $response->getFirstAssertion()->getFirstAttributeStatement()
            ->getFirstAttributeByName($attributeName)->getFirstAttributeValue();
    }

    private function getValues(Response $response, $attributeName) {
        return $response->getFirstAssertion()->getFirstAttributeStatement()
            ->getFirstAttributeByName($attributeName)->getAllAttributeValues();
    }
}