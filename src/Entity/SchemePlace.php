<?php

namespace App\Entity;

use App\Repository\SchemePlaceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SchemePlaceRepository::class)]
class SchemePlace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['scheme:read'])]
    private int $id;

    #[ORM\Column(length: 255)]
    #[Groups(['scheme:read'])]
    private string $name;

    #[ORM\Column(type: Types::FLOAT)]
    #[Groups(['scheme:read'])]
    private float $price;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['scheme:read'])]
    private ?array $point;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['scheme:read'])]
    private ?array $area;

    #[ORM\ManyToOne(inversedBy: 'places')]
    private ?Scheme $scheme = null;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return SchemePlace
     */
    public function setId(int $id): SchemePlace
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
     * @return SchemePlace
     */
    public function setName(string $name): SchemePlace
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return SchemePlace
     */
    public function setPrice(float $price): SchemePlace
    {
        $this->price = $price;
        return $this;
    }

    public function getScheme(): ?Scheme
    {
        return $this->scheme;
    }

    public function setScheme(?Scheme $scheme): static
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getPoint(): ?array
    {
        return $this->point;
    }

    /**
     * @param array|null $point
     * @return SchemePlace
     */
    public function setPoint(?array $point): SchemePlace
    {
        $this->point = $point;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getArea(): ?array
    {
        return $this->area;
    }

    /**
     * @param array|null $area
     * @return SchemePlace
     */
    public function setArea(?array $area): SchemePlace
    {
        $this->area = $area;
        return $this;
    }
}