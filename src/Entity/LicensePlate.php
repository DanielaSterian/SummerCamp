<?php

namespace App\Entity;

use App\Repository\LicensePlateRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Annotations;

/**
 * @ORM\Entity(repositoryClass=LicensePlateRepository::class)
 * @ORM\Table(name="license_plate",
 * uniqueConstraints={@ORM\UniqueConstraint(name="unique_idx", fields={"licensePlate", "user"})}
 *     ,indexes={
 *   @ORM\Index(name="license_idx", columns={"license_plate","user_id"})
 * })
 */
class LicensePlate
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
    private $licensePlate;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="licensePlates")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLicensePlate(): ?string
    {
        return $this->licensePlate;
    }

    public function setLicensePlate(string $licensePlate): self
    {
        $this->licensePlate = $licensePlate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function __toString(): string
    {
        return $this->licensePlate;
    }
}
