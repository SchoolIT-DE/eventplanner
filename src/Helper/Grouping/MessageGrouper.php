<?php

namespace App\Helper\Grouping;

use App\Entity\Message;

class MessageGrouper extends AbstractGrouper {

    /**
     * @param Message[]
     * @return Group[]
     * @throws \Exception
     */
    public function groupMessagesByGroup(array $messages) {
        return $this->group($messages, function(Message $message) {
            return $message->getGroup();
        }, function($key) {
            return new Group($key);
        });
    }
}