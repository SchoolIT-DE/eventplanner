<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserSetLanguageListener implements EventSubscriberInterface {

    private $redirectRoute;
    private $tokenStorage;
    private $urlGenerator;

    public function __construct($redirectRoute, TokenStorageInterface $tokenStorage, UrlGeneratorInterface $urlGenerator) {
        $this->redirectRoute = $redirectRoute;
        $this->tokenStorage = $tokenStorage;
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelRequest(RequestEvent $event) {
        $request = $event->getRequest();

        if(!$event->isMasterRequest()) {
            return;
        }

        $currentRoute = $request->attributes->get('_route');

        if($currentRoute === $this->redirectRoute || $currentRoute === null) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if($token === null) {
            return;
        }

        $user = $token->getUser();

        if($user === null || !$user instanceof User) {
            return;
        }

        if($user->getLanguage() === null) {
            $url = $this->urlGenerator->generate($this->redirectRoute);
            $response = new RedirectResponse($url);

            $event->setResponse($response);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents() {
        return [
            KernelEvents::REQUEST => [ 'onKernelRequest', 0]
        ];
    }
}