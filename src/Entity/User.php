<?php

namespace App\Entity;

use App\Helper\SizesHelper;
use App\Repository\UserRepository;
use App\Service\UsedSpaceChecker;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="Istnieje konto w systemie o podanym email")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=File::class, mappedBy="owner")
     */
    private $files;

    /**
     * @ORM\ManyToMany(targetEntity=File::class, mappedBy="shared")
     */
    private $shared;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $diskLimit;

    /**
     * @ORM\OneToMany(targetEntity=Folder::class, mappedBy="owner", orphanRemoval=true)
     */
    private $folders;

    /**
     * @ORM\OneToMany(targetEntity=Package::class, mappedBy="ownerId", orphanRemoval=true)
     */
    private $packages;

    private $diskLimitHuman;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->shared = new ArrayCollection();
        $this->folders = new ArrayCollection();
        $this->packages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
            $file->setOwner($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getOwner() === $this) {
                $file->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|File[]
     */
    public function getShared(): Collection
    {
        return $this->shared;
    }

    public function addShared(File $shared): self
    {
        if (!$this->shared->contains($shared)) {
            $this->shared[] = $shared;
            $shared->addShared($this);
        }

        return $this;
    }

    public function removeShared(File $shared): self
    {
        if ($this->shared->removeElement($shared)) {
            $shared->removeShared($this);
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getDiskLimit(): ?string
    {
        return $this->diskLimit;
    }

    public function setDiskLimit(string $diskLimit): self
    {
        $this->diskLimit = $diskLimit;

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
            $folder->setOwner($this);
        }

        return $this;
    }

    public function removeFolder(Folder $folder): self
    {
        if ($this->folders->removeElement($folder)) {
            // set the owning side to null (unless already changed)
            if ($folder->getOwner() === $this) {
                $folder->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Package[]
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    public function addPackage(Package $package): self
    {
        if (!$this->packages->contains($package)) {
            $this->packages[] = $package;
            $package->setOwnerId($this);
        }

        return $this;
    }

    public function removePackage(Package $package): self
    {
        if ($this->packages->removeElement($package)) {
            // set the owning side to null (unless already changed)
            if ($package->getOwnerId() === $this) {
                $package->setOwnerId(null);
            }
        }

        return $this;
    }


    public function getDiskLimitArr(): ?array
    {
        $t = new UsedSpaceChecker();
        return $t->getUsedSpace($this);
    }
}
