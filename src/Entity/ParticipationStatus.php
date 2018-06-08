<?php

namespace App\Entity;

use App\Helper\ParticipationStatus\ParticipationStatusHelper;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 */
class ParticipationStatus {
    const STATUS_PENDING = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_DECLINED = 3;
    const STATUS_MAYBE = 4;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="participants")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $event;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $changedAt;

    /**
     * @ORM\Column(type="string", length=128, unique=true)
     */
    private $linkToken;

    /**
     * @return Event
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * @param Event $event
     * @return ParticipationStatus
     */
    public function setEvent(Event $event) {
        $this->event = $event;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param User $user
     * @return ParticipationStatus
     */
    public function setUser(User $user) {
        $this->user = $user;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @param int $status
     * @return ParticipationStatus
     */
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getChangedAt() {
        return $this->changedAt;
    }

    /**
     * @param \DateTime $changedAt
     * @return ParticipationStatus
     */
    public function setChangedAt(\DateTime $changedAt) {
        $this->changedAt = $changedAt;
        return $this;
    }

    public function getLinkToken() {
        return $this->linkToken;
    }

    /**
     * @param $linkToken
     * @return ParticipationStatus
     */
    public function setLinkToken($linkToken) {
        $this->linkToken = $linkToken;
        return $this;
    }

    public static function isValidStatus($status) {
        return is_numeric($status)
            && in_array($status, [ 1, 2, 3, 4]);
    }
}