<?php

namespace App\Controller;

use App\Entity\Message;
use App\Event\MessageEvent;
use App\Form\MessageType;
use App\Security\Voter\GroupVoter;
use App\Security\Voter\MessageVoter;
use SchoolIT\CommonBundle\Form\ConfirmType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MessageAdminController extends Controller {

    /**
     * @Route("/admin/messages", name="admin_messages")
     */
    public function add(Request $request) {
        $message = new Message();

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(GroupVoter::MAIL, $message->getGroup());
            $em = $this->getDoctrine()->getManager();

            $em->persist($message);
            $em->flush();

            $this->get('event_dispatcher')
                ->dispatch(MessageEvent::CREATED, new MessageEvent($message));

            $this->addFlash('success', 'messages.success');

            return $this->redirectToRoute('messages');
        }

        return $this->render('admin/messages/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/messages/{id}/remove", name="remove_message")
     */
    public function remove(Request $request, Message $message) {
        $this->denyAccessUnlessGranted(MessageVoter::REMOVE, $message);

        $form = $this->createForm(ConfirmType::class, null, [
            'message' => $this->get('translator')->trans('messages.remove.confirm', [
                '%date%' => $message->getCreatedAt()->format($this->get('translator')->trans('dateformat'))
            ])
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($message);
            $em->flush();

            $this->addFlash('success', 'messages.remove.success');
            return $this->redirectToRoute('messages');
        }

        return $this->render('admin/messages/remove.html.twig', [
            'form' => $form->createView()
        ]);
    }
}