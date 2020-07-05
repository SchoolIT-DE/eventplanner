<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController {

    /**
     * @Route("/users/{uuid}", name="show_user")
     */
    public function show(User $user) {
        return $this->render('users/show.html.twig', [
            'actualUser' => $user
        ]);
    }
}