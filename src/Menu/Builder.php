<?php

namespace App\Menu;

use App\Entity\Event;
use App\Entity\Group;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use LightSaml\SpBundle\Security\Authentication\Token\SamlSpToken;
use SchulIT\CommonBundle\DarkMode\DarkModeManagerInterface;
use SchulIT\CommonBundle\Helper\DateHelper;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Builder {

    /** @var TokenStorageInterface  */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface  */
    private $authorizationChecker;

    /** @var EntityManagerInterface  */
    private $em;

    /** @var DateHelper */
    private $dateHelper;

    /** @var FactoryInterface  */
    private $factory;

    private $translator;

    private $darkModeManager;

    private $idpProfileUrl;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker,
                                EntityManagerInterface $em, DateHelper $dateHelper, FactoryInterface $factory, TranslatorInterface $translator,
                                DarkModeManagerInterface $darkModeManager, string $idpProfileUrl) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->em = $em;
        $this->dateHelper = $dateHelper;
        $this->factory = $factory;
        $this->translator = $translator;
        $this->darkModeManager = $darkModeManager;
        $this->idpProfileUrl = $idpProfileUrl;
    }

    public function mainMenu(array $options): ItemInterface {
        $menu = $this->factory->createItem('root')
            ->setChildrenAttribute('class', 'navbar-nav mr-auto');

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $menu->addChild('events.label', [
            'route' => 'events'
        ])
            ->setExtra('count', $this->em->getRepository(Event::class)->countForGroups($user->getGroups()->toArray(), $this->dateHelper->getToday()))
            ->setExtra('icon', 'far fa-calendar-alt');

        $menu->addChild('groups.label', [
            'route' => 'groups'
        ])
            ->setExtra('icon', 'fas fa-users');

        return $menu;
    }

    public function userMenu(array $options): ItemInterface {
        $menu = $this->factory->createItem('root')
            ->setChildrenAttributes([
                'class' => 'navbar-nav float-lg-right'
            ]);

        $user = $this->tokenStorage->getToken()->getUser();

        if(!$user instanceof User) {
            return $menu;
        }

        $displayName = $user->getUsername();

        $userMenu = $menu->addChild('user', [
            'label' => $displayName
        ])
            ->setExtra('icon', 'fa fa-user')
            ->setExtra('menu', 'user')
            ->setExtra('menu-container', '#submenu')
            ->setExtra('pull-right', true);

        $userMenu->addChild('profile.overview.label', [
            'route' => 'profile'
        ])
            ->setExtra('icon', 'far fa-user');

        $userMenu->addChild('profile.label', [
            'uri' => $this->idpProfileUrl
        ])
            ->setAttribute('target', '_blank')
            ->setExtra('icon', 'far fa-address-card');

        $label = 'dark_mode.enable';
        $icon = 'far fa-moon';

        if($this->darkModeManager->isDarkModeEnabled()) {
            $label = 'dark_mode.disable';
            $icon = 'far fa-sun';
        }

        $userMenu->addChild($label, [
            'route' => 'toggle_darkmode'
        ])
            ->setExtra('icon', $icon);

        $menu->addChild('label.logout', [
            'route' => 'logout',
            'label' => ''
        ])
            ->setExtra('icon', 'fas fa-sign-out-alt')
            ->setAttribute('title', $this->translator->trans('auth.logout'));

        return $menu;
    }

    public function adminMenu(array $options): ItemInterface {
        $root = $this->factory->createItem('root')
            ->setChildrenAttributes([
                'class' => 'navbar-nav float-lg-right'
            ]);

        $menu = $root->addChild('admin', [
            'label' => ''
        ])
            ->setExtra('icon', 'fa fa-cogs')
            ->setAttribute('title', $this->translator->trans('administration.label'))
            ->setExtra('menu', 'admin')
            ->setExtra('menu-container', '#submenu')
            ->setExtra('pull-right', true);

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        if($this->authorizationChecker->isGranted('ROLE_GROUP_CREATOR')
            || $this->em->getRepository(Group::class)->countUserIsAdminOf($user) > 0) {

            $menu->addChild('manage_groups.label', [
                'route' => 'manage_groups'
            ])
                ->setExtra('icon', 'fas fa-users');

            $menu->addChild('manage_events.label', [
                'route' => 'manage_events'
            ])
                ->setExtra('icon', 'far fa-calendar-alt');
        }

        if($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $menu->addChild('cron.label', [
                'route' => 'admin_cronjobs'
            ])
                ->setExtra('icon', 'fas fa-history');

            $menu->addChild('logs.label', [
                'route' => 'admin_logs'
            ])
                ->setExtra('icon', 'fas fa-clipboard-list');
            $menu->addChild('mails.label', [
                'route' => 'admin_mails'
            ])
                ->setExtra('icon', 'far fa-envelope');
        }

        return $root;
    }

    public function servicesMenu(): ItemInterface {
        $root = $this->factory->createItem('root')
            ->setChildrenAttributes([
                'class' => 'navbar-nav float-lg-right'
            ]);

        $token = $this->tokenStorage->getToken();

        if($token instanceof SamlSpToken) {
            $menu = $root->addChild('services', [
                'label' => ''
            ])
                ->setExtra('icon', 'fa fa-th')
                ->setExtra('menu', 'services')
                ->setExtra('pull-right', true)
                ->setAttribute('title', $this->translator->trans('services.label'));

            foreach($token->getAttribute('services') as $service) {
                $menu->addChild($service->name, [
                    'uri' => $service->url
                ])
                    ->setAttribute('title', $service->description)
                    ->setAttribute('target', '_blank');
            }
        }

        return $root;
    }
}