# Query Params for Laravel

A powerful, zero-boilerplate package to handle complex API query parameters (filtering, sorting, field selection, relationship loading, and pagination) using native Laravel validation and optimized Eloquent queries.

## 🚀 Features

- **Zero-Boilerplate:** Use PHP 8 Attributes to automate everything.
- **Fancy URLs:** Support for clean, "sentence-style" URL parameters.
- **Native Validation:** Auto-generates strict, type-safe Laravel validation rules from your database schema.
- **Pluggable Drivers:** Built-in support for complex field types like Translatable (JSON or Side-table).
- **Frontend Metadata:** One-click JSON blueprint to build dynamic filter services.
- **High Performance:** Multi-layer caching for rules and model instances.

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

## 🔄 Package Workflow

The package processes every request through four distinct, high-performance stages:

1.  **Normalization:** Translates "Fancy URLs" (e.g., `filter=active:true`) into strict nested arrays.
2.  **Rule Generation:** Inspects your database schema to generate strict Laravel rules (e.g., `boolean`, `integer`). These rules are cached in production for speed.
3.  **Validation:** Laravel's native validator runs against the normalized data using the generated rules.
4.  **Query Building:** Transforms the validated data into highly optimized Eloquent queries, including smart joins for translatable fields.

---

## 🔗 Fancy URL Specification

The package supports "Sentence Style" parameters, allowing you to combine multiple operations into a single query key.

| Operation | URL Key | Fancy Sentence Format | Example |
| :--- | :--- | :--- | :--- |
| **Filtering** | `filter` | `field:operator:value` | `?filter=name:like:Victor,active:true` |
| **Sorting** | `sort` | `field:direction` | `?sort=created_at:desc,name:asc` |
| **Fields** | `fields` | `field1,field2` | `?fields=id,name,email` |
| **Includes** | `include` | `relation1,relation2` | `?include=domains,users` |
| **Pagination** | `page` | `key:value` | `?page=number:2,limit:50` |

### Supported Filter Operators
| Operator | Description | URL Example |
| :--- | :--- | :--- |
| `eq` | Equal (Default) | `status:active` |
| `ne` | Not Equal | `role:ne:admin` |
| `like` | Case-sensitive search | `name:like:John` |
| `ilike` | Case-insensitive search | `email:ilike:HOTMAIL` |
| `gt` / `gte` | Greater than (or equal) | `price:gt:100` |
| `lt` / `lte` | Less than (or equal) | `age:lte:18` |
| `in` / `nin` | In list / Not in list | `id:in:1,2,3` |
| `null` / `notnull` | Is Null / Is Not Null | `deleted_at:null:true` |
| `between` | Between two values | `price:between:10,50` |

---

## 🖥️ Frontend Dynamic Filters (Metadata)

The package provides a "Single Source of Truth" for frontend developers to build dynamic UI filters without hardcoding field names or types.

```php
// In your controller or service
return Resource::getMetadata(User::class);
```

**What it returns:**
- **Filters:** All database columns with their **real types** (boolean, date, int) and allowed operators.
- **Sorts:** Whitelist of columns that support ordering.
- **Includes:** List of relationships including their **Type** (HasMany, etc.) and **Related Model**.
- **Pagination:** Default and max limits.
- **Syntax:** Reminders for the "Fancy URL" patterns.

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

## ⚙️ Configuration Options

| Key | Environment Variable | Default | Description |
| :--- | :--- | :--- | :--- |
| `caching.enabled` | `QUERY_PARAMS_CACHE_ENABLED` | `true` | Enable/Disable rule caching. |
| `caching.ttl` | `QUERY_PARAMS_CACHE_TTL` | `3600` | Cache duration in seconds. |
| `force_cache` | `QUERY_PARAMS_FORCE_CACHE` | `false` | Force caching in non-production environments. |

---

## ⚖️ License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
