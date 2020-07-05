<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Helper\Ics\IcsHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IcsController extends AbstractController {

    /**
     * @Route("/ics/{token}", name="ics")
     */
    public function ics(IcsHelper $icsHelper) {
        /** @var User $user */
        $user = $this->getUser();

        /** @var Event[] $events */
        $events = $this->getDoctrine()->getRepository(Event::class)
            ->findForGroups($user->getGroups()->toArray());

        $ics = $icsHelper->getIcsContent($events);

        return new Response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename=calendar.ics'
        ]);
    }
}