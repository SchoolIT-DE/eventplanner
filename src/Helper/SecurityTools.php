<?php

namespace App\Helper;

class SecurityTools {
    public function getRandom() {
        return hash('sha512', random_bytes(50));
    }
}