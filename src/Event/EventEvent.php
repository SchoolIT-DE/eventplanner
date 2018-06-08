<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;
use App\Entity\Event as EventEntity;

class EventEvent extends Event {
    const CREATED = 'vp.event.created';

    private $event;

    public function __construct(EventEntity $event) {
        $this->event = $event;
    }

    public function getEvent() {
        return $this->event;
    }
}