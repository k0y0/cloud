<?php

namespace App\Entity;

use App\Repository\PackageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PackageRepository::class)
 */
class Package
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isShared;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="packages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ownerId;

    /**
     * @ORM\ManyToMany(targetEntity=File::class, inversedBy="packages")
     */
    private $files;

    /**
     * @ORM\ManyToMany(targetEntity=Folder::class, inversedBy="packages")
     */
    private $folders;

    /**
     * @ORM\OneToOne(targetEntity=Share::class, mappedBy="package", cascade={"persist", "remove"})
     */
    private $shareLink;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->folders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOwnerId(): ?User
    {
        return $this->ownerId;
    }

    public function setOwnerId(?User $ownerId): self
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    /**
     * @return Collection|File[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }

    /**
     * @return Collection|Folder[]
     */
    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function addFolder(Folder $folder): self
    {
        if (!$this->folders->contains($folder)) {
            $this->folders[] = $folder;
        }

        return $this;
    }

    public function removeFolder(Folder $folder): self
    {
        $this->folders->removeElement($folder);

        return $this;
    }

    public function getShareLink(): ?Share
    {
        return $this->shareLink;
    }

    public function setShareLink(Share $shareLink): self
    {
        // set the owning side of the relation if necessary
        if ($shareLink->getPackage() !== $this) {
            $shareLink->setPackage($this);
        }

        $this->shareLink = $shareLink;

        return $this;
    }
}
