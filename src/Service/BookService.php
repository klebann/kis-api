<?php

namespace App\Service;

use App\Entity\Book;
use App\Enum\BookStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class BookService
{
    public function __construct(
        private EntityManagerInterface $em
    )
    {
    }

    public function borrow(Book $book, string $cardNumber): Book
    {
        if ($book->getStatus() === BookStatus::BORROWED) {
            throw new ConflictHttpException('Book is already borrowed');
        }

        $book->setStatus(BookStatus::BORROWED);
        $book->setBorrowedBy($cardNumber);
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
