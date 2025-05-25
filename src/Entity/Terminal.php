<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\Controller\Terminal\ChangeController;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\TerminalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: TerminalRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
    ],
    normalizationContext: ['groups' => ['terminal:read']],
    paginationEnabled: false
)]
#[UniqueEntity(fields: ['ipAddress'])]
#[Post(
    uriTemplate: 'terminals/edit',
    controller: ChangeController::class,
    openapi: new Model\Operation(
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                            ],
                            'kkt' => [
                                'type' => 'boolean',
                            ],
                        ],
                    ]
                ]
            ])
        )
    ),
    deserialize: false)]
#[GetCollection(normalizationContext: ['groups' => ['terminal:read']])]
class Terminal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['terminal:read', 'exceptionLog:write', 'sessions:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups('terminal:read')]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups('terminal:read')]
    private ?bool $network = false;

    #[ORM\Column]
    #[Groups('terminal:read')]
    private ?bool $kkt = false;

    #[ORM\Column(length: 255)]
    #[Groups('terminal:read')]
    private ?string $ipAddress = null;

    #[ORM\OneToMany(targetEntity: Sessions::class, mappedBy: 'terminal', cascade: ['persist', 'remove'])]
    private Collection $sessions;

    #[ORM\OneToMany(targetEntity: ExceptionLog::class, mappedBy: 'terminal', cascade: ['persist', 'remove'])]
    private Collection $exceptionLogs;

    #[ORM\Column]
    #[Groups('terminal:read')]
    private ?int $password = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups('terminal:read')]
    private ?string $contacts = null;

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

    public function isNetwork(): ?bool
    {
        return $this->network;
    }

    public function setNetwork(bool $network): static
    {
        $this->network = $network;

        return $this;
    }

    public function isKkt(): ?bool
    {
        return $this->kkt;
    }

    public function setKkt(bool $kkt): static
    {
        $this->kkt = $kkt;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getIsNetworkStringify(): string
    {
        $style = 'color: ';
        $style .= $this->isNetwork() ? 'green' : 'red';
        $text = $this->isNetwork() ? 'Да' : 'Нет';
        return "<div style='$style'>$text</div>";
    }

    public function getIsKktStringify(): string
    {
        $style = 'color: ';
        $style .= $this->isKkt() ? 'green' : 'red';
        $text = $this->isKkt() ? 'Да' : 'Нет';
        return "<div style='$style'>$text</div>";
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

    public function getPassword(): ?int
    {
        return $this->password;
    }

    public function setPassword(int $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getContacts(): ?string
    {
        return $this->contacts;
    }

    public function setContacts(?string $contacts): static
    {
        $this->contacts = $contacts;

        return $this;
    }
}
