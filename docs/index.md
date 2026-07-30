---
layout: home

hero:
    name: Laravel Query Engine
    text: Schema-aware API filtering engine for Laravel.
    tagline: Automatically generates dynamic API parameters, strict validation, and optimized queries based on Eloquent Models.
    actions:
        - theme: brand
          text: Demo API Doc
          link: https://demo-project.libs.victormgomes.net/docs/api
        - theme: alt
          text: Demo Request
          link: https://demo-project.libs.victormgomes.net/api/users?filters[status][eq]=active&sorts[created_at]=desc&page[number]=1&page[limit]=10
        - theme: brand
          text: Demo JSON URL Request
          link: https://demo-project.libs.victormgomes.net/api/users?filters={"status":{"eq":"active"}}&sorts={"created_at":"desc"}&page={"number":1,"limit":10}

features:
    - title: 🔒 Fully Secure
      details: Automatically detects schema changes and only allows filtering/sorting by allowed attributes.
    - title: ⚡️ High Performance
      details: Optimized SQL query generation without overhead.
    - title: 🛠 Developer Friendly
      details: Clean URL syntax, simple integration into Eloquent models.
---

# 🚀 Quick Start

## 1. Install the package

```bash
composer require victormgomes/laravel-query-engine
```

## 2. Annotate your FormRequest with the attribute and trait

```php
use Victormgomes\LaravelQueryEngine\Attributes\MapQueryEngine;
use Victormgomes\LaravelQueryEngine\Traits\HasQueryEngineRules;

#[MapQueryEngine(User::class)]
class IndexUserRequest extends FormRequest
{
    use HasQueryEngineRules;
}
```

## 3. Build the query

```php
User::paginateQuery($request);
```
