<?php

namespace App\Notification;

use App\Entity\Group;
use App\Entity\ParticipationStatus;
use App\Entity\User;
use App\Event\CommentEvent;
use App\Event\EventEvent;
use App\Event\MessageEvent;
use App\Helper\Ics\IcsHelper;
use App\Helper\SecurityTools;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class EmailNotificationListener implements EventSubscriberInterface {

    private $email_from;
    private $om;
    private $translator;
    private $twig;
    private $mailer;
    private $icsHelper;
    private $securityTools;
    private $logger;

    public function __construct(string $email_from, ObjectManager $objectManager, TranslatorInterface $translator, \Twig_Environment $twig, \Swift_Mailer $mailer, IcsHelper $icsHelper, SecurityTools $securityTools, LoggerInterface $logger = null) {
        $this->email_from = $email_from;
        $this->om = $objectManager;
        $this->translator = $translator;
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->icsHelper = $icsHelper;
        $this->securityTools = $securityTools;
        $this->logger = $logger ?? new NullLogger();
    }

    public function onEventCreated(EventEvent $eventEvent) {
        $this->logger->debug('onEventCreated() start');

        $event = $eventEvent->getEvent();

        $users = $this->getUsers($event->getGroups()->toArray());
        $ics = $this->icsHelper->getIcsContent([ $event ]);
        $currentLocale = $this->translator->getLocale();

        $this->logger->debug(sprintf('onEventCreated() sending mails to %d users', count($users)));

        foreach ($users as $user) {
            if($user->getLanguage() === null) {
                continue;
            }

            $status = $event->getParticipantionStatusFor($user);

            if($status === null) {
                $status = (new ParticipationStatus())
                    ->setEvent($event)
                    ->setUser($user)
                    ->setStatus(ParticipationStatus::STATUS_PENDING)
                    ->setLinkToken($this->securityTools->getRandom());

                $this->om->persist($status);
                $this->om->flush();
            }

            $this->translator->setLocale($user->getLanguage());

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

    public function onCommentCreated(CommentEvent $commentEvent) {
        $this->logger->debug('onCommentCreated() started');

        $comment = $commentEvent->getComment();
        $event = $comment->getEvent();

        $users = $this->getUsers($event->getGroups()->toArray());

        $currentLocale = $this->translator->getLocale();

        $this->logger->debug(sprintf('onCommentCreated() sending mails to %d users', count($users)));

        foreach($users as $user) {
            if($user->getLanguage() === null) {
                continue;
            }

            $this->translator->setLocale($user->getLanguage());

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

    public function onMessageCreated(MessageEvent $messageEvent) {
        $this->logger->debug('onMessageCreated() started');

        $message = $messageEvent->getMessage();
        $group = $message->getGroup();

        $isGlobalMessage = $group === null;

        if($isGlobalMessage) {
            /** @var User[] $users */
            $users = $group->getMembers();
        } else {
            /** @var User[] $users */
            $users = $this->om
                ->getRepository(User::class)
                ->findAll();
        }

        $currentLocale = $this->translator->getLocale();

        $this->logger->debug(sprintf('onMessageCreated() sending mails to %d users', count($users)));

        foreach($users as $user) {
            if($user->getLanguage() === null) {
                continue;
            }

            $this->translator->setLocale($user->getLanguage());

            if($isGlobalMessage) {
                $subject = $this->translator->trans('message.global.subject', [ ], 'mail');
            } else {
                $subject = $this->translator->trans('message.group.subject', [ '%group%' => $group->getName() ], 'mail');
            }

            $content = $this->twig->render('mail/new_message.html.twig', [
                'users' => $user,
                'group' => $group,
                'message' => $message,
                'isGlobalMessage' => $isGlobalMessage
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
        $this->logger->debug('onMessageCreated() ended');
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
            EventEvent::CREATED => 'onEventCreated',
            CommentEvent::CREATED => 'onCommentCreated',
            MessageEvent::CREATED => 'onMessageCreated'
        ];
    }
}