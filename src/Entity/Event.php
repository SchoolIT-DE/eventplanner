<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 */
class Event {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="`name`")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull()
     */
    private $start;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull()
     */
    private $end;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="created_by")
     * @Gedmo\Blameable(on="create")
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\ManyToMany(targetEntity="Group")
     * @ORM\JoinTable("event_groups")
     */
    private $groups;

    /**
     * @ORM\OneToMany(targetEntity="ParticipationStatus", mappedBy="event")
     * @ORM\JoinTable("event_participants")
     */
    private $participants;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="event")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="event", cascade={"persist"})
     */
    private $files;

    public function __construct() {
        $this->groups = new ArrayCollection();
        $this->participants = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Event
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Event
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStart() {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     * @return Event
     */
    public function setStart(\DateTime $start) {
        $this->start = $start;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd() {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return Event
     */
    public function setEnd( \DateTime$end) {
        $this->end = $end;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocation() {
        return $this->location;
    }

    /**
     * @param string $location
     * @return Event
     */
    public function setLocation($location) {
        $this->location = $location;
        return $this;
    }

    /**
     * @return User
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     * @return Event
     */
    public function setCreatedBy(User $createdBy) {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Event
     */
    public function setCreatedAt(\DateTime $createdAt) {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroups() {
        return $this->groups;
    }

    /**
     * @param mixed $groups
     * @return Event
     */
    public function setGroups($groups) {
        $this->groups = $groups;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParticipants() {
        return $this->participants;
    }

    public function addParticipant(ParticipationStatus $status) {
        $this->participants->add($status);
    }

    public function removeParticipant(ParticipationStatus $status) {
        $this->participants->removeElement($status);
    }

    public function getParticipantionStatusFor(User $user) {
        foreach($this->getParticipants() as $status) {
            if($status->getUser()->getId() === $user->getId()) {
                return $status;
            }
        }

        return null;
    }

    public function hasParticipantStatus(User $user) {
        return $this->getParticipantionStatusFor($user) !== null;
    }

    /**
     * @return ArrayCollection
     */
    public function getComments() {
        return $this->comments;
    }

    public function addComment(Comment $comment) {
        $this->comments->add($comment);
    }

    public function removeComment(Comment $comment) {
        $this->comments->removeElement($comment);
    }

    /**
     * @return ArrayCollection
     */
    public function getFiles() {
        return $this->files;
    }

    public function addFile(File $file) {
        $this->files->add($file);
    }

    public function removeFile(File $file) {
        $this->files->removeElement($file);
    }
}