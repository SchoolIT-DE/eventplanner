<?php

namespace App\Controller;

use App\Entity\Message;
use App\Helper\Grouping\Group;
use App\Helper\Grouping\MessageGrouper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MessageController extends Controller {

    /**
     * @Route("/messages", name="messages")
     */
    public function index(MessageGrouper $messageGrouper) {
        /** @var Message[] $messages */
        $messages = $this->getDoctrine()->getManager()
            ->getRepository(Message::class)
            ->findAllForUser($this->getUser());

        /** @var Group[] $groups */
        $groups = $messageGrouper->groupMessagesByGroup($messages);

        return $this->render('messages/index.html.twig', [
            'groups' => $groups
        ]);
    }
}