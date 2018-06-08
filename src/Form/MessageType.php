<?php

namespace App\Form;

use App\Entity\Group;
use App\Repository\GroupRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MessageType extends AbstractType {

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('group', EntityType::class, [
                'required' => true,
                'label' => 'label.group',
                'choice_label' => function(Group $group) {
                    return $group->getName();
                },
                'class' => Group::class,
                'query_builder' => function(GroupRepository $repository) {
                    return $repository->findAllUserIsAdminOfQueryBuilder($this->tokenStorage->getToken()->getUser());
                }
            ])
            ->add('content', TextareaType::class, [
                'label' => 'label.content'
            ]);
    }
}