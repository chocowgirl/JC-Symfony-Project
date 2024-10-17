<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $activityDate = null;

    #[ORM\Column]
    private ?float $activityDistanceKm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $activityNote = null;

    #[ORM\Column(nullable: true)]
    private ?float $activityChronoMin = null;

    #[ORM\ManyToOne(inversedBy: 'activities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'shoeActivities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Shoepair $shoepairUsed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActivityDate(): ?\DateTimeInterface
    {
        return $this->activityDate;
    }

    public function setActivityDate(\DateTimeInterface $activityDate): static
    {
        $this->activityDate = $activityDate;

        return $this;
    }

    public function getActivityDistanceKm(): ?float
    {
        return $this->activityDistanceKm;
    }

    public function setActivityDistanceKm(float $activityDistanceKm): static
    {
        $this->activityDistanceKm = $activityDistanceKm;

        return $this;
    }

    public function getActivityNote(): ?string
    {
        return $this->activityNote;
    }

    public function setActivityNote(?string $activityNote): static
    {
        $this->activityNote = $activityNote;

        return $this;
    }

    public function getActivityChronoMin(): ?float
    {
        return $this->activityChronoMin;
    }

    public function setActivityChronoMin(?float $activityChronoMin): static
    {
        $this->activityChronoMin = $activityChronoMin;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getShoepairUsed(): ?Shoepair
    {
        return $this->shoepairUsed;
    }

    public function setShoepairUsed(?Shoepair $shoepairUsed): static
    {
        $this->shoepairUsed = $shoepairUsed;

        return $this;
    }
}
