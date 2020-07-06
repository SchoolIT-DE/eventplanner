<?php

namespace App\Security\User;

use App\Entity\User;
use App\Helper\SecurityTools;
use Doctrine\ORM\EntityManagerInterface;
use LightSaml\Model\Protocol\Response;
use LightSaml\SpBundle\Security\User\UserCreatorInterface;
use LightSaml\SpBundle\Security\User\UsernameMapperInterface;
use Ramsey\Uuid\Uuid;
use SchulIT\CommonBundle\Saml\ClaimTypes;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCreator implements UserCreatorInterface {

    /** @var EntityManagerInterface */
    private $em;


    /** @var UserMapper */
    private $userMapper;

    /** @var SecurityTools */
    private $securityTools;

    public function __construct(EntityManagerInterface $entityManager, UserMapper $userMapper, SecurityTools $securityTools) {
        $this->em = $entityManager;
        $this->securityTools = $securityTools;
    }

    /**
     * @param Response $response
     * @return UserInterface|null
     */
    public function createUser(Response $response) {
        $id = $response->getFirstAssertion()
            ->getFirstAttributeStatement()
            ->getFirstAttributeByName(ClaimTypes::ID)
            ->getFirstAttributeValue();

        $user = (new User())
            ->setIdpId(Uuid::fromString($id));

        $this->userMapper->mapUser($user, $response);

        do {
            $token = substr($this->securityTools->getRandom(), 0, 128);
            $user->setCalendarToken($token);
        } while($this->em->getRepository(User::class)->findOneByCalendarToken($token) !== null);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }


}