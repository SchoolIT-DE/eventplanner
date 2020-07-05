<?php

namespace App\DarkMode;

use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use SchulIT\CommonBundle\DarkMode\DarkModeManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DarkModeManager implements DarkModeManagerInterface {

    private const Key = 'settings.dark_mode.enabled';

    private $tokenStorage;
    private $em;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $manager) {
        $this->tokenStorage = $tokenStorage;
        $this->em = $manager;
    }

    private function getUser(): ?User {
        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

    private function setDarkMode(bool $isDarkModeEnabled): void {
        $user = $this->getUser();

        if ($user !== null) {
            $user->setData(static::Key, $isDarkModeEnabled);
            $this->em->persist($user);
        }
    }

    public function enableDarkMode(): void {
        $this->setDarkMode(true);
    }

    public function disableDarkMode(): void {
        $this->setDarkMode(false);
    }

    public function isDarkModeEnabled(): bool {
        $user = $this->getUser();

        if ($user !== null) {
            return $user->getData(static::Key, false) === true;
        }

        return false;
    }
}