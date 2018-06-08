<?php

namespace App\Controller;

use App\Entity\Group;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GroupController extends Controller {

    /**
     * @Route("/groups", name="groups")
     */
    public function index() {
        return $this->render('groups/index.html.twig', [
            'groups' => $this->getUser()->getGroups()
        ]);
    }

    /**
     * @Route("/groups/{id}", name="show_group")
     */
    public function show(Group $group) {
        return $this->render('groups/show.html.twig', [
            'group' => $group
        ]);
    }
}