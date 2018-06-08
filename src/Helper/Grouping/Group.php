<?php

namespace App\Helper\Grouping;

class Group {
    private $key;
    private $items = [ ];

    public function __construct($key) {
        $this->key = $key;
    }

    public function getKey() {
        return $this->key;
    }

    public function getItems() {
        return $this->items;
    }

    public function addItem($item) {
        $this->items[] = $item;
    }
}