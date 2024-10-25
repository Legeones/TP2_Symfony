<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeInterface $deadline = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\Column(enumType: Priority::class)]
    private ?Priority $priority = null;

    #[ORM\Column(enumType: Status::class)]
    private ?Status $status = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'owned_tickets')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $owned_by = null;

    /**
     * @var Collection<int, TicketStatusHistory>
     */
    #[ORM\OneToMany(targetEntity: TicketStatusHistory::class, mappedBy: 'ticket_id')]
    private Collection $ticketStatusHistories;

    #[ORM\ManyToOne(inversedBy: 'assigned_tickets')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $assigned_to = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $resolve_at = null;

    public function __construct()
    {
        $this->ticketStatusHistories = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTime();
        $this->status = Status::Open;
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

    public function getCreatedat(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedat(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTimeInterface $deadline): static
    {

        $this->deadline = $deadline;

        return $this;
    }

    public function setDeadlineBasedOnPriority(): static
    {
        $this->deadline = (new \DateTimeImmutable())->modify('+' . $this->priority->days() . ' days');
        return $this;
    }

    public function getUpdatedat(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedat(?\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getPriority(): ?Priority
    {
        return $this->priority;
    }

    public function setPriority(Priority $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getOwnedby(): ?User
    {
        return $this->owned_by;
    }

    public function setOwnedby(?User $owned_by): static
    {
        $this->owned_by = $owned_by;

        return $this;
    }

    /**
     * @return Collection<int, TicketStatusHistory>
     */
    public function getTicketStatusHistories(): Collection
    {
        return $this->ticketStatusHistories;
    }

    public function addTicketStatusHistory(TicketStatusHistory $ticketStatusHistory): static
    {
        if (!$this->ticketStatusHistories->contains($ticketStatusHistory)) {
            $this->ticketStatusHistories->add($ticketStatusHistory);
            $ticketStatusHistory->setTicketId($this);
        }

        return $this;
    }

    public function removeTicketStatusHistory(TicketStatusHistory $ticketStatusHistory): static
    {
        if ($this->ticketStatusHistories->removeElement($ticketStatusHistory)) {
            // set the owning side to null (unless already changed)
            if ($ticketStatusHistory->getTicketId() === $this) {
                $ticketStatusHistory->setTicketId(null);
            }
        }

        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assigned_to;
    }

    public function setAssignedTo(?User $assigned_to): static
    {
        $this->assigned_to = $assigned_to;
        $this->status = Status::InProgress;

        return $this;
    }

    public function getResolveAt(): ?\DateTimeImmutable
    {
        return $this->resolve_at;
    }

    public function setResolveAt(\DateTimeImmutable $resolve_at): static
    {
        $this->resolve_at = $resolve_at;

        return $this;
    }
}
