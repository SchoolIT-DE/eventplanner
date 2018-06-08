<?php

namespace App\Event;

use App\Entity\Message;
use Symfony\Component\EventDispatcher\Event;

class MessageEvent extends Event {
    const CREATED = 'vp.message.created';

    private $message;

    public function __construct(Message $message) {
        $this->message = $message;
    }

    public function getMessage() {
        return $this->message;
    }

}