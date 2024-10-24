<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'assigned_to')]
    private Collection $owned_tickets;

    /**
     * @var Collection<int, TicketStatusHistory>
     */
    #[ORM\OneToMany(targetEntity: TicketStatusHistory::class, mappedBy: 'changed_by')]
    private Collection $ticketAssignedTicketHistories;

    public function __construct()
    {
        $this->owned_tickets = new ArrayCollection();
        $this->ticketAssignedTicketHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getOwnedTickets(): Collection
    {
        return $this->owned_tickets;
    }

    public function addOwnedTicket(Ticket $ownedTicket): static
    {
        if (!$this->owned_tickets->contains($ownedTicket)) {
            $this->owned_tickets->add($ownedTicket);
            $ownedTicket->setOwnedby($this);
        }

        return $this;
    }

    public function removeOwnedTicket(Ticket $ownedTicket): static
    {
        if ($this->owned_tickets->removeElement($ownedTicket)) {
            // set the owning side to null (unless already changed)
            if ($ownedTicket->getOwnedby() === $this) {
                $ownedTicket->setOwnedby(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TicketStatusHistory>
     */
    public function getTicketAssignedTicketHistories(): Collection
    {
        return $this->ticketAssignedTicketHistories;
    }

    public function addTicketAssignedTicketHistory(TicketStatusHistory $ticketAssignedTicketHistory): static
    {
        if (!$this->ticketAssignedTicketHistories->contains($ticketAssignedTicketHistory)) {
            $this->ticketAssignedTicketHistories->add($ticketAssignedTicketHistory);
            $ticketAssignedTicketHistory->setChangedBy($this);
        }

        return $this;
    }

    public function removeTicketAssignedTicketHistory(TicketStatusHistory $ticketAssignedTicketHistory): static
    {
        if ($this->ticketAssignedTicketHistories->removeElement($ticketAssignedTicketHistory)) {
            // set the owning side to null (unless already changed)
            if ($ticketAssignedTicketHistory->getChangedBy() === $this) {
                $ticketAssignedTicketHistory->setChangedBy(null);
            }
        }

        return $this;
    }
}
