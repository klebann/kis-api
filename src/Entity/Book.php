<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\BookStatus;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_book_serial_number', fields: ['serialNumber'])]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 6)]
    #[Assert\NotBlank(message: 'Serial number is required')]
    #[Assert\Regex(
        pattern: '/^\d{6}$/',
        message: 'Serial number must be exactly 6 digits'
    )]
    private string $serialNumber;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Title is required')]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Author is required')]
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

    /**
     * @return array{
     *     id: int|null,
     *     serialNumber: string|null,
     *     title: string|null,
     *     author: string|null,
     *     status: string,
     *     borrowedBy: string|null,
     *     borrowedAt: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'serialNumber' => $this->serialNumber,
            'title' => $this->title,
            'author' => $this->author,
            'status' => $this->status->value,
            'borrowedBy' => $this->borrowedBy,
            'borrowedAt' => $this->borrowedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
