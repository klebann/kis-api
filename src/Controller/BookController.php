<?php

namespace App\Controller;

use App\DTO\BorrowBookRequest;
use App\DTO\CreateBookRequest;
use App\DTO\UpdateBookRequest;
use App\Entity\Book;
use App\Repository\BookRepository;
use App\Service\BookService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class BookController extends BaseController
{
    #[Route('/books', methods: ['GET'])]
    public function list(BookRepository $bookRepository): JsonResponse
    {
        $books = $bookRepository->findAll();

        return new JsonResponse(array_map(
            static fn(Book $book) => $book->toArray(),
            $books,
        ));
    }

    #[Route('/books', methods: ['POST'])]
    public function create(Request $request, BookService $service): JsonResponse
    {
        $data = $this->decodeJson($request);

        if (!isset($data['serialNumber'], $data['title'], $data['author'])) {
            throw new BadRequestHttpException('Missing required fields');
        }

        $dto = new CreateBookRequest();
        $dto->serialNumber = $data['serialNumber'] ?? null;
        $dto->title = $data['title'] ?? null;
        $dto->author = $data['author'] ?? null;

        $book = $service->create($dto);

        return new JsonResponse($book->toArray(), 201);
    }

    #[Route('/books/{id}', methods: ['GET'])]
    public function getOne(Book $book): JsonResponse
    {
        return new JsonResponse($book->toArray());
    }

    #[Route('/books/{id}', methods: ['DELETE'])]
    public function delete(Book $book, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($book);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/books/{id}', methods: ['PATCH'])]
    public function update(Book $book, Request $request, BookService $service): JsonResponse
    {
        $data = $this->decodeJson($request);

        $dto = new UpdateBookRequest();
        $dto->serialNumber = $data['serialNumber'] ?? null;
        $dto->title = $data['title'] ?? null;
        $dto->author = $data['author'] ?? null;

        $book = $service->update($book, $dto);

        return new JsonResponse($book->toArray());
    }

    #[Route('/books/{id}/borrow', methods: ['POST'])]
    public function borrow(
        Book        $book,
        Request     $request,
        BookService $service,
    ): JsonResponse
    {
        $data = $this->decodeJson($request);

        $dto = new BorrowBookRequest();
        $dto->libraryCardNumber = $data['libraryCardNumber'] ?? null;

        $book = $service->borrow($book, $dto);

        return new JsonResponse($book->toArray());
    }

    #[Route('/books/{id}/return', methods: ['POST'])]
    public function return(Book $book, BookService $service): JsonResponse
    {
        $service->return($book);

        return new JsonResponse($book->toArray());
    }
}
