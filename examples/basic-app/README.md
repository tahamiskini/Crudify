# Crudify Example App

A minimal Laravel 11 app demonstrating the [Crudify](https://github.com/tahamiskini/crudify) package.

## Setup

```bash
cp .env.example .env
composer install
touch database/database.sqlite
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

## Routes

All endpoints are served under `/api`:

| Method   | Endpoint                                  | Description              |
| -------- | ----------------------------------------- | ------------------------ |
| GET      | `/api/posts`                              | List posts (paginated)   |
| GET      | `/api/posts/{id}`                         | Get one post             |
| POST     | `/api/posts`                              | Create a post            |
| PUT/PATCH| `/api/posts/{id}`                         | Update a post            |
| DELETE   | `/api/posts/{id}`                         | Delete a post            |
| POST     | `/api/posts/mass-create`                  | Mass create posts        |
| PUT/PATCH| `/api/posts/mass-update`                  | Mass update posts        |
| DELETE   | `/api/posts/mass-delete`                  | Mass delete posts        |
| POST     | `/api/posts/mass-create-or-update`        | Upsert posts             |
| POST     | `/api/posts/{id}/add-relation/{field}`    | Add relation (hasMany)   |
| DELETE   | `/api/posts/{id}/remove-relation/{field}[/{rid}]` | Remove relation |
| POST     | `/api/posts/{id}/attach-relation/{field}/{rid}`  | Attach relation (BelongsToMany) |
| DELETE   | `/api/posts/{id}/detach-relation/{field}/{rid}`  | Detach relation (BelongsToMany) |

Same pattern applies to `/api/tags`.

### Query filtering (via spatie/laravel-query-builder)

```bash
# Filter
GET /api/posts?filter[title]=Hello
GET /api/posts?filter[published]=1

# Sort
GET /api/posts?sort=-created_at

# Paginate
GET /api/posts?per_page=50
```
