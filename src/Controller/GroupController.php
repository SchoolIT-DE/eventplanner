<?php

namespace App\Controller;

use App\Entity\Group;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController {

    /**
     * @Route("/groups", name="groups")
     */
    public function index() {
        return $this->render('groups/index.html.twig', [
            'groups' => $this->getUser()->getGroups()
        ]);
    }

    /**
     * @Route("/groups/{uuid}", name="show_group")
     */
    public function show(Group $group) {
        return $this->render('groups/show.html.twig', [
            'group' => $group
        ]);
    }
}