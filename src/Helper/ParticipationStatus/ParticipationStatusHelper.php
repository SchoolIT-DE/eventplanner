<?php

namespace App\Helper\ParticipationStatus;

use App\Entity\Event;

class ParticipationStatusHelper {
    /**
     * @param Event $event
     * @return ParticipationStatus[]
     */
    public function getParticipationStatus(Event $event) {
        $users = [ ];

        foreach($event->getGroups() as $group) {
            foreach($group->getMembers() as $user) {
                $users[$user->getId()] = $user;
            }
        }

        /** @var \App\Entity\ParticipationStatus[] $participants */
        $participants = [ ];

        foreach($event->getParticipants() as $participant) {
            $participants[$participant->getUser()->getId()] = $participant;
        }

        $result = [ ];

        foreach($users as $userId => $user) {
            if(isset($participants[$userId])) {
                $status = $participants[$userId];
                $result[$userId] = new ParticipationStatus($user, $status->getStatus(), $status->getChangedAt());
            } else {
                $result[$userId] = new ParticipationStatus($user);
            }
        }

        return $result;
    }
}