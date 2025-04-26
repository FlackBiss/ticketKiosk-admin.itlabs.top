<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\TerminalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: TerminalRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => 'terminal:read'],
    paginationEnabled: false
)]
class Terminal
{
    use CreatedAtTrait;
    use UpdatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['terminal:read', 'exceptionLog:write', 'sessions:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups('terminal:read')]
    private ?string $title = null;

    #[ORM\OneToMany(targetEntity: Sessions::class, mappedBy: 'terminal', cascade: ['persist', 'remove'])]
    private Collection $sessions;

    #[ORM\OneToMany(targetEntity: ExceptionLog::class, mappedBy: 'terminal', cascade: ['persist', 'remove'])]
    private Collection $exceptionLogs;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
        $this->exceptionLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->title;
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

    /**
     * @return Collection<int, Sessions>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Sessions $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setTerminal($this);
        }

        return $this;
    }

    public function removeSession(Sessions $session): static
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getTerminal() === $this) {
                $session->setTerminal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ExceptionLog>
     */
    public function getExceptionLogs(): Collection
    {
        return $this->exceptionLogs;
    }

    public function addExceptionLog(ExceptionLog $exceptionLog): static
    {
        if (!$this->exceptionLogs->contains($exceptionLog)) {
            $this->exceptionLogs->add($exceptionLog);
            $exceptionLog->setTerminal($this);
        }

        return $this;
    }

    public function removeExceptionLog(ExceptionLog $exceptionLog): static
    {
        if ($this->exceptionLogs->removeElement($exceptionLog)) {
            // set the owning side to null (unless already changed)
            if ($exceptionLog->getTerminal() === $this) {
                $exceptionLog->setTerminal(null);
            }
        }

        return $this;
    }
}
