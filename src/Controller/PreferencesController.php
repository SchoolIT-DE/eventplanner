<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PreferencesController extends AbstractController {
    /**
     * @Route("/preferences/language", name="language")
     */
    public function language(Request $request) {
        /** @var string[] $languages */
        $languages = $this->getParameter('locales');

        $choices = [ ];

        foreach($languages as $language) {
            $choices['translations.' . $language] = $language;
        }

        $user = $this->getUser();

        $form = $this->createFormBuilder($user)
            ->add('language', ChoiceType::class, [
                'label' => 'label.language',
                'choices' => $choices
            ])
            ->getForm();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'preferences.language.success');

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('preferences/language.html.twig', [
            'form' => $form->createView()
        ]);
    }
}