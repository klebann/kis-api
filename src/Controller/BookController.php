<?php

namespace App\Controller;

use App\DTO\BorrowBookRequest;
use App\Entity\Book;
use App\Enum\BookStatus;
use App\Repository\BookRepository;
use App\Service\BookService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class BookController
{
    #[Route('/books', methods: ['GET'])]
    public function list(BookRepository $bookRepository): JsonResponse
    {
        $books = $bookRepository->findAll();

        $data = array_map(function ($book) {
            return [
                'id' => $book->getId(),
                'serialNumber' => $book->getSerialNumber(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'status' => $book->getStatus()->value,
                'borrowedBy' => $book->getBorrowedBy(),
                'borrowedAt' => $book->getBorrowedAt()?->format('Y-m-d H:i:s'),
            ];
        }, $books);

        return new JsonResponse($data);
    }

    #[Route('/books', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            throw new BadRequestHttpException('Invalid JSON');
        }

        if (!isset($data['serialNumber'], $data['title'], $data['author'])) {
            throw new BadRequestHttpException('Missing required fields');
        }

        $book = new Book();
        $book->setSerialNumber($data['serialNumber']);
        $book->setTitle($data['title']);
        $book->setAuthor($data['author']);
        $book->setStatus(BookStatus::AVAILABLE);

        $errors = $validator->validate($book);

        if (count($errors) > 0) {
            throw new ValidationFailedException($book, $errors);
        }

        $existing = $em->getRepository(Book::class)
            ->findOneBy(['serialNumber' => $book->getSerialNumber()]);

        if ($existing) {
            throw new ConflictHttpException('Serial number already exists');
        }

        try {
            $em->persist($book);
            $em->flush();
        } catch (UniqueConstraintViolationException) {
            throw new ConflictHttpException('Serial number already exists');
        }

        return new JsonResponse([
            'id' => $book->getId(),
            'serialNumber' => $book->getSerialNumber(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'status' => $book->getStatus()->value,
        ], 201);
    }

    #[Route('/books/{id}', methods: ['GET'])]
    public function getOne(Book $book): JsonResponse
    {
        return new JsonResponse([
            'id' => $book->getId(),
            'serialNumber' => $book->getSerialNumber(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'status' => $book->getStatus()->value,
            'borrowedBy' => $book->getBorrowedBy(),
            'borrowedAt' => $book->getBorrowedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/books/{id}', methods: ['DELETE'])]
    public function delete(Book $book, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($book);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/books/{id}', methods: ['PATCH'])]
    public function update(
        Book                   $book,
        Request                $request,
        EntityManagerInterface $em,
        ValidatorInterface     $validator
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            throw new BadRequestHttpException('Invalid JSON');
        }

        if (isset($data['serialNumber'])) {
            $book->setSerialNumber($data['serialNumber']);
        }

        if (isset($data['title'])) {
            $book->setTitle($data['title']);
        }

        if (isset($data['author'])) {
            $book->setAuthor($data['author']);
        }

        $errors = $validator->validate($book);

        if (count($errors) > 0) {
            throw new ValidationFailedException($book, $errors);
        }

        if (isset($data['serialNumber'])) {
            $existing = $em->getRepository(Book::class)
                ->findOneBy(['serialNumber' => $book->getSerialNumber()]);

            if ($existing !== null && $existing->getId() !== $book->getId()) {
                throw new ConflictHttpException('Serial number already exists');
            }
        }

        try {
            $em->flush();
        } catch (UniqueConstraintViolationException) {
            throw new ConflictHttpException('Serial number already exists');
        }

        return new JsonResponse([
            'id' => $book->getId(),
            'serialNumber' => $book->getSerialNumber(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'status' => $book->getStatus()->value,
            'borrowedBy' => $book->getBorrowedBy(),
            'borrowedAt' => $book->getBorrowedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/books/{id}/borrow', methods: ['PATCH'])]
    public function borrow(
        Book               $book,
        Request            $request,
        BookService        $service,
        ValidatorInterface $validator
    ): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            throw new BadRequestHttpException('Invalid JSON');
        }

        $dto = new BorrowBookRequest();
        $dto->libraryCardNumber = $data['libraryCardNumber'] ?? '';

        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            throw new ValidationFailedException($dto, $errors);
        }

        $service->borrow($book, $dto->libraryCardNumber);

        return new JsonResponse([
            'id' => $book->getId(),
            'status' => $book->getStatus()->value,
            'borrowedBy' => $book->getBorrowedBy(),
            'borrowedAt' => $book->getBorrowedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/books/{id}/return', methods: ['PATCH'])]
    public function return(Book $book, BookService $service): JsonResponse
    {
        $service->return($book);

        return new JsonResponse([
            'id' => $book->getId(),
            'status' => $book->getStatus()->value,
            'borrowedBy' => $book->getBorrowedBy(),
            'borrowedAt' => $book->getBorrowedAt()?->format('Y-m-d H:i:s'),
        ]);
    }
}
