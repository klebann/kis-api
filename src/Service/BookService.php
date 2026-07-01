<?php

namespace App\Service;

use App\DTO\BorrowBookRequest;
use App\DTO\CreateBookRequest;
use App\DTO\UpdateBookRequest;
use App\Entity\Book;
use App\Enum\BookStatus;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface     $validator
    )
    {

    }

    public function create(CreateBookRequest $dto): Book
    {
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            throw new ValidationFailedException($dto, $errors);
        }

        try {
            $book = new Book();
            $book->setSerialNumber($dto->serialNumber);
            $book->setTitle($dto->title);
            $book->setAuthor($dto->author);
            $book->setStatus(BookStatus::AVAILABLE);

            $this->em->persist($book);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new ConflictHttpException('Serial number already exists');
        }

        return $book;
    }

    public function update(Book $book, UpdateBookRequest $dto): Book
    {
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            throw new ValidationFailedException($dto, $errors);
        }

        if ($dto->serialNumber !== null) {
            $book->setSerialNumber($dto->serialNumber);
        }

        if ($dto->title !== null) {
            $book->setTitle($dto->title);
        }

        if ($dto->author !== null) {
            $book->setAuthor($dto->author);
        }

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            throw new ConflictHttpException('Serial number already exists');
        }

        return $book;
    }

    public function borrow(Book $book, BorrowBookRequest $dto): Book
    {
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            throw new ValidationFailedException($dto, $errors);
        }

        if ($book->getStatus() === BookStatus::BORROWED) {
            throw new ConflictHttpException('Book is already borrowed');
        }

        $book->setStatus(BookStatus::BORROWED);
        $book->setBorrowedBy($dto->libraryCardNumber);
        $book->setBorrowedAt(new \DateTimeImmutable());

        $this->em->flush();

        return $book;
    }

    public function return(Book $book): Book
    {
        if ($book->getStatus() === BookStatus::AVAILABLE) {
            throw new ConflictHttpException('Book is not borrowed');
        }

        $book->setStatus(BookStatus::AVAILABLE);
        $book->setBorrowedBy(null);
        $book->setBorrowedAt(null);

        $this->em->flush();

        return $book;
    }
}
