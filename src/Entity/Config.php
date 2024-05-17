<?php

namespace App\Entity;

use App\Repository\ConfigRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigRepository::class)]
class Config
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $value_invoice = null;

    #[ORM\Column(length: 255)]
    private ?string $month_invoiced = null;

    #[ORM\Column(length: 255)]
    private ?string $value_subscription = null;

    #[ORM\Column(length: 255)]
    private ?string $number_records_table = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValueInvoice(): ?string
    {
        return $this->value_invoice;
    }

    public function setValueInvoice(string $value_invoice): static
    {
        $this->value_invoice = $value_invoice;

        return $this;
    }

    public function getMonthInvoiced(): ?string
    {
        return $this->month_invoiced;
    }

    public function setMonthInvoiced(string $month_invoiced): static
    {
        $this->month_invoiced = $month_invoiced;

        return $this;
    }

    public function getValueSubscription(): ?string
    {
        return $this->value_subscription;
    }

    public function setValueSubscription(string $value_subscription): static
    {
        $this->value_subscription = $value_subscription;

        return $this;
    }

    public function getNumberRecordsTable(): ?string
    {
        return $this->number_records_table;
    }

    public function setNumberRecordsTable(string $number_records_table): static
    {
        $this->number_records_table = $number_records_table;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
