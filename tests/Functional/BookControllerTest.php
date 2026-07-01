<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookControllerTest extends WebTestCase
{
    private function createBook(
        $client,
        string $serialNumber = '123456',
        string $title = 'The Hobbit',
        string $author = 'J.R.R. Tolkien',
    ): array {
        $client->request(
            'POST',
            '/api/books',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'serialNumber' => $serialNumber,
                'title' => $title,
                'author' => $author,
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(201);

        return json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    }

    private function decodeResponse($client): array
    {
        return json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    }

    public function testCreateBook(): void
    {
        $client = static::createClient();

        $data = $this->createBook($client);

        $this->assertArrayHasKey('id', $data);
        $this->assertSame('123456', $data['serialNumber']);
        $this->assertSame('The Hobbit', $data['title']);
        $this->assertSame('J.R.R. Tolkien', $data['author']);
        $this->assertSame('available', $data['status']);
    }

    public function testCreateBookWithInvalidJson(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/books',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: 'not-json',
        );

        $this->assertResponseStatusCodeSame(400);
        $data = $this->decodeResponse($client);
        $this->assertSame('Invalid JSON', $data['message']);
        $this->assertSame(400, $data['code']);
    }

    public function testCreateBookWithMissingFields(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/books',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['title' => 'The Hobbit'], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(400);
        $data = $this->decodeResponse($client);
        $this->assertSame('Missing required fields', $data['message']);
    }

    public function testCreateBookWithInvalidSerialNumber(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/books',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'serialNumber' => 'abc',
                'title' => 'The Hobbit',
                'author' => 'J.R.R. Tolkien',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(422);
        $data = $this->decodeResponse($client);
        $this->assertSame('Validation failed', $data['message']);
        $this->assertArrayHasKey('serialNumber', $data['errors']);
    }

    public function testCreateBookWithDuplicateSerialNumber(): void
    {
        $client = static::createClient();
        $this->createBook($client, '111111');

        $client->request(
            'POST',
            '/api/books',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'serialNumber' => '111111',
                'title' => 'Another Book',
                'author' => 'Another Author',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(409);
        $data = $this->decodeResponse($client);
        $this->assertSame('Serial number already exists', $data['message']);
    }

    public function testListBooks(): void
    {
        $client = static::createClient();
        $this->createBook($client, '222222', 'Book A', 'Author A');
        $this->createBook($client, '333333', 'Book B', 'Author B');

        $client->request('GET', '/api/books');

        $this->assertResponseIsSuccessful();
        $data = $this->decodeResponse($client);
        $this->assertCount(2, $data);
        $this->assertSame('222222', $data[0]['serialNumber']);
        $this->assertSame('333333', $data[1]['serialNumber']);
    }

    public function testGetBook(): void
    {
        $client = static::createClient();
        $book = $this->createBook($client);

        $client->request('GET', '/api/books/'.$book['id']);

        $this->assertResponseIsSuccessful();
        $data = $this->decodeResponse($client);
        $this->assertSame($book['id'], $data['id']);
        $this->assertSame('123456', $data['serialNumber']);
        $this->assertNull($data['borrowedBy']);
        $this->assertNull($data['borrowedAt']);
    }

    public function testGetBookNotFound(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/books/99999');

        $this->assertResponseStatusCodeSame(404);
        $data = $this->decodeResponse($client);
        $this->assertSame(404, $data['code']);
    }

    public function testUpdateBook(): void
    {
        $client = static::createClient();
        $book = $this->createBook($client);

        $client->request(
            'PATCH',
            '/api/books/'.$book['id'],
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['title' => 'The Hobbit - updated'], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();
        $data = $this->decodeResponse($client);
        $this->assertSame('The Hobbit - updated', $data['title']);
        $this->assertSame('123456', $data['serialNumber']);
    }

    public function testUpdateBookWithDuplicateSerialNumber(): void
    {
        $client = static::createClient();
        $this->createBook($client, '444444');
        $book = $this->createBook($client, '555555');

        $client->request(
            'PATCH',
            '/api/books/'.$book['id'],
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['serialNumber' => '444444'], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(409);
        $data = $this->decodeResponse($client);
        $this->assertSame('Serial number already exists', $data['message']);
    }

    public function testDeleteBook(): void
    {
        $client = static::createClient();
        $book = $this->createBook($client);

        $client->request('DELETE', '/api/books/'.$book['id']);

        $this->assertResponseStatusCodeSame(204);

        $client->request('GET', '/api/books/'.$book['id']);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testBorrowBook(): void
    {
        $client = static::createClient();
        $book = $this->createBook($client);

        $client->request(
            'PATCH',
            '/api/books/'.$book['id'].'/borrow',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['libraryCardNumber' => '654321'], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();
        $data = $this->decodeResponse($client);
        $this->assertSame('borrowed', $data['status']);
        $this->assertSame('654321', $data['borrowedBy']);
        $this->assertNotNull($data['borrowedAt']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $data['borrowedAt']);
    }

    public function testBorrowBookWithInvalidLibraryCardNumber(): void
    {
        $client = static::createClient();
        $book = $this->createBook($client);

        $client->request(
            'PATCH',
            '/api/books/'.$book['id'].'/borrow',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['libraryCardNumber' => 'abc'], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(422);
        $data = $this->decodeResponse($client);
        $this->assertSame('Validation failed', $data['message']);
        $this->assertArrayHasKey('libraryCardNumber', $data['errors']);
    }

    public function testBorrowAlreadyBorrowedBook(): void
    {
        $client = static::createClient();
        $book = $this->createBook($client);

        $client->request(
            'PATCH',
            '/api/books/'.$book['id'].'/borrow',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['libraryCardNumber' => '654321'], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();

        $client->request(
            'PATCH',
            '/api/books/'.$book['id'].'/borrow',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['libraryCardNumber' => '111111'], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(409);
        $data = $this->decodeResponse($client);
        $this->assertSame('Book is already borrowed', $data['message']);
    }

    public function testReturnBook(): void
    {
        $client = static::createClient();
        $book = $this->createBook($client);

        $client->request(
            'PATCH',
            '/api/books/'.$book['id'].'/borrow',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['libraryCardNumber' => '654321'], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();

        $client->request('PATCH', '/api/books/'.$book['id'].'/return');

        $this->assertResponseIsSuccessful();
        $data = $this->decodeResponse($client);
        $this->assertSame('available', $data['status']);
        $this->assertNull($data['borrowedBy']);
        $this->assertNull($data['borrowedAt']);
    }

    public function testReturnBookThatIsNotBorrowed(): void
    {
        $client = static::createClient();
        $book = $this->createBook($client);

        $client->request('PATCH', '/api/books/'.$book['id'].'/return');

        $this->assertResponseStatusCodeSame(409);
        $data = $this->decodeResponse($client);
        $this->assertSame('Book is not borrowed', $data['message']);
    }
}
