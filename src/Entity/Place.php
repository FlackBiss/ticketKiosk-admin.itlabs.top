<?php

namespace App\Entity;

use ApiPlatform\Metadata\GetCollection;
use App\Repository\PlaceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[GetCollection(normalizationContext: ['groups' => ['place:read']])]
#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['scheme:read', 'place:read'])]
    private ?int $id;

    #[ORM\Column(length: 255)]
    #[Groups(['scheme:read', 'place:read'])]
    private ?string $name;

    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(['scheme:read', 'place:read'])]
    private ?int $price;

    #[ORM\Column(length: 255)]
    #[Groups(['scheme:read', 'place:read'])]
    private ?string $color = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(?int $id): Place
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): Place
    {
        $this->name = $name;
        return $this;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(?int $price): Place
    {
        $this->price = $price;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }
}