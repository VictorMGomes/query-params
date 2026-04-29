# Query Params for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/victormgomes/query-params.svg?style=flat-square)](https://packagist.org/packages/victormgomes/query-params)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/victormgomes/query-params/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/victormgomes/query-params/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/victormgomes/query-params/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/victormgomes/query-params/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/victormgomes/query-params.svg?style=flat-square)](https://packagist.org/packages/victormgomes/query-params)
[![License](https://img.shields.io/packagist/l/victormgomes/query-params.svg?style=flat-square)](https://packagist.org/packages/victormgomes/query-params)

A powerful, zero-boilerplate package to handle complex API query parameters (filtering, sorting, field selection, relationship loading, and pagination) using native Laravel validation and optimized Eloquent queries.

## 🚀 Features

- **Zero-Config Relations:** Auto-maps `snake_case` aliases and Foreign Keys for all Eloquent relationships.
- **Intelligent Queries:** Automatically optimizes relationship filters to use FK columns for better DB performance.
- **Strict Security:** Native enforcement of Eloquent's `$visible` and `$hidden` attributes.
- **Definitive Metadata:** A specialized, deduplicated JSON structure for building dynamic frontend filters.
- **Fancy URLs:** Support for clean, "sentence-style" URL parameters.
- **Native Validation:** Auto-generates strict, type-safe Laravel validation rules from your database schema.
- **Pluggable Drivers:** Built-in support for complex field types like Translatable (JSON or Side-table).

---

## 📦 Installation

1. Install the package via composer:

```bash
composer require victormgomes/query-params
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --tag="query-params-config"
```

---

## ⚡ Quick Start

Simply add the `#[MapQueryParams]` attribute and the `HasQueryParams` trait to your `FormRequest`:

```php
use Victormgomes\QueryParams\Attributes\MapQueryParams;
use Victormgomes\QueryParams\Concerns\HasQueryParams;
use App\Models\User;

#[MapQueryParams(User::class)]
class IndexUserRequest extends FormRequest
{
    use HasQueryParams;
}
```

In your controller:

```php
public function index(IndexUserRequest $request)
{
    // The QueryBuilder uses the validated and normalized data automatically
    return QueryBuilder::build(User::class, $request);
}
```

---

## 🛡️ Security & Visibility

The package strictly follows Laravel's internal visibility rules. Your API will never leak sensitive data because it respects:

1.  **`$visible` (Allow-list):** If defined, only these fields will be exposed in metadata and available for query operations.
2.  **`$hidden` (Deny-list):** Fields in this array are strictly excluded from all operations.

---

## 🔗 Fancy URL Specification

The package supports "Sentence Style" parameters, allowing you to combine multiple operations into a single query key.

| Operation      | URL Key   | Fancy Sentence Format  | Example                                |
| :------------- | :-------- | :--------------------- | :------------------------------------- |
| **Filtering**  | `filter`  | `field:operator:value` | `?filter=name:like:Victor,active:true` |
| **Sorting**    | `sort`    | `field:direction`      | `?sort=created_at:desc,name:asc`       |
| **Fields**     | `fields`  | `field1,field2`        | `?fields=id,name,email`                |
| **Includes**   | `include` | `relation1,relation2`  | `?include=domains,active_tags`         |
| **Pagination** | `page`    | `key:value`            | `?page=number:2,limit:50`              |

### Zero-Config Relationship Aliases
You don't need to worry about naming conventions. The package automatically discovers:
- **Snake Case:** `active_tags` in URL maps to `activeTags()` in Model.
- **Foreign Keys:** `people_id` in URL maps to `people()` relation.
- **Intelligent Filtering:** `?filter=people:1` is automatically optimized to `WHERE people_id = 1`.

### Supported Filter Operators

| Operator           | Description              | URL Example             |
| :----------------- | :----------------------- | :---------------------- |
| `eq`               | Equal (Default)          | `status:active`         |
| `ne`               | Not Equal                | `role:ne:admin`         |
| `like`             | Case-sensitive search    | `name:like:John`        |
| `ilike`            | Case-insensitive search  | `email:ilike:HOTMAIL`   |
| `gt` / `gte`       | Greater than (or equal)  | `price:gt:100`          |
| `lt` / `lte`       | Less than (or equal)     | `age:lte:18`            |
| `in` / `nin`       | In list / Not in list    | `id:in:1,2,3`           |
| `null` / `notnull` | Is Null / Is Not Null    | `deleted_at:null:true`  |
| `between`          | Between two values       | `price:between:10,50`   |
| `contains`         | JSON/Array contains      | `tags:contains:urgent`  |
| `fts`              | Full Text Search         | `content:fts:laravel`   |

---

## 🖥️ Frontend Dynamic Filters (Metadata)

The package provides a curated "Definitive Metadata" structure for frontend developers to build dynamic UI filters. It deduplicates aliases and only shows the most relevant names.

```php
// In your controller or service
return Resource::getDefinitiveMetadata(User::class);
```

**Example JSON Response:**

```json
{
    "model": "Contact",
    "filters": {
        "name": { "type": "string", "operations": ["eq", "like", "ilike"] },
        "people": { "type": "relation_id", "operations": ["eq", "in"] }
    },
    "sorts": ["id", "name", "people"],
    "includes": {
        "people": { "related": "People", "type": "BelongsTo" },
        "active_tags": { "related": "Tag", "type": "BelongsToMany" }
    },
    "pagination": {
        "keys": ["number", "limit"],
        "defaults": { "limit": 10, "max_limit": 100 }
    }
}
```

---

## 🛠️ Advanced: Pluggable Drivers

Extend the package to handle custom database behaviors by defining a Resolver.

```php
// config/query-params.php
'drivers' => [
    'translatable' => \App\Support\QueryDrivers\TranslationDriver::class,
],
```

Your driver must implement the `Victormgomes\QueryParams\Contracts\FieldResolver` interface to handle custom filtering, sorting, and output transformation.

---

## ⚖️ License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
