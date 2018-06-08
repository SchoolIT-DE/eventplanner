<?php

namespace App\Event;

use App\Entity\Comment;
use Symfony\Component\EventDispatcher\Event;

class CommentEvent extends Event {
    const CREATED = 'vp.comment.created';

    private $comment;

    public function __construct(Comment $comment) {
        $this->comment = $comment;
    }

    public function getComment() {
        return $this->comment;
    }
}