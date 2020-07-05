<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity()
 * @UniqueEntity(fields={"username", "calendarToken"})
 */
class User implements UserInterface {

    use IdTrait;
    use UuidTrait;

    /**
     * @ORM\Column(type="uuid")
     * @var UuidInterface
     */
    private $idpId;

    /**
     * @ORM\Column(type="string", length=191, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string")
     */
    private $firstname;

    /**
     * @ORM\Column(type="string")
     */
    private $lastname;

    /**
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [ 'ROLE_USER' ];

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $mobile;

    /**
     * @ORM\Column(type="string", length=128, nullable=true, unique=true)
     */
    private $calendarToken;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isMailOnNewEventEnabled = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isMailOnNewCommentEnabled = false;

    /**
     * @ORM\ManyToMany(targetEntity="Group", mappedBy="members")
     */
    private $groups;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    private $language;

    /**
     * @ORM\Column(type="json")
     * @var string[]
     */
    private $data = [ ];

    public function __construct() {
        $this->uuid = Uuid::uuid4();
        $this->groups = new ArrayCollection();
    }

    /**
     * @return UuidInterface|null
     */
    public function getIdpId(): ?UuidInterface {
        return $this->idpId;
    }

    /**
     * @param UuidInterface $uuid
     * @return User
     */
    public function setIdpId(UuidInterface $uuid): User {
        $this->idpId = $uuid;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname() {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     * @return User
     */
    public function setFirstname($firstname) {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastname() {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     * @return User
     */
    public function setLastname($lastname) {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * @param string $address
     * @return User
     */
    public function setAddress($address) {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return User
     */
    public function setPhone($phone) {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getMobile() {
        return $this->mobile;
    }

    /**
     * @param string $mobile
     * @return User
     */
    public function setMobile($mobile) {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCalendarToken() {
        return $this->calendarToken;
    }

    /**
     * @param string|null $calendarToken
     * @return User
     */
    public function setCalendarToken($calendarToken) {
        $this->calendarToken = $calendarToken;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isMailOnNewEventEnabled() {
        return $this->isMailOnNewEventEnabled;
    }

    /**
     * @param boolean $isMailOnNewEventEnabled
     * @return User
     */
    public function setIsMailOnNewEventEnabled($isMailOnNewEventEnabled) {
        $this->isMailOnNewEventEnabled = $isMailOnNewEventEnabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isMailOnNewCommentEnabled() {
        return $this->isMailOnNewCommentEnabled;
    }

    /**
     * @param boolean $isMailOnNewCommentEnabled
     * @return User
     */
    public function setIsMailOnNewCommentEnabled($isMailOnNewCommentEnabled) {
        $this->isMailOnNewCommentEnabled = $isMailOnNewCommentEnabled;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups() {
        return $this->groups;
    }

    public function addGroup(Group $group) {
        $this->groups->add($group);
    }

    public function removeGroup(Group $group) {
        $this->groups->removeElement($group);
    }

    /**
     * @return string
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * @param string $language
     * @return User
     */
    public function setLanguage($language) {
        $this->language = $language;
        return $this;
    }

    public function getData(string $key, $default = null) {
        return $this->data[$key] ?? $default;
    }

    public function setData(string $key, $data): void {
        $this->data[$key] = $data;
    }


    /**
     * @inheritDoc
     */
    public function getRoles() {
        return $this->roles;
    }

    /**
     * @param string[] $roles
     * @return User
     */
    public function setRoles(array $roles) {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPassword() {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getSalt() {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @param string $username
     * @return User
     */
    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials() { }
}