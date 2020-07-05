<?php

namespace App\Twig;

use App\Entity\ParticipationStatus;
use App\Entity\User;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class VpExtension extends AbstractExtension {
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function getFilters() {
        return [
            new TwigFilter('shortdate', [ $this, 'shortdate' ]),
            new TwigFilter('status', [ $this, 'status' ]),
            new TwigFilter('user', [ $this, 'user' ])
        ];
    }

    public function user(User $user) {
        return sprintf('%s, %s (%s)', $user->getLastname(), $user->getFirstname(), $user->getUsername());
    }

    public function status($status) {
        switch($status) {
            case ParticipationStatus::STATUS_PENDING:
                return $this->translator->trans('status.pending');

            case ParticipationStatus::STATUS_ACCEPTED:
                return $this->translator->trans('status.accepted');

            case ParticipationStatus::STATUS_DECLINED:
                return $this->translator->trans('status.declined');

            case ParticipationStatus::STATUS_MAYBE:
                return $this->translator->trans('status.maybe');
        }

        throw new \LogicException('This code should not be executed');
    }

    public function shortdate(\DateTime $dateTime) {
        return $dateTime->format('d.m.Y H:i');
    }

}