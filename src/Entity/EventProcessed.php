<?php

namespace App\Entity;

use App\Repository\EventProcessedRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: EventProcessedRepository::class)]
class EventProcessed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $eventId;

    #[ORM\Column(type: Types::JSON)]
    private array $payload = [];

    #[ORM\OneToMany(targetEntity: AccountingTransaction::class, mappedBy: 'eventProcessed', cascade: ['persist', 'remove'])]
    private Collection $transactions;
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(string $eventId, array $payload)
    {
        $this->eventId = $eventId;
        $this->payload = $payload;
        $this->createdAt = new \DateTimeImmutable();
        $this->transactions = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, AccountingTransaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(AccountingTransaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setEvent($this);
        }
        return $this;
    }
}
