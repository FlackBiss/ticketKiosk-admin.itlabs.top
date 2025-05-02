<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['event:read']],
)]
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['news:read', 'event:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['news:read', 'event:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['news:read', 'event:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['news:read', 'event:read'])]
    private ?\DateTimeImmutable $dateTimeAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['news:read', 'event:read'])]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Groups(['news:read', 'event:read'])]
    private ?string $age = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['news:read', 'event:read'])]
    private ?\DateTimeInterface $duration = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['news:read', 'event:read'])]
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
    #[Groups(['news:read', 'event:read'])]
    private ?int $price = null;

    /**
     * @var Collection<int, SessionEvents>
     */
    #[ORM\OneToMany(targetEntity: SessionEvents::class, mappedBy: 'event')]
    private Collection $sessionEvents;

    public function __construct()
    {
        $this->news = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        $this->sessionEvents = new ArrayCollection();
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

    /**
     * @return Collection<int, SessionEvents>
     */
    public function getSessionEvents(): Collection
    {
        return $this->sessionEvents;
    }

    public function addSessionEvent(SessionEvents $sessionEvent): static
    {
        if (!$this->sessionEvents->contains($sessionEvent)) {
            $this->sessionEvents->add($sessionEvent);
            $sessionEvent->setEvent($this);
        }

        return $this;
    }

    public function removeSessionEvent(SessionEvents $sessionEvent): static
    {
        if ($this->sessionEvents->removeElement($sessionEvent)) {
            // set the owning side to null (unless already changed)
            if ($sessionEvent->getEvent() === $this) {
                $sessionEvent->setEvent(null);
            }
        }

        return $this;
    }
}
