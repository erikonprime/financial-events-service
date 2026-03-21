<?php

namespace App\Entity;

use App\Enum\AccountType;
use App\Enum\DirectionType;
use App\Repository\AccountingTransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AccountingTransactionRepository::class)]
class AccountingTransaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: EventProcessed::class, inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EventProcessed $event = null;

    #[ORM\Column(length: 100, enumType: AccountType::class)]
    #[Groups(['transaction:list'])]
    private AccountType $account;

    #[ORM\Column(length: 100, enumType: DirectionType::class)]
    #[Groups(['transaction:list'])]
    private DirectionType $direction;

    #[ORM\Column(type: 'decimal', precision: 18, scale: 2)]
    #[Groups(['transaction:list'])]
    private string $amount;

    #[ORM\Column(length: 3)]
    #[Groups(['transaction:list'])]
    private string $currency;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['transaction:list'])]
    private DateTimeImmutable $eventTimestamp;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        AccountType $account,
        DirectionType $direction,
        string $amount,
        string $currency,
        DateTimeImmutable $eventTimestamp,
    ) {
        $this->account = $account;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->direction = $direction;
        $this->eventTimestamp = $eventTimestamp;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?EventProcessed
    {
        return $this->event;
    }

    public function setEvent(?EventProcessed $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getAccount(): AccountType
    {
        return $this->account;
    }

    public function getDirection(): DirectionType
    {
        return $this->direction;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getEventTimestamp(): DateTimeImmutable
    {
        return $this->eventTimestamp;
    }
}
