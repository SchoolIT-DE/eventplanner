<?php

namespace App\Helper\Grouping;

use App\Entity\Event;
use App\Entity\Group as GroupEntity;

class EventGrouper {

    /**
     * @param Event[] $events
     * @return Group[]
     */
    public function groupByGroup(array $events) {
        $groups = [ ];

        $idFunc = function(GroupEntity $group) { return $group->getId(); };

        foreach($events as $event) {
            foreach($event->getGroups() as $group) {
                $groupId = $idFunc($group);

                if(!isset($groups[$groupId])) {
                    $groups[$groupId] = new Group($group->getName());
                }

                $groups[$groupId]->addItem($event);
            }
        }

        return $groups;
    }
}