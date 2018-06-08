<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;

class PreferencesController extends Controller {
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

            return $this->redirectToRoute('language');
        }

        return $this->render('preferences/language.html.twig', [
            'form' => $form->createView()
        ]);
    }
}