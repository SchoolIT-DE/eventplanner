<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\File;
use App\Entity\Group;
use App\Entity\ParticipationStatus;
use App\Event\EventEvent;
use App\Form\EventType;
use App\Helper\Grouping\EventGrouper;
use App\Helper\SecurityTools;
use App\Security\Voter\EventVoter;
use SchoolIT\CommonBundle\Form\ConfirmType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ManageEventsController extends Controller {
    /**
     * @Route("/admin/events", name="manage_events")
     */
    public function index(EventGrouper $eventGrouper) {
        /** @var Group[] $groups */
        $groups = $this->getDoctrine()->getRepository(Group::class)
            ->findAllUserIsAdminOf($this->getUser());

        /** @var Event[] $events */
        $events = $this->getDoctrine()->getRepository(Event::class)
            ->findForGroups($groups);

        /** @var Group[] $groups */
        $groups = $eventGrouper->groupByGroup($events);

        return $this->render('admin/events/index.html.twig', [
            'groups' => $groups
        ]);
    }

    /**
     * @Route("/admin/events/add", name="add_event")
     */
    public function add(Request $request, SecurityTools $securityTools) {
        $event = new Event();

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(EventVoter::ADD, $event);

            $em = $this->getDoctrine()->getManager();

            $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
            /** @var UploadedFile[] $uploadedFiles */
            $uploadedFiles = $form->get('group_files')->get('files')->getData();
            foreach($uploadedFiles as $uploadedFile) {
                $file = new File();
                $file->setEvent($event);
                $file->setOriginalFilename($uploadedFile->getClientOriginalName());

                $event->addFile($file);

                $uploadableManager->markEntityToUpload($file, $uploadedFile);
            }

            $participationStatus = (new ParticipationStatus())
                ->setEvent($event)
                ->setUser($this->getUser())
                ->setLinkToken($securityTools->getRandom())
                ->setStatus($form->get('group_status')->get('status')->getData());

            $event->addParticipant($participationStatus);

            $em->persist($participationStatus);
            $em->persist($event);

            $em->flush();

            $this->get('event_dispatcher')
                ->dispatch(EventEvent::CREATED, new EventEvent($event));

            $this->addFlash('success', 'manage_events.add.success');
            return $this->redirectToRoute('show_event', [
                'id' => $event->getId()
            ]);
        }

        return $this->render('admin/events/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/events/{id}/edit", name="edit_event")
     */
    public function edit(Request $request, Event $event) {
        $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);

            $em->flush();

            $this->addFlash('success', 'manage_events.edit.success');
            return $this->redirectToRoute('show_event', [
                'id' => $event->getId()
            ]);
        }

        return $this->render('admin/events/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event
        ]);
    }

    /**
     * @Route("/admin/events/{id}/remove", name="remove_event")
     */
    public function remove(Request $request, Event $event) {
        $this->denyAccessUnlessGranted(EventVoter::REMOVE, $event);

        $form = $this->createForm(ConfirmType::class, null, [
            'message' => $this->get('translator')->trans('manage_events.remove.confirm', [
                '%name%' => $event->getName()
            ])
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($event);
            $em->flush();

            $this->addFlash('success', 'manage_events.remove.success');
            return $this->redirectToRoute('manage_events');
        }

        return $this->render('admin/events/remove.html.twig', [
            'form' => $form->createView()
        ]);
    }
}