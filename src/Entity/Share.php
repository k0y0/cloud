<?php

namespace App\Entity;

use App\Repository\ShareRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShareRepository::class)
 */
class Share
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
    private $uId;

    /**
     * @ORM\OneToOne(targetEntity=Package::class, inversedBy="shareLink", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $package;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUId(): ?string
    {
        return $this->uId;
    }

    public function setUId(string $uId): self
    {
        $this->uId = $uId;

        return $this;
    }

    public function getPackage(): ?Package
    {
        return $this->package;
    }

    public function setPackage(Package $package): self
    {
        $this->package = $package;

        return $this;
    }
}
