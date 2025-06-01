<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\Event\EventController;
use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\EventRepository;
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
#[ApiResource(
    operations: [
        new GetCollection(order: ['dateTimeAt' => 'DESC'], filters: ['app.event_date_filter', 'app.event_title_filter', 'app.event_type_filter', 'app.event_scheme_filter']),
    ],
    normalizationContext: ['groups' => ['event:read']],
)]
#[GetCollection(uriTemplate: 'events_dates', controller: EventController::class, paginationEnabled: false,)]
#[Get(normalizationContext: ['groups' => ['event:reads']])]
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    use UpdatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['news:read', 'event:read', 'event:reads'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['news:read', 'event:read', 'event:reads'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['news:read', 'event:read', 'event:reads'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['news:read', 'event:read', 'event:reads'])]
    private ?\DateTimeImmutable $dateTimeAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['news:read', 'event:read', 'event:reads'])]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Groups(['news:read', 'event:read', 'event:reads'])]
    private ?string $age = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['news:read', 'event:read', 'event:reads'])]
    private ?\DateTimeInterface $duration = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['news:read', 'event:read', 'event:reads'])]
    private ?int $places = null;

    /**
     * @var Collection<int, News>
     */
    #[ORM\OneToMany(targetEntity: News::class, mappedBy: 'event', cascade: ['persist'])]
    private Collection $news;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'event', cascade: ['all'])]
    private Collection $tickets;

    #[ORM\Column(nullable: true)]
    #[Groups(['news:read', 'event:read', 'event:reads'])]
    private ?int $price = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['news:read', 'event:read', 'event:reads'])]
    private ?Scheme $scheme = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $schemeData;

    private string $schemeWidget;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['news:read', 'event:read', 'event:reads'])]
    private ?string $shortDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['news:read', 'event:read', 'event:reads'])]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'event_images', fileNameProperty: 'image')]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])]
    private ?File $imageFile = null;

    public function __construct()
    {
        $this->news = new ArrayCollection();
        $this->tickets = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateTimeAt(): ?\DateTimeImmutable
    {
        return $this->dateTimeAt;
    }

    public function setDateTimeAt(\DateTimeImmutable $dateTimeAt): static
    {
        $this->dateTimeAt = $dateTimeAt;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getAge(): ?string
    {
        return $this->age;
    }

    public function setAge(string $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getDuration(): ?\DateTimeInterface
    {
        return $this->duration;
    }

    public function setDuration(\DateTimeInterface $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPlaces(): ?int
    {
        return $this->places;
    }

    public function setPlaces(?int $places): static
    {
        $this->places = $places;

        return $this;
    }

    /**
     * @return Collection<int, News>
     */
    public function getNews(): Collection
    {
        return $this->news;
    }

    public function addNews(News $news): static
    {
        if (!$this->news->contains($news)) {
            $this->news->add($news);
            $news->setEvent($this);
        }

        return $this;
    }

    public function removeNews(News $news): static
    {
        if ($this->news->removeElement($news)) {
            // set the owning side to null (unless already changed)
            if ($news->getEvent() === $this) {
                $news->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): static
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setEvent($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getEvent() === $this) {
                $ticket->setEvent(null);
            }
        }

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): static
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

    public function getSchemeData(): ?string
    {
        return json_encode($this->schemeData, true);
    }

    public function setSchemeData(?string $schemeData): Event
    {
        $this->schemeData = json_decode($schemeData);
        return $this;
    }

    public function getSchemeWidget(): string
    {
        return $this->schemeWidget;
    }

    public function setSchemeWidget(string $schemeWidget): Event
    {
        $this->schemeWidget = $schemeWidget;
        return $this;
    }

    #[Groups(['event:reads'])]
    public function getSchemeDataJson(): ?array
    {
        return $this->schemeData;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    #[Groups(['event:read', 'event:reads', 'news:read'])]
    public function getStartPrice(): int
    {
        $basePrice = $this->getPrice();
        $schemePrices = array_column((array)$this->getSchemeDataJson(), 'price');

        $prices = array_filter(
            array_merge([$basePrice], $schemePrices),
            fn($p) => is_int($p) && $p >= 0
        );

        if (empty($prices)) {
            return 0;
        }

        return min($prices);
    }

    #[Groups(['event:reads'])]
    public function getTypesPlaces(): ?array
    {
        if (empty($this->getSchemeDataJson())) {
            return null;
        }

        $types = [];
        foreach ($this->getSchemeDataJson() as $item) {
            $id = $item['placeId'];
            if (!isset($types[$id])) {
                $types[$id] = [
                    'placeId' => $id,
                    'name'    => $item['name'],
                    'color'   => $item['color'],
                    'price'   => $item['price'],
                ];
            }
        }

        return array_values($types);
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): self
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            $this->updatedAt = new DateTime();
        }

        return $this;
    }

    #[Groups(['event:read'])]
    public function getPlacesHave(): ?int
    {
        $placesHave = 0;

        if ($this->getType() === 'Ограниченное количество мест')
        {
            $placesHave = $this->getPlaces() - $this->getTickets()?->count() ?? 0;
        }

        else if ($this->getType() === 'Места согласно билетам')
        {
            foreach ($this->getSchemeDataJson() as $item)
            {
                if ($item['booked'] === true)
                {
                    $placesHave++;
                }
            }

            $placesHave = count($this->getSchemeDataJson()) - $placesHave;
        }

        return $placesHave;
    }
}
