<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('isMailOnNewEventEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'profile.notifications.new_event.label',
                'help' => 'profile.notifications.new_event.help',
                'label_attr' => [
                    'class' => 'checkbox-custom'
                ]
            ])
            ->add('isMailOnNewCommentEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'profile.notifications.new_comment.label',
                'help' => 'profile.notifications.new_comment.help',
                'label_attr' => [
                    'class' => 'checkbox-custom'
                ]
            ]);
    }
}