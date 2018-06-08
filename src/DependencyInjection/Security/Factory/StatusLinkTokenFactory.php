<?php

namespace App\DependencyInjection\Security\Factory;

use App\Security\Authentication\Provider\StatusLinkTokenProvider;
use App\Security\Firewall\StatusLinkTokenListener;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class StatusLinkTokenFactory implements SecurityFactoryInterface {

    /**
     * @inheritDoc
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint) {
        $providerId = 'security.authentication.provider.status_link_token.' . $id;
        $container
            ->setDefinition($providerId, new ChildDefinition(StatusLinkTokenProvider::class));

        $listenerId = 'security.authentication.listener.status_link_token.' . $id;
        $container->setDefinition($listenerId, new ChildDefinition(StatusLinkTokenListener::class));

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
        return 'status_link_token';
    }

    public function addConfiguration(NodeDefinition $builder) {
        // TODO: Implement addConfiguration() method.
    }
}