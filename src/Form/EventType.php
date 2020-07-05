<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\ParticipationStatus;
use App\Repository\GroupRepository;
use SchulIT\CommonBundle\Form\FieldsetType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EventType extends AbstractType {

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('group_general', FieldsetType::class, [
                'legend' => 'label.general',
                'fields' => function(FormBuilderInterface $builder) {
                    $builder
                        ->add('name', TextType::class, [
                            'label' => 'label.name'
                        ])
                        ->add('description', TextareaType::class, [
                            'label' => 'label.description'
                        ])
                        ->add('location', TextType::class, [
                            'label' => 'label.location'
                        ])
                        ->add('groups', EntityType::class, [
                            'class' => Group::class,
                            'label' => 'label.groups',
                            'multiple' => true,
                            'choice_label' => 'name',
                            'query_builder' => function(GroupRepository $repository) {
                                return $repository->findAllUserIsAdminOfQueryBuilder($this->tokenStorage->getToken()->getUser());
                            }
                        ]);
                }
            ])
            ->add('group_date', FieldsetType::class, [
                'legend' => 'label.date_time',
                'fields' => function(FormBuilderInterface $builder) {
                    $builder
                        ->add('start', DateTimeType::class, [
                            'label' => 'label.start',
                            'date_widget' => 'single_text',
                            'time_widget' => 'single_text'
                        ])
                        ->add('end', DateTimeType::class, [
                            'label' => 'label.end',
                            'date_widget' => 'single_text',
                            'time_widget' => 'single_text'
                        ]);
                }
            ])
            ->add('group_files', FieldsetType::class, [
                'legend' => 'label.files',
                'fields' => function(FormBuilderInterface $builder) {
                    $builder
                        ->add('files', FileType::class, [
                            'label' => 'label.files',
                            'multiple' => true,
                            'mapped' => false,
                            'required' => false
                        ]);
                }
            ])
            ->add('group_status', FieldsetType::class, [
                'legend' => 'label.status',
                'fields' => function(FormBuilderInterface $builder) {
                    $builder
                        ->add('status', ChoiceType::class, [
                            'choices' => $this->getStatusChoices(),
                            'label' => 'label.status',
                            'mapped' => false
                        ]);
                }
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $formEvent) {
            $form = $formEvent->getForm();
            $event = $formEvent->getData();

            if($event->getId() !== null) {
                $form->remove('group_files')
                    ->remove('group_status');
            }
        });
    }

    protected function getStatusChoices() {
        $status = [
            'status.pending' => ParticipationStatus::STATUS_PENDING,
            'status.accepted' => ParticipationStatus::STATUS_ACCEPTED,
            'status.declined' => ParticipationStatus::STATUS_DECLINED,
            'status.maybe' => ParticipationStatus::STATUS_MAYBE
        ];

        return $status;
    }

}