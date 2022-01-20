<?php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FileRepository::class)
 */
class File
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mimeType;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $uploadetAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="files")
     */
    private $owner;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filesize;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="shared")
     */
    private $shared;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isShared;

    /**
     * @ORM\ManyToOne(targetEntity=Folder::class, inversedBy="files")
     */
    private $folder;

    public function __construct()
    {
        $this->shared = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getUploadetAt(): ?\DateTimeImmutable
    {
        return $this->uploadetAt;
    }

    public function setUploadetAt(\DateTimeImmutable $uploadetAt): self
    {
        $this->uploadetAt = $uploadetAt;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getFilesize(): ?string
    {
        return $this->filesize;
    }

    public function setFilesize(string $filesize): self
    {
        $this->filesize = $filesize;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getShared(): Collection
    {
        return $this->shared;
    }

    public function addShared(User $shared): self
    {
        if (!$this->shared->contains($shared)) {
            $this->shared[] = $shared;
        }

        return $this;
    }

    public function removeShared(User $shared): self
    {
        $this->shared->removeElement($shared);

        return $this;
    }

    public function getIsShared(): ?bool
    {
        return $this->isShared;
    }

    public function setIsShared(bool $isShared): self
    {
        $this->isShared = $isShared;

        return $this;
    }

    public function getFolder(): ?Folder
    {
        return $this->folder;
    }

    public function setFolder(?Folder $folder): self
    {
        $this->folder = $folder;

        return $this;
    }
}
