<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\File;
use App\Entity\Group;
use App\Entity\ParticipationStatus;
use App\Event\EventCreatedEvent;
use App\Form\EventType;
use App\Helper\Grouping\EventGrouper;
use App\Helper\SecurityTools;
use App\Security\Voter\EventVoter;
use SchulIT\CommonBundle\Form\ConfirmType;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ManageEventsController extends AbstractController {
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
    public function add(Request $request, SecurityTools $securityTools, UploadableManager $uploadableManager, EventDispatcherInterface $eventDispatcher) {
        $event = new Event();

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(EventVoter::ADD, $event);

            $em = $this->getDoctrine()->getManager();

            //$uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
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

            $eventDispatcher
                ->dispatch(new EventCreatedEvent($event));

            $this->addFlash('success', 'manage_events.add.success');
            return $this->redirectToRoute('show_event', [
                'uuid' => $event->getUuid()
            ]);
        }

        return $this->render('admin/events/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/events/{uuid}/edit", name="edit_event")
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
                'uuid' => $event->getUuid()
            ]);
        }

        return $this->render('admin/events/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event
        ]);
    }

    /**
     * @Route("/admin/events/{uuid}/remove", name="remove_event")
     */
    public function remove(Request $request, Event $event) {
        $this->denyAccessUnlessGranted(EventVoter::REMOVE, $event);

        $form = $this->createForm(ConfirmType::class, null, [
            'message' => 'manage_events.remove.confirm',
            'message_parameters' => [
                '%name%' => $event->getName()
            ]
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
            'form' => $form->createView(),
            'event' => $event
        ]);
    }
}