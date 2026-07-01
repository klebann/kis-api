# 📚 Library API (Symfony)

Proste REST API do zarządzania książkami w systemie bibliotecznym.
Aplikacja uruchamiana jest w Dockerze wraz z PostgreSQL.

---

# 🚀 Uruchomienie projektu

```bash
docker compose up -d
```

Instalacja zależności:

```bash
docker compose exec php composer install
```

Migracje:

```bash
docker compose exec php php bin/console doctrine:migrations:migrate
```

---

# Endpointy

## Create book

### Request

```http
POST /api/books
Content-Type: application/json
```

```json
{
    "serialNumber": 123456,
    "title": "The Hobbit",
    "author": "J.R.R. Tolkien"
}
```

### Response (201)

```json
{
    "id": 1,
    "serialNumber": "123456",
    "title": "The Hobbit",
    "author": "J.R.R. Tolkien",
    "status": "available"
}
```

---

## Get all books

### Request

```http
GET /api/books
```

### Response (200)

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

---

## 🔍 Get single book

```http
GET /api/books/{id}
```

### Response (200)

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

---

## ✏️ Update book

```http
PATCH /api/books/{id}
```

### Request

```json
{
    "title": "The Hobbit - updated"
}
```

### Response (200)

```json
{
    "id": 1,
    "serialNumber": "234312",
    "title": "Sample Book Title",
    "author": "John Doe",
    "status": "available",
    "borrowedBy": null,
    "borrowedAt": null
}
```

---

## ❌ Delete book

```http
DELETE /api/books/{id}
```

### Response

```
204 No Content
```

---

## 📥 Borrow book

```http
PATCH /api/books/{id}/borrow
Content-Type: application/json
```

### Request

```json
{
    "libraryCardNumber": "654321"
}
```

### Response (200)

```json
{
    "id": 1,
    "status": "borrowed",
    "borrowedBy": "654321",
    "borrowedAt": "2026-07-01 19:30:03"
}
```

---

## 📤 Return book

```http
PATCH /api/books/{id}/return
```

### Response (200)

```json
{
  "id": 1,
  "status": "available",
  "borrowedBy": null,
  "borrowedAt": null
}
```

---

# ⚠️ Error handling

Wszystkie błędy zwracane są w formacie JSON.

---

## ❌ Validation error (422)

```json
{
  "message": "Validation failed",
  "errors": {
    "libraryCardNumber": [
      "Library card number must be exactly 6 digits"
    ]
  }
}
```

---

## ❌ Conflict (409)

Przykład: próba wypożyczenia już wypożyczonej książki

```json
{
  "error": "Book is already borrowed",
  "code": 409
}
```

---

## ❌ Not found (404)

```json
{
  "error": "Not Found",
  "code": 404
}
```

---

## ❌ Server error (500)

```json
{
  "error": "Internal Server Error",
  "code": 500
}
```

---

# 🧱 Tech stack

* PHP 8+
* Symfony 8.1
* PostgreSQL
* Doctrine ORM
* Docker / Docker Compose

---

# 📌 Notes

* Authentication is not implemented (as per requirements)
* Serial number and library card number are 6-digit numeric values
* Book status is handled via enum (`available`, `borrowed`)
* Date format is `Y-m-d H:i:s
