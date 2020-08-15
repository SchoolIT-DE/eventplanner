<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Event;
use App\Entity\File;
use App\Entity\Group;
use App\Entity\ParticipationStatus;
use App\Entity\User;
use App\Event\CommentCreatedEvent;
use App\Form\CommentType;
use App\Helper\ParticipationStatus\ParticipationStatusHelper;
use App\Helper\SecurityTools;
use App\Security\Voter\CommentVoter;
use App\Security\Voter\EventVoter;
use SchulIT\CommonBundle\Form\ConfirmType;
use SchulIT\CommonBundle\Helper\DateHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventController extends AbstractController {

    const EVENT_STATUS_TOKEN_ID = 'csrf.event.status';

    /**
     * @Route("/", name="dashboard")
     * @Route("/events", name="events")
     * @Route("/events/all", name="all_events")
     */
    public function index(Request $request, ParticipationStatusHelper $participationStatusHelper, DateHelper $dateHelper) {
        $routeName = $request->get('_route');
        $allEvents = $routeName === 'all_events';

        /** @var Group[] $groups */
        $groups = $this->getUser()->getGroups()->toArray();

        /** @var Event[] $events */
        $events = $this->getDoctrine()->getManager()
            ->getRepository(Event::class)
            ->findForGroups($groups, $allEvents ? null : $dateHelper->getToday());

        $participants = [ ];
        $accepted = [ ];
        $ownParticipation = [ ];

        /** @var User $user */
        $user = $this->getUser();

        foreach($events as $event) {
            $eventId = $event->getId();

            $participants[$eventId] = $participationStatusHelper->getParticipationStatus($event);
            $accepted[$eventId] = 0;

            foreach($participants[$eventId] as $participant) {
                if($participant->getUser()->getId() === $user->getId()) {
                    $ownParticipation[$eventId] = $participant;
                }

                if($participant->getStatus() === ParticipationStatus::STATUS_ACCEPTED) {
                    $accepted[$eventId]++;
                }
            }
        }

        return $this->render('events/index.html.twig', [
            'all_events' => $allEvents,
            'events' => $events,
            'participants' => $participants,
            'accepted' => $accepted,
            'ownParticipation' => $ownParticipation,
            'csrfToken' => $this->get('security.csrf.token_manager')->getToken(static::EVENT_STATUS_TOKEN_ID)
        ]);
    }

    /**
     * @Route("/events/{uuid}", name="show_event")
     */
    public function show(Request $request, Event $event, ParticipationStatusHelper $participationStatusHelper, EventDispatcherInterface $eventDispatcher) {
        $this->denyAccessUnlessGranted(EventVoter::VIEW, $event);

        $comment = (new Comment())
            ->setEvent($event);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(CommentVoter::ADD, $comment);

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            $eventDispatcher->dispatch(new CommentCreatedEvent($comment));

            $this->addFlash('success', 'event.comments.success');

            return $this->redirectToRoute('show_event', [
                'uuid' => $event->getUuid()
            ]);
        }

        $participants = $participationStatusHelper->getParticipationStatus($event);
        $accepted = 0;

        foreach($participants as $participant) {
            if($participant->getStatus() === ParticipationStatus::STATUS_ACCEPTED) {
                $accepted++;
            }
        }

        return $this->render('events/show.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'participants' => $participants,
            'accepted' => $accepted,
            'csrfToken' => $this->get('security.csrf.token_manager')->getToken(static::EVENT_STATUS_TOKEN_ID)
        ]);
    }

    /**
     * @Route("/events/{eventUuid}/file/{uuid}/{filename}", name="download_file")
     */
    public function downloadFile(Request $request, File $file) {
        $this->denyAccessUnlessGranted(EventVoter::VIEW, $file->getEvent());

        $response = new BinaryFileResponse($file->getStorageName(), 200, [
            'Content-Type' => $file->getMimeType(),
            'Content-Length' => $file->getSize()
        ]);

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file->getOriginalFilename()
        );

        return $response;
    }

    /**
     * @Route("/events/{eventUuid}/comments/{uuid}/remove", name="remove_comment")
     */
    public function removeComment(Request $request, Comment $comment) {
        $this->denyAccessUnlessGranted(CommentVoter::REMOVE, $comment);

        $form = $this->createForm(ConfirmType::class, null, [
            'message' => $this->get('translator')->trans('event.comments.remove.confirm')
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();

            $this->addFlash('success', 'event.comments.remove.success');

            return $this->redirectToRoute('show_event', [
                'uuid' => $comment->getEvent()->getUuid()
            ]);
        }

        return $this->render('events/remove_comment.html.twig', [
            'event' => $comment->getEvent(),
            'comment' => $comment,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/events/{uuid}/change_status", name="change_status")
     */
    public function changeStatus(Request $request, Event $event, SecurityTools $securityTools) {
        $this->denyAccessUnlessGranted(EventVoter::CHANGE_STATUS, $event);

        $status = $request->request->get('status', null);
        $token = $request->request->get('_csrf_token');

        if($this->get('security.csrf.token_manager')->isTokenValid(new CsrfToken(static::EVENT_STATUS_TOKEN_ID, $token)) !== true) {
            $this->addFlash('error', 'Invalid CSRF token.');

            return $this->redirectToRoute('show_event', [
                'uuid' => $event->getUuid()
            ]);
        }

        if(ParticipationStatus::isValidStatus($status) !== true || $status === ParticipationStatus::STATUS_PENDING) {
            $this->addFlash('error', 'event.status.error_invalid');

            return $this->redirectToRoute('show_event', [
                'uuid' => $event->getUuid()
            ]);
        }

        /** @var User $user */
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $isChanged = false;

        foreach($event->getParticipants() as $participant) {
            if($participant->getUser()->getId() === $user->getId()) {
                $participant->setStatus($status);
                $em->persist($participant);

                $isChanged = true;
            }
        }

        if($isChanged === false) {
            $participant = (new ParticipationStatus())
                ->setEvent($event)
                ->setUser($user)
                ->setLinkToken($securityTools->getRandom())
                ->setStatus($status);
            $em->persist($participant);
        }

        $em->flush();

        $this->addFlash('success', 'event.status.success');
        return $this->redirectToRoute('show_event', [
            'uuid' => $event->getUuid()
        ]);
    }

    /**
     * @Route("/mail/change_status/{token}/{status}", name="change_status_mail")
     * @ParamConverter("participationStatus", options={"mapping": {"token" = "linkToken"}})
     */
    public function changeStatusWithEmail(ParticipationStatus $participationStatus, $status) {
        if(ParticipationStatus::isValidStatus($status) !== true || $status === ParticipationStatus::STATUS_PENDING) {
            throw new BadRequestHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $participationStatus->setStatus($status);
        $em->persist($participationStatus);
        $em->flush();

        $this->addFlash('success', 'event.status.success');
        return $this->render('events/status_changed.html.twig');
    }
}