<?php

namespace App\DependencyInjection\Security\Factory;

use App\Security\Authentication\Provider\CalendarTokenProvider;
use App\Security\Firewall\CalendarTokenListener;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CalendarTokenFactory implements SecurityFactoryInterface {

    /**
     * @inheritDoc
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint) {
        $providerId = 'security.authentication.provider.calendar_token.' . $id;
        $container
            ->setDefinition($providerId, new ChildDefinition(CalendarTokenProvider::class));

        $listenerId = 'security.authentication.listener.calendar_token.' . $id;
        $container->setDefinition($listenerId, new ChildDefinition(CalendarTokenListener::class));

        return [
            $providerId,
            $listenerId,
            $defaultEntryPoint
        ];
    }

    /**
     * @inheritDoc
     */
    public function getPosition() {
        return 'pre_auth';
    }

    /**
     * @inheritDoc
     */
    public function getKey() {
        return 'calendar_token';
    }

    public function addConfiguration(NodeDefinition $builder) {
        // TODO: Implement addConfiguration() method.
    }
}