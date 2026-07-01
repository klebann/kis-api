<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\BookStatus;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 6, unique: true)]
    private string $serialNumber;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $author = null;

    #[ORM\Column(enumType: BookStatus::class)]
    private BookStatus $status = BookStatus::AVAILABLE;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $borrowedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $borrowedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(string $serialNumber): static
    {
        $this->serialNumber = $serialNumber;

        return $this;
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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getStatus(): BookStatus
    {
        return $this->status;
    }

    public function setStatus(BookStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getBorrowedBy(): ?string
    {
        return $this->borrowedBy;
    }

    public function setBorrowedBy(?string $borrowedBy): static
    {
        $this->borrowedBy = $borrowedBy;

        return $this;
    }

    public function getBorrowedAt(): ?\DateTimeImmutable
    {
        return $this->borrowedAt;
    }

    public function setBorrowedAt(?\DateTimeImmutable $borrowedAt): static
    {
        $this->borrowedAt = $borrowedAt;

        return $this;
    }
}
