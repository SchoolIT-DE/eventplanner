<?php

namespace App\Helper\ParticipationStatus;

use App\Entity\ParticipationStatus as ParticipationStatusEntity;
use App\Entity\User;

class ParticipationStatus {
    private $user;
    private $status;
    private $lastChange;

    public function __construct(User $user, $status = ParticipationStatusEntity::STATUS_PENDING, \DateTime $lastChange = null) {
        $this->user = $user;
        $this->status = $status;
        $this->lastChange = $lastChange;
    }

    /**
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastChange() {
        return $this->lastChange;
    }
}