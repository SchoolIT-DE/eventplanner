<?php

namespace App\Helper\Grouping;

abstract class AbstractGrouper {

    /**
     * @param array $items
     * @param \Closure $selector
     * @param \Closure $groupFactory
     * @return Group[]
     * @throws \Exception
     */
    protected function group(array $items, \Closure $selector, \Closure $groupFactory) {
        /** @var Group[] $groups */
        $groups = [ ];

        foreach($items as $item) {
            $keys = $selector($item);

            if(!is_array($keys)) {
                $keys = [ $keys ];
            }

            foreach($keys as $key) {
                $group = null;

                foreach($groups as $g) {
                    if($g->getKey() == $key) {
                        $group = $g;
                    }
                }

                if($group === null) {
                    $group = $groupFactory($key);

                    if(!$group instanceof Group) {
                        throw new \Exception('$groupFactory must return instance of Group');
                    }

                    $groups[] = $group;
                }

                $group->addItem($item);
            }
        }

        return $groups;
    }
}