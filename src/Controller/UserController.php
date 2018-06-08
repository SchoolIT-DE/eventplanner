<?php

namespace App\Controller;

use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller {

    /**
     * @Route("/users/{id}", name="show_user")
     */
    public function show(User $user) {
        return $this->render('users/show.html.twig', [
            'actualUser' => $user
        ]);
    }
}