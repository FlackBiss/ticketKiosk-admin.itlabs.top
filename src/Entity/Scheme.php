<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\SchemeRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: SchemeRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['scheme:read']],
    paginationEnabled: false,
)]
class Scheme
{
    use UpdatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['scheme:read', 'news:read', 'event:read', 'event:reads'])]
    private int $id;

    #[ORM\Column(length: 255)]
    #[Groups(['scheme:read', 'news:read', 'event:read', 'event:reads'])]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['scheme:read', 'news:read', 'event:read', 'event:reads'])]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'scheme_images', fileNameProperty: 'image')]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])]
    private ?File $imageFile = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'scheme', orphanRemoval: true)]
    private Collection $events;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $schemeData;

    private string $schemeWidget;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
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
     * @return ?string
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param ?string $image
     * @return Scheme
     */
    public function setImage(?string $image): Scheme
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return File|null
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * @param File|null $imageFile
     * @return Scheme
     */
    public function setImageFile(?File $imageFile): Scheme
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            $this->updatedAt = new DateTime();
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setScheme($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getScheme() === $this) {
                $event->setScheme(null);
            }
        }

        return $this;
    }

    #[Groups(['scheme:read'])]
    public function getSchemeData(): ?string
    {
        return json_encode($this->schemeData, true);
    }

    public function setSchemeData(?string $schemeData): Scheme
    {
        $this->schemeData = json_decode($schemeData);
        return $this;
    }

    public function getSchemeWidget(): string
    {
        return $this->schemeWidget;
    }

    public function setSchemeWidget(string $schemeWidget): Scheme
    {
        $this->schemeWidget = $schemeWidget;
        return $this;
    }

    public function getSchemeDataJson(): ?array
    {
        return $this->schemeData;
    }
}