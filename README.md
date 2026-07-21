# Laravel Crudify — Generic CRUD Controller for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tahamiskini/crudify.svg?style=flat-square)](https://packagist.org/packages/tahamiskini/crudify)
[![Total Downloads](https://img.shields.io/packagist/dt/tahamiskini/crudify.svg?style=flat-square)](https://packagist.org/packages/tahamiskini/crudify)
[![License](https://img.shields.io/packagist/l/tahamiskini/crudify?style=flat-square)](https://github.com/tahamiskini/crudify/blob/main/LICENSE.md)

Crudify eliminates repetitive boilerplate by providing a single, convention-based CRUD controller for all your Eloquent models. One `Route::crud()` call gives you a full RESTful API — including mass operations, relation management, query filtering, and event hooks.

**When to use it:** CRUD-heavy, convention-driven codebases — admin panels, REST APIs, back-office dashboards where most models follow a predictable create/read/update/delete pattern.

**When to skip it:** Heavily event-driven/CQRS architectures, endpoints with unique business rules per operation, read-only or calculation-heavy apps, or cases where every response needs a completely custom format.

- [Installation](#installation)
- [Usage](#usage)
  - [Quick Start](#quick-start)
  - [Route Macro](#route-macro)
  - [Convention & Auto-Discovery](#convention--auto-discovery)
  - [Naming Conventions & Customization](#naming-conventions--customization)
- [API Endpoints](#api-endpoints)
  - [Single-Resource CRUD](#single-resource-crud)
  - [Mass Operations](#mass-operations)
  - [Relation Management](#relation-management)
- [Form Requests (Validation)](#form-requests-validation)
- [Policies (Authorization)](#policies-authorization)
- [Query Filtering & Sorting](#query-filtering--sorting)
  - [Built-in Filter Operators](#built-in-filter-operators)
  - [Custom Filter Operators](#custom-filter-operators)
  - [Includes (Eager Loading)](#includes-eager-loading)
  - [Sorting](#sorting)
- [API Resources (Response Transformation)](#api-resources-response-transformation)
- [Events](#events)
- [Configuration](#configuration)
- [Auto-Generate Routes](#auto-generate-routes)
- [Extending the Controller](#extending-the-controller)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [License](#license)

## Installation

```bash
composer require tahamiskini/crudify
```

Publish the config file:

```bash
php artisan vendor:publish --tag="crudify-config"
```

## Usage

### Quick Start

Create a model, policy, and form request following the naming conventions, then register the routes:

```php
// routes/api.php
use App\Models\Post;
use Illuminate\Support\Facades\Route;

Route::crud('posts', Post::class);
```

That's it. You now have a full CRUD API at `/api/posts`.

### Route Macro

The `Route::crud()` macro registers **13 endpoints** for a given resource:

```php
Route::crud('posts', Post::class);
```

**Signature:**

```php
Route::crud(
    string $resource,        // URI segment, e.g. 'posts'
    string $model,           // Model class, e.g. Post::class
    array $options = []      // Optional: ['namespace' => 'App']
);
```

Options:
- `namespace` — Override the root namespace (default: `App`)

### Convention & Auto-Discovery

Crudify resolves models, form requests, and policies by convention:

| Component | Convention | Example |
|---|---|---|
| **Model** | `{namespace}\Models\{ModelName}` | `App\Models\Post` |
| **Form Request** | `{namespace}\Http\Requests\{ModelName}Request` | `App\Http\Requests\PostRequest` |
| **Policy** | `{namespace}\Policies\{ModelName}Policy` | `App\Policies\PostPolicy` |
| **Fallback Policy** | `Taha\Crudify\Policies\CrudifyPolicy` (permissive) | — |

If a form request class does not exist, the controller falls back to `CrudifyRequest` (no validation). If a policy class does not exist, it falls back to `CrudifyPolicy` (all operations allowed).

### Naming Conventions & Customization

For a model named `Post`, the following files are auto-discovered:

```
app/
├── Models/
│   └── Post.php
├── Http/
│   └── Requests/
│       └── PostRequest.php
└── Policies/
    └── PostPolicy.php
```

The `studly()` method handles kebab-case and snake-case conversion:
- `blog-post` → `BlogPost`
- `blog_post` → `BlogPost`

## API Endpoints

All endpoints are registered under the configured prefix (default: `api`).

### Single-Resource CRUD

| Method | URI | Controller Method | Description |
|---|---|---|---|
| `GET` | `/api/posts` | `readMore` | Paginated list |
| `GET` | `/api/posts/{id}` | `readOne` | Single resource |
| `POST` | `/api/posts` | `create` | Create resource |
| `PUT`/`PATCH` | `/api/posts/{id}` | `update` | Update resource |
| `DELETE` | `/api/posts/{id}` | `delete` | Delete resource (returns 204) |

**Response format** (single resource):
```json
{
  "data": {
    "id": 1,
    "title": "Hello World",
    "body": "My first post",
    "published": true,
    "created_at": "2026-01-01T00:00:00.000000Z",
    "updated_at": "2026-01-01T00:00:00.000000Z"
  }
}
```

**Response format** (paginated list):
```json
{
  "data": [
    { "id": 1, "title": "Hello World", ... }
  ],
  "links": { ... },
  "meta": { "current_page": 1, "last_page": 1, "per_page": 20, "total": 3 }
}
```

### Mass Operations

| Method | URI | Controller Method | Description |
|---|---|---|---|
| `POST` | `/api/posts/mass-create` | `massCreate` | Create multiple records |
| `PUT`/`PATCH` | `/api/posts/mass-update` | `massUpdate` | Update multiple records |
| `DELETE` | `/api/posts/mass-delete` | `massDelete` | Delete multiple records |
| `POST` | `/api/posts/mass-create-or-update` | `massCreateOrUpdate` | Upsert records |

**Mass create request:**
```json
{
  "items": [
    { "title": "Post 1", "body": "Content 1" },
    { "title": "Post 2", "body": "Content 2" }
  ]
}
```

**Mass update request:**
```json
{
  "items": [
    { "id": 1, "title": "Updated Title" },
    { "id": 2, "title": "Another Update" }
  ]
}
```

**Mass delete request:**
```json
{
  "items": [
    { "id": 1 },
    { "id": 2 }
  ]
}
```

**Mass create-or-update request:**
```json
{
  "items": [
    { "id": 1, "title": "Update existing" },
    { "title": "Create new" }
  ]
}
```

### Relation Management

Crudify supports managing Eloquent relationships through dedicated endpoints.

| Method | URI | Description |
|---|---|---|
| `POST` | `/api/posts/{id}/add-relation/{relationField}` | Add a related model (create child for HasMany / attach pivot for BelongsToMany without detaching) |
| `DELETE` | `/api/posts/{id}/remove-relation/{relationField}/{relationId?}` | Remove a related model (delete child / detach pivot) |
| `POST` | `/api/posts/{id}/attach-relation/{relationField}/{relationId}` | Attach an existing model to the relation (reassign HasMany / sync-without-detach for BelongsToMany) |
| `DELETE` | `/api/posts/{id}/detach-relation/{relationField}/{relationId}` | Detach a related model (remove pivot / nullify FK) |

**Example — add a tag to a post (BelongsToMany):**
```bash
curl -X POST /api/posts/1/add-relation/tags \
  -H 'Content-Type: application/json' \
  -d '{"name": "New Tag"}'
```

**Example — attach existing tag (BelongsToMany):**
```bash
curl -X POST /api/posts/1/attach-relation/tags/5
```

**Example — remove (detach) a tag:**
```bash
curl -X DELETE /api/posts/1/detach-relation/tags/5
```

**Example — add a comment to a post (HasMany):**
```bash
curl -X POST /api/posts/1/add-relation/comments \
  -H 'Content-Type: application/json' \
  -d '{"body": "Great post!"}'
```

**Supported relation types:**

| Relation Type | add | remove | attach | detach |
|---|---|---|---|---|
| `BelongsToMany` / `MorphToMany` | Creates related model + syncs without detach | Detaches pivot | Syncs without detach | Detaches pivot |
| `HasMany` / `MorphMany` | Creates child | Deletes child | Reassigns child FK | Nullifies child FK |
| `BelongsTo` | — | — | — | — |
| `HasOne` | — | — | — | — |
| `MorphTo` | — | — | — | — |

## Form Requests (Validation)

Create a form request following the naming convention to define validation rules:

```php
<?php

namespace App\Http\Requests;

use Taha\Crudify\CrudifyRequest;

class PostRequest extends CrudifyRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'published' => 'boolean',
        ];
    }
}
```

If no form request class exists, the controller falls back to `CrudifyRequest` which allows all fields (empty `rules()`).

## Policies (Authorization)

Create a policy following the naming convention. Crudify supports these authorization gates:

| Method | Gate | Authorizes |
|---|---|---|
| `readOne` | `readOne` | Viewing a single resource |
| `readMore` | `readMore` | Listing resources |
| `create` | `create` | Creating a resource |
| `update` | `update` | Updating a resource |
| `delete` | `delete` | Deleting a resource |
| `massCreate` | `massCreate` | Mass-creating resources |
| `massUpdate` | `massUpdate` | Mass-updating resources |
| `massDelete` | `massDelete` | Mass-deleting resources |
| `massCreateOrUpdate` | `massCreateOrUpdate` | Upserting resources |

```php
<?php

namespace App\Policies;

use App\Models\Post;
use Illuminate\Foundation\Auth\User;

class PostPolicy
{
    public function readMore(?User $user): bool
    {
        return true;
    }

    public function readOne(?User $user, Post $post): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        return auth()->check();
    }

    public function update(?User $user, Post $post): bool
    {
        return $user?->id === $post->user_id;
    }

    public function delete(?User $user, Post $post): bool
    {
        return $user?->id === $post->user_id;
    }

    public function massCreate(?User $user): bool
    {
        return auth()->check();
    }

    public function massUpdate(?User $user): bool
    {
        return auth()->check();
    }

    public function massDelete(?User $user): bool
    {
        return auth()->check();
    }

    public function massCreateOrUpdate(?User $user): bool
    {
        return auth()->check();
    }
}
```

The default `CrudifyPolicy` allows everything (`true` for all gates). Policy resolution uses Laravel's `Gate::guessPolicyNamesUsing` — Crudify registers a guesser that looks for `{ModelNamespace}\Policies\{ModelName}Policy` and falls back to `CrudifyPolicy`.

## Query Filtering & Sorting

Crudify integrates with [spatie/laravel-query-builder](https://github.com/spatie/laravel-query-builder) and adds a custom filter parser with operator-prefixed keys.

### Built-in Filter Operators

Use the `filter` query parameter with operator-prefixed keys:

```
GET /api/posts?filter[eq:status]=published
GET /api/posts?filter[contains:title]=Hello
GET /api/posts?filter[gt:price]=100&filter[lt:price]=500
GET /api/posts?filter[in:status]=draft,published
GET /api/posts?filter[between:price]=100,500
GET /api/posts?filter[isNull:deleted_at]
```

| Operator | SQL | Example |
|---|---|---|
| `eq` (default) | `field = value` | `eq:status=published` |
| `neq` / `noteq` / `notEq` | `field != value` | `neq:status=draft` |
| `contains` | `field LIKE '%value%'` | `contains:title=Hello` |
| `startsWith` / `startswith` | `field LIKE 'value%'` | `startsWith:title=Hel` |
| `endsWith` / `endswith` | `field LIKE '%value'` | `endsWith:title=orld` |
| `gt` | `field > value` | `gt:price=100` |
| `gte` | `field >= value` | `gte:price=100` |
| `lt` | `field < value` | `lt:price=200` |
| `lte` | `field <= value` | `lte:price=200` |
| `in` | `field IN (values)` | `in:status=draft,published` |
| `notIn` / `notin` | `field NOT IN (values)` | `notIn:status=archived` |
| `isNull` / `isnull` | `field IS NULL` | `isNull:deleted_at` |
| `notNull` / `notnull` | `field IS NOT NULL` | `notNull:published_at` |
| `between` | `field BETWEEN v1 AND v2` | `between:price=100,500` |

### Custom Filter Operators

Register custom filter operators in your `AppServiceProvider`:

```php
use Taha\Crudify\Facades\FilterParser;

FilterParser::registerFilter('regex', new class implements \Taha\Crudify\Services\Filter\Contracts\FilterContract {
    public function apply(QueryBuilder $query, string $field, mixed $value): QueryBuilder
    {
        return $query->where($field, 'REGEXP', $value);
    }
});
```

Then use it:
```
GET /api/posts?filter[regex:title]=^Hello
```

### Includes (Eager Loading)

```
GET /api/posts?include=tags,comments.user
```

### Sorting

```
GET /api/posts?sort=-created_at
GET /api/posts?sort=title
GET /api/posts?sort=author.name
```

Prefix with `-` for descending order. Dot-notation sorts (e.g., `author.name`) are supported for related columns.

**Pagination:**
```
GET /api/posts?per_page=50
```

Default is 20 items per page.

## API Resources (Response Transformation)

Crudify uses `CrudifyResource` (extends `JsonResource`) for response transformation.

**Dynamic appends:**
```
GET /api/posts/1?append=full_name,summary
```

**Dynamic visibility:**
```
GET /api/posts/1?visible=title,body
```

You can override the resource class by extending the controller and overriding `createResource()` / `createResourceCollection()`.

## Events

Crudify dispatches events before and after each CRUD operation. All events carry a public `$actionPayload` property.

| Event | Dispatched by |
|---|---|
| `CrudModelBeforeCreate` | `Create::run()` |
| `CrudModelAfterCreate` | `Create::run()` |
| `CrudModelBeforeUpdate` | `Update::run()` |
| `CrudModelAfterUpdate` | `Update::run()` |
| `CrudModelBeforeDelete` | `Delete::run()` |
| `CrudModelAfterDelete` | `Delete::run()` |
| `CrudModelBeforeAddRelation` | `AddRelation::run()` |
| `CrudModelAfterAddRelation` | `AddRelation::run()` |
| `CrudModelBeforeRemoveRelation` | `RemoveRelation::run()` |
| `CrudModelAfterRemoveRelation` | `RemoveRelation::run()` |
| `CrudModelBeforeAttachRelation` | `AttachRelation::run()` |
| `CrudModelAfterAttachRelation` | `AttachRelation::run()` |
| `CrudModelBeforeDetachRelation` | `DetachRelation::run()` |
| `CrudModelAfterDetachRelation` | `DetachRelation::run()` |

**Example listener:**
```php
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Taha\Crudify\Events\CrudModelAfterCreate;
use Taha\Crudify\Events\CrudModelAfterUpdate;
use Taha\Crudify\Events\CrudModelBeforeDelete;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(function (CrudModelAfterCreate $event) {
            Log::info('Model created', [
                'model' => get_class($event->actionPayload->getModel()),
                'data' => $event->actionPayload->getData(),
            ]);
        });

        Event::listen(function (CrudModelBeforeDelete $event) {
            $model = $event->actionPayload->getModel();
            // Perform cleanup before deletion
        });
    }
}
```

## Configuration

Published config (`config/crudify.php`):

```php
<?php

return [
    'namespace' => env('CRUDIFY_NAMESPACE', 'App'),

    'routes_prefix' => 'api',

    'middlewares' => [],

    'merge_model_data_to_request' => false,

    'auto_sync_parent_relations' => false,

    'sync_parent_relations_max_depth' => 5,
];
```

| Option | Default | Description |
|---|---|---|
| `namespace` | `'App'` | Root namespace for model/request/policy resolution |
| `routes_prefix` | `'api'` | URI prefix for auto-generated routes |
| `middlewares` | `[]` | Middleware stack applied to all CRUD routes |
| `merge_model_data_to_request` | `false` | Merge existing model data into update request for validation |
| `auto_sync_parent_relations` | `false` | Auto-resolve parent IDs for HasMany relations |
| `sync_parent_relations_max_depth` | `5` | Max recursion depth for parent relation resolution |

## Auto-Generate Routes

Crudify provides an artisan command to scan your `app/Models` directory and generate `Route::crud()` calls automatically:

```bash
php artisan crudify:generate-routes
```

Options:

| Option | Description |
|---|---|
| `--dir` | Custom directory to scan for models |
| `--output` | Custom output path for the generated routes file (default: `routes/crudify.php`) |

**Example:**
```bash
php artisan crudify:generate-routes --dir=app/Models --output=routes/crud.php
```

Generated output (`routes/crudify.php`):
```php
<?php

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->group(function () {
        Route::crud('posts', Post::class);
        Route::crud('tags', Tag::class);
    });
```

Don't forget to include the generated file in your route loading:
```php
// routes/api.php
require __DIR__.'/crudify.php';
```

## Extending the Controller

Override any action by extending `CrudifyController` and using custom routes:

```php
<?php

namespace App\Http\Controllers;

use Taha\Crudify\CrudifyController;
use Taha\Crudify\Actions\ActionPayloadInterface;
use Taha\Crudify\Actions\ExecutableActionResponseContract;

class PostController extends CrudifyController
{
    protected function onCreate(ActionPayloadInterface $actionPayload): ExecutableActionResponseContract
    {
        $data = $actionPayload->getData();
        $data['user_id'] = auth()->id();

        $payload = $this->createActionPayload(
            request(),
            $actionPayload->getModel(),
            $data
        );

        return $this->getCreateAction()->run($payload);
    }

    protected function getCreateAction(): \Taha\Crudify\Actions\ExecutableAction
    {
        return resolve(\App\Actions\CustomCreate::class);
    }

    protected function createResource(\Illuminate\Database\Eloquent\Model $resource): \Taha\Crudify\CrudifyResource
    {
        return new \App\Http\Resources\PostResource($resource);
    }
}
```

Then use your custom controller in routes:

```php
Route::match(['GET'], '/posts/{id}', [PostController::class, 'readOne']);
```

## Testing

```bash
composer test
```

The test suite uses Orchestra Testbench with an in-memory SQLite database. Tests cover:

- All CRUD action events (before/after create, update, delete)
- Mass operations (create, update, delete, create-or-update)
- Relation management events
- Route macro registration (all 13 endpoints)
- Route auto-generation command
- Filter parser (all 14 operators, custom operators, invalid operator exceptions)
- Query parser (includes, sorts, dot-notation, grouped includes)
- Full controller integration (read, create, update, delete against real database)
- Policy resolution and default policy behavior
- API resource transformation (append, visible, null resource)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
