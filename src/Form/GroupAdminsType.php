<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class GroupAdminsType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('admins', EntityType::class, [
                'label' => 'label.admins',
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return sprintf('%s, %s (%s)', $user->getLastname(), $user->getFirstname(), $user->getUsername());
                },
                'multiple' => true,
                'expanded' => false
            ]);
    }
}