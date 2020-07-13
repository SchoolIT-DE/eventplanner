<?php

namespace App\Notification;

use App\Entity\Group;
use App\Entity\ParticipationStatus;
use App\Entity\User;
use App\Event\CommentCreatedEvent;
use App\Event\EventCreatedEvent;
use App\Helper\Ics\IcsHelper;
use App\Helper\SecurityTools;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class EmailNotificationListener implements EventSubscriberInterface {

    private $email_from;
    private $em;
    private $translator;
    private $twig;
    private $mailer;
    private $icsHelper;
    private $securityTools;
    private $logger;

    public function __construct(string $email_from, EntityManagerInterface $manager, TranslatorInterface $translator,
                                Environment $twig, Swift_Mailer $mailer, IcsHelper $icsHelper, SecurityTools $securityTools, LoggerInterface $logger) {
        $this->email_from = $email_from;
        $this->em = $manager;
        $this->translator = $translator;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->icsHelper = $icsHelper;
        $this->securityTools = $securityTools;
        $this->logger = $logger;
    }

    public function onEventCreated(EventCreatedEvent $eventEvent) {
        $this->logger->debug('onEventCreated() start');

        $event = $eventEvent->getEvent();

        $users = $this->getUsers($event->getGroups()->toArray());
        $ics = $this->icsHelper->getIcsContent([ $event ]);
        $currentLocale = $this->translator->getLocale();

        $this->logger->debug(sprintf('onEventCreated() sending mails to %d users', count($users)));

        foreach ($users as $user) {
            $status = $event->getParticipantionStatusFor($user);

            if($status === null) {
                $status = (new ParticipationStatus())
                    ->setEvent($event)
                    ->setUser($user)
                    ->setStatus(ParticipationStatus::STATUS_PENDING)
                    ->setLinkToken($this->securityTools->getRandom());

                $this->em->persist($status);
                $this->em->flush();
            }

            $subject = $this->translator->trans('comment.subject', [ '%event%' => $event->getName() ], 'mail');
            $content = $this->twig->render('mail/invitation.html.twig', [
                'users' => $user,
                'event' => $event,
                'status' => $status
            ]);

            $message = (new \Swift_Message())
                ->setSubject($subject)
                ->setContentType('text/html')
                ->setBody($content)
                ->setTo($user->getEmail())
                ->setFrom($this->email_from);

            $attachment = (new \Swift_Attachment())
                ->setFilename(sprintf('%s.ics', $event->getName()))
                ->setContentType('text/calendar')
                ->setBody($ics);
            $message->attach($attachment);

            foreach($event->getFiles() as $file) {
                $attachment = \Swift_Attachment::fromPath($file->getStorageName(), $file->getMimeType());
                $attachment->setFilename($file->getOriginalFilename());
                $message->attach($attachment);
            }

            $this->mailer->send($message);
        }

        $this->translator->setLocale($currentLocale);

        $this->logger->debug('onEventCreated() ended');
    }

    public function onCommentCreated(CommentCreatedEvent $commentEvent) {
        $this->logger->debug('onCommentCreated() started');

        $comment = $commentEvent->getComment();
        $event = $comment->getEvent();

        $users = $this->getUsers($event->getGroups()->toArray());

        $currentLocale = $this->translator->getLocale();

        $this->logger->debug(sprintf('onCommentCreated() sending mails to %d users', count($users)));

        foreach($users as $user) {
            $subject = $this->translator->trans('comment.subject', [ '%event%' => $event->getName() ], 'mail');
            $content = $this->twig->render('mail/new_comment.html.twig', [
                'users' => $user,
                'comment' => $comment
            ]);

            $message = (new \Swift_Message())
                ->setSubject($subject)
                ->setContentType('text/html')
                ->setBody($content)
                ->setTo($user->getEmail())
                ->setFrom($this->email_from);

            $this->mailer->send($message);
        }

        $this->translator->setLocale($currentLocale);
        $this->logger->debug('onCommentCreated() ended');
    }

    /**
     * @param Group[] $groups
     * @return User[]
     */
    private function getUsers(array $groups) {
        $users = [ ];

        foreach($groups as $group) {
            foreach($group->getMembers() as $member) {
                $users[$member->getId()] = $member;
            }
        }

        return $users;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents() {
        return [
            EventCreatedEvent::class => 'onEventCreated',
            CommentCreatedEvent::class => 'onCommentCreated'
        ];
    }
}