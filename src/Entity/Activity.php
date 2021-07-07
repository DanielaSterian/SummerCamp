<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Annotations;
use App\Repository\ActivityRepository;

/**
 * @ORM\Entity(repositoryClass=ActivityRepository::class)
 */
class Activity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $blocker;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $blockee;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $status;

    public function __construct()
    {
        $this->status = 0;
    }

    public function getBlocker(): ?string
    {
        return $this->blocker;
    }

    public function setBlocker(string $blocker): self
    {
        $this->blocker = $blocker;

        return $this;
    }

    public function getBlockee(): ?string
    {
        return $this->blockee;
    }

    public function setBlockee(string $blockee): self
    {
        $this->blockee = $blockee;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): int
    {
        $this->status = $status;

        return $status;
    }
}
