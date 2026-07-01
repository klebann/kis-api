# Library API (Symfony)

Proste REST API do zarzadzania ksiazkami w systemie bibliotecznym.
Aplikacja uruchamiana jest w Dockerze razem z PostgreSQL.

## Uruchomienie projektu

```bash
docker compose up -d
```

Podczas startu kontener PHP instaluje zaleznosci Composer i wykonuje migracje bazy danych.

API jest dostepne pod adresem:

```text
https://localhost/api
```

Przy lokalnych requestach przez `curl` moze byc potrzebna flaga `-k`, poniewaz FrankenPHP/Caddy uzywa lokalnego certyfikatu HTTPS.

## Endpointy

### Create book

```http
POST /api/books
Content-Type: application/json
```

```json
{
  "serialNumber": "123456",
  "title": "The Hobbit",
  "author": "J.R.R. Tolkien"
}
```

Response `201`:

```json
{
  "id": 1,
  "serialNumber": "123456",
  "title": "The Hobbit",
  "author": "J.R.R. Tolkien",
  "status": "available"
}
```

### Get all books

```http
GET /api/books
```

Response `200`:

```json
[
  {
    "id": 1,
    "serialNumber": "123456",
    "title": "The Hobbit",
    "author": "J.R.R. Tolkien",
    "status": "available",
    "borrowedBy": null,
    "borrowedAt": null
  }
]
```

### Get single book

```http
GET /api/books/{id}
```

Response `200`:

```json
{
  "id": 1,
  "serialNumber": "123456",
  "title": "The Hobbit",
  "author": "J.R.R. Tolkien",
  "status": "available",
  "borrowedBy": null,
  "borrowedAt": null
}
```

### Update book

```http
PATCH /api/books/{id}
Content-Type: application/json
```

```json
{
  "title": "The Hobbit - updated"
}
```

Response `200`:

```json
{
  "id": 1,
  "serialNumber": "123456",
  "title": "The Hobbit - updated",
  "author": "J.R.R. Tolkien",
  "status": "available",
  "borrowedBy": null,
  "borrowedAt": null
}
```

### Delete book

```http
DELETE /api/books/{id}
```

Response `204 No Content`.

### Borrow book

```http
PATCH /api/books/{id}/borrow
Content-Type: application/json
```

```json
{
  "libraryCardNumber": "654321"
}
```

Response `200`:

```json
{
  "id": 1,
  "status": "borrowed",
  "borrowedBy": "654321",
  "borrowedAt": "2026-07-01 19:30:03"
}
```

### Return book

```http
PATCH /api/books/{id}/return
```

Response `200`:

```json
{
  "id": 1,
  "status": "available",
  "borrowedBy": null,
  "borrowedAt": null
}
```

## Error handling

Wszystkie bledy zwracane sa w formacie JSON:

```json
{
  "message": "Error message",
  "code": 400,
  "errors": {}
}
```

### Validation error

Response `422`:

```json
{
  "message": "Validation failed",
  "code": 422,
  "errors": {
    "serialNumber": [
      "Serial number must be exactly 6 digits"
    ]
  }
}
```

### Conflict

Response `409`:

```json
{
  "message": "Book is already borrowed",
  "code": 409,
  "errors": {}
}
```

### Not found

Response `404`:

```json
{
  "message": "Not Found",
  "code": 404,
  "errors": {}
}
```

### Server error

Response `500`:

```json
{
  "message": "Internal Server Error",
  "code": 500,
  "errors": {}
}
```

## Tech stack

* PHP 8.5
* Symfony 8.1
* PostgreSQL
* Doctrine ORM
* Docker / Docker Compose

## Notes

* Authentication and authorization are intentionally omitted.
* Serial number and library card number must be six digits.
* Book status is handled by enum: `available`, `borrowed`.
* Date format is `Y-m-d H:i:s`.
