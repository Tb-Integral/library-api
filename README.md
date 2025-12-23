# Book Library API

REST API for a mobile book library application.

---

## Tech Stack
- PHP 8.2
- MySQL 8
- PDO
- JWT authorization
- MVC architecture
- Service layer
- REST API

---

## Features
- User registration and authentication
- JWT-protected endpoints
- User list and library sharing
- Book management (CRUD)
- Soft delete and restore books
- Access to other users' libraries
- External book search:
  - Google Books API
  - Mann–Ivanov–Ferber API
- Saving external books to personal library

---

## Database
- MySQL 8
- Database schema follows **Third Normal Form (3NF)**
- Database migrations included

---

## Requirements
- PHP 8.2
- MySQL 8
- Composer

---

## Installation
1. Clone the repository
2. Install dependencies:
    composer install
3. Configure database connection (env / config file)
4. Run migrations:
    php sql/migration.php

---

## Authentication
All protected endpoints require JWT token.

Header: Authorization: Bearer <token>

---

## API Endpoints
1. Auth
- POST /register — user registration
- POST /login — user authentication
- GET /me — get current user info

2. Users
- GET /users — list of users
- POST /users/{id}/share — grant access to library

3. Books
- GET /books — list user books
- POST /books — create book
- GET /books/{id} — get book by id
- PUT /books/{id} — update book
- DELETE /books/{id} — soft delete book
- POST /books/{id}/restore — restore deleted book
- GET /books/shared — books shared by other users

4. External Books
- GET /external/books/search?q=php — search books
- POST /external/books/save — save external book to library

---

## Code Style
- Code formatted using php-cs-fixer
- PSR / PER Coding Style

---

## Notes
- All database queries use prepared statements
- SQL injection protection implemented
- MVC architecture with separated business logic