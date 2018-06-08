<?php

namespace App\Security\Firewall;

use App\Security\Token\StatusLinkToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class StatusLinkTokenListener implements ListenerInterface {

    const PARAM_NAME = 'token';

    protected $tokenStorage;
    protected $authenticationManager;

    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * @inheritDoc
     */
    public function handle(GetResponseEvent $event) {
        $request = $event->getRequest();
        $params = $request->attributes->get('_route_params');

        $tokenValue = $params[static::PARAM_NAME] ?? null;

        if($tokenValue !== null) {
            $token = new StatusLinkToken($tokenValue);

            try {
                $authToken = $this->authenticationManager->authenticate($token);
                $this->tokenStorage->setToken($authToken);

                return;
            } catch(AuthenticationException $e) {
                // TODO
            }
        }

        $response = new Response('', Response::HTTP_FORBIDDEN);
        $event->setResponse($response);
    }
}