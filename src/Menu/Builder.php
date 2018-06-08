<?php

namespace App\Menu;

use App\Entity\Event;
use App\Entity\Group;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use SchoolIT\CommonBundle\Helper\DateHelper;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker,
                                EntityManagerInterface $em, DateHelper $dateHelper, FactoryInterface $factory) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->em = $em;
        $this->dateHelper = $dateHelper;
        $this->factory = $factory;
    }

    public function mainMenu(array $options) {
        $menu = $this->factory->createItem('root')
            ->setChildrenAttribute('class', 'nav nav-pills flex-column');

        $menu->addChild('menu.label', [
            'attributes' => [
                'class' => 'header'
            ]
        ]);

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $menu->addChild('events.label', [
            'route' => 'events'
        ])
            ->setAttribute('count', $this->em->getRepository(Event::class)->countForGroups($user->getGroups()->toArray(), $this->dateHelper->getToday()));

        $menu->addChild('messages.label', [
            'route' => 'messages'
        ])
            ->setAttribute('count', $this->em->getRepository(Message::class)->countMessagesForUser($user));

        $menu->addChild('groups.label', [
            'route' => 'groups'
        ]);

        if($this->authorizationChecker->isGranted('ROLE_GROUP_CREATOR')
         || $this->em->getRepository(Group::class)->countUserIsAdminOf($user) > 0) {

            $menu->addChild('administration.label', [
                'attributes' => [
                    'class' => 'header'
                ]
            ]);

            $menu->addChild('manage_groups.label', [
                'route' => 'manage_groups'
            ]);

            $menu->addChild('messages.new', [
                'route' => 'admin_messages'
            ]);

            $menu->addChild('manage_events.label', [
                'route' => 'manage_events'
            ]);
        }

        if($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $menu->addChild('logs.label', [
                'route' => 'admin_logs'
            ]);
            $menu->addChild('mails.label', [
                'route' => 'admin_mails'
            ]);
        }

        return $menu;
    }

}