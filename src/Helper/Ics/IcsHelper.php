<?php

namespace App\Helper\Ics;

use App\Entity\Event;
use App\Entity\ParticipationStatus as ParticipationStatusEntity;
use App\Helper\ParticipationStatus\ParticipationStatus;
use App\Helper\ParticipationStatus\ParticipationStatusHelper;

class IcsHelper {
    const ICS_FORMAT = 'Ymd\THis';

    private $timezone;
    private $participationStatusHelper;

    public function __construct($timezone, ParticipationStatusHelper $participationStatusHelper) {
        $this->timezone = $timezone;
        $this->participationStatusHelper = $participationStatusHelper;
    }

    public function getIcsContent(array $events, $addAttendees = true) {
        $template = <<<ICS
BEGIN:VCALENDAR
PRODID:PHP
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:REQUEST
X-WR-TIMEZONE:%s
ICS;

        $ics = sprintf($template, $this->timezone);

        foreach($events as $event) {
            $ics .= $this->getEventIcs($event, $addAttendees);
        }

        $ics .= "END:VCALENDAR";

        return $ics;
    }

    private function getEventIcs(Event $event, $addAttendees = true) {
        $description = $event->getDescription();
        $description = str_replace("\r", "", $description);
        $description = str_replace("\n", "\\n", $description);

        $participants = $this->participationStatusHelper->getParticipationStatus($event);

        $body = "BEGIN:VEVENT\r\n".
            "ORGANIZER".$this->getParticipationStatus($participants[$event->getCreatedBy()->getId()]).";CN=\"".$event->getCreatedBy()->getLastname().", ". $event->getCreatedBy()->getFirstname() ."\":MAILTO:".$event->getCreatedBy()->getEmail()."\r\n";

        if($addAttendees === true) {
            foreach ($participants as $userId => $status) {
                $body .= "ATTENDEE" . $this->getParticipationStatus($status) . ";CN=\"" . $status->getUser()->getLastname() . ", " . $status->getUser()->getFirstname() . "\":MAILTO:" . $status->getUser()->getEmail() . "\r\n";
            }
        }

        $body .= "DTSTART;TZID=".$this->timezone.":".$this->getDate($event->getStart())."\r\n";
        $body .= "DTEND;TZID=".$this->timezone.":".$this->getDate($event->getEnd())."\r\n";
        $body .= "DESCRIPTION:".$description."\r\n";
        $body .= "SUMMARY:".$event->getName()."\r\n";
        $body .= "LOCATION:".$event->getLocation()."\r\n";
        $body .= "UID:".sha1($event->getId())."\r\n";
        $body .= "SEQUENCE:0\r\n";
        $body .= "DTSTAMP:".$this->getDate()."\r\n";
        $body .= "END:VEVENT\r\n";

        return $body;
    }

    private function getParticipationStatus(ParticipationStatus $status = null) {
        if($status === null) {
            return '';
        }

        switch ($status->getStatus()) {
            case ParticipationStatusEntity::STATUS_ACCEPTED:
                return 'ACCEPTED';

            case ParticipationStatusEntity::STATUS_DECLINED:
                return 'DECLINED';

            case ParticipationStatusEntity::STATUS_MAYBE:
                return 'TENTATIVE';
        }

        return '';
    }

    private function getDate(\DateTime $dateTime = null) {
        if($dateTime === null) {
            $dateTime = new \DateTime('now');
        }

        return $dateTime->format(static::ICS_FORMAT);
    }
}