<?php

namespace App\Security\Firewall;

use App\Security\Token\CalendarToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CalendarTokenListener {

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
    public function __invoke(RequestEvent $event) {
        $request = $event->getRequest();
        $params = $request->attributes->get('_route_params');

        $tokenValue = $params[static::PARAM_NAME] ?? null;

        if($tokenValue !== null) {
            $token = new CalendarToken($tokenValue);

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