<?php

namespace App\Event;

use App\Entity\Comment;
use Symfony\Contracts\EventDispatcher\Event;

class CommentCreatedEvent extends Event {
    private $comment;

    public function __construct(Comment $comment) {
        $this->comment = $comment;
    }

    public function getComment() {
        return $this->comment;
    }
}