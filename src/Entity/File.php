<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @Gedmo\Uploadable(allowOverwrite=false, appendNumber=true, filenameGenerator="SHA1")
 */
class File {

    use IdTrait;
    use UuidTrait;

    /**
     * @ORM\Column(type="string", name="original_filename")
     * @Assert\NotBlank()
     */
    private $originalFilename;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Gedmo\UploadableFileName()
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     * @Gedmo\UploadableFilePath()
     */
    private $storageName;

    /**
     * @ORM\Column(type="string")
     * @Gedmo\UploadableFileMimeType()
     */
    private $mimeType;

    /**
     * @ORM\Column(type="decimal")
     * @Gedmo\UploadableFileSize()
     */
    private $size;

    /**
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="files")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $event;

    public function __construct() {
        $this->uuid = Uuid::uuid4();
    }

    /**
     * @return string
     */
    public function getOriginalFilename() {
        return $this->originalFilename;
    }

    /**
     * @param string $filename
     * @return File
     */
    public function setOriginalFilename($filename) {
        $this->originalFilename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getMimeType() {
        return $this->mimeType;
    }

    /**
     * @return int
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getStorageName() {
        return $this->storageName;
    }

    /**
     * @return Event
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * @param Event $event
     * @return File
     */
    public function setEvent(Event $event) {
        $this->event = $event;
        return $this;
    }
}