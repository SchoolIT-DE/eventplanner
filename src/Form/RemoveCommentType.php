<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;

class RemoveCommentType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('confirm', CheckboxType::class, [
                'label' => 'event.comments.remove.confirm',
                'required' => true,
                'data' => false,
                'constraints' => [
                    new IsTrue()
                ]
            ]);
    }
}