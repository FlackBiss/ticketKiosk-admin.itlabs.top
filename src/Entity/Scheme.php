<?php

namespace App\Entity;

use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\SchemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SchemeRepository::class)]
class Scheme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['scheme:read'])]
    private int $id;

    #[ORM\Column(length: 255)]
    #[Groups(['scheme:read'])]
    private string $name;

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['scheme:read'])]
    private ?array $scheme = null;

    /**
     * @var Collection<int, SchemePlace>
     */
    #[ORM\OneToMany(targetEntity: SchemePlace::class, mappedBy: 'scheme')]
    private Collection $places;

    public function __construct()
    {
        $this->places = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Scheme
     */
    public function setId(int $id): Scheme
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Scheme
     */
    public function setName(string $name): Scheme
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection<int, SchemePlace>
     */
    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function addPlace(SchemePlace $place): static
    {
        if (!$this->places->contains($place)) {
            $this->places->add($place);
            $place->setScheme($this);
        }

        return $this;
    }

    public function removePlace(SchemePlace $place): static
    {
        if ($this->places->removeElement($place)) {
            // set the owning side to null (unless already changed)
            if ($place->getScheme() === $this) {
                $place->setScheme(null);
            }
        }

        return $this;
    }

    /**
     * @return array|null
     */
    public function getScheme(): ?array
    {
        return $this->scheme;
    }

    /**
     * @param array|null $scheme
     * @return Scheme
     */
    public function setScheme(?array $scheme): Scheme
    {
        $this->scheme = $scheme;
        return $this;
    }
}