<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupRepository")
 * @ORM\Table(name="`group`")
 */
class Group {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="groups")
     * @ORM\JoinTable(name="group_members")
     */
    private $members;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="group_admins")
     */
    private $admins;

    public function __construct() {
        $this->members = new ArrayCollection();
        $this->admins = new ArrayCollection();
    }

    /**
     * @return mixed
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
     * @return Group
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getMembers() {
        return $this->members;
    }

    public function addMember(User $user) {
        $this->members->add($user);
    }

    public function removeMember(User $user) {
        $this->members->removeElement($user);
    }

    /**
     * @return ArrayCollection
     */
    public function getAdmins() {
        return $this->admins;
    }

    public function addAdmin(User $user) {
        $this->admins->add($user);
    }

    public function removeAdmin(User $user) {
        $this->admins->removeElement($user);
    }
}