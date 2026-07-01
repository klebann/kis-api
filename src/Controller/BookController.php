<?php

namespace App\Controller;

use App\Entity\Book;
use App\Enum\BookStatus;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class BookController
{
    #[Route('/api/books', methods: ['GET'])]
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

    #[Route('/api/books', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $book = new Book();
        $book->setSerialNumber($data['serialNumber']);
        $book->setTitle($data['title']);
        $book->setAuthor($data['author']);
        $book->setStatus(BookStatus::AVAILABLE);

        $em->persist($book);
        $em->flush();

        return new JsonResponse([
            'id' => $book->getId(),
            'serialNumber' => $book->getSerialNumber(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'status' => $book->getStatus()->value,
        ], 201);
    }
}
