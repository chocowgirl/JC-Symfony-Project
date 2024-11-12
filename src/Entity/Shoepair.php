<?php

namespace App\Entity;

use App\Repository\ShoepairRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShoepairRepository::class)]
class Shoepair
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nameBrandModel = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $startDateOfUse = null;

    #[ORM\Column]
    private ?float $wearLimitKm = null;

    #[ORM\Column]
    private ?float $currentWearKm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shoeNote = null;

    #[ORM\Column]
    private ?bool $inActiveService = null;

    #[ORM\ManyToOne(inversedBy: 'shoepairs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userOwner = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'shoepairUsed', orphanRemoval: true)]
    private Collection $shoeActivities;

    public function __construct()
    {
        $this->shoeActivities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameBrandModel(): ?string
    {
        return $this->nameBrandModel;
    }

    public function setNameBrandModel(string $nameBrandModel): static
    {
        $this->nameBrandModel = $nameBrandModel;

        return $this;
    }

    public function getStartDateOfUse(): ?\DateTimeInterface
    {
        return $this->startDateOfUse;
    }

    public function setStartDateOfUse(\DateTimeInterface $startDateOfUse): static
    {
        $this->startDateOfUse = $startDateOfUse;

        return $this;
    }

    public function getWearLimitKm(): ?float
    {
        return $this->wearLimitKm;
    }

    public function setWearLimitKm(float $wearLimitKm): static
    {
        $this->wearLimitKm = $wearLimitKm;

        return $this;
    }

    public function getCurrentWearKm(): ?float
    {
        return $this->currentWearKm;
    }

    public function setCurrentWearKm(float $currentWearKm): static
    {
        $this->currentWearKm = $currentWearKm;

        return $this;
    }

    public function getShoeNote(): ?string
    {
        return $this->shoeNote;
    }

    public function setShoeNote(?string $shoeNote): static
    {
        $this->shoeNote = $shoeNote;

        return $this;
    }

    public function isInActiveService(): ?bool
    {
        return $this->inActiveService;
    }

    public function setInActiveService(bool $inActiveService): static
    {
        $this->inActiveService = $inActiveService;

        return $this;
    }

    public function getUserOwner(): ?User
    {
        return $this->userOwner;
    }

    public function setUserOwner(?User $userOwner): static
    {
        $this->userOwner = $userOwner;

        return $this;
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getShoeActivities(): Collection
    {
        return $this->shoeActivities;
    }

    public function addShoeActivity(Activity $shoeActivity): static
    {
        if (!$this->shoeActivities->contains($shoeActivity)) {
            $this->shoeActivities->add($shoeActivity);
            $shoeActivity->setShoepairUsed($this);
        }

        return $this;
    }

    public function removeShoeActivity(Activity $shoeActivity): static
    {
        if ($this->shoeActivities->removeElement($shoeActivity)) {
            // set the owning side to null (unless already changed)
            if ($shoeActivity->getShoepairUsed() === $this) {
                $shoeActivity->setShoepairUsed(null);
            }
        }

        return $this;
    }
}
