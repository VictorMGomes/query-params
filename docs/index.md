---
layout: home

hero:
  name: Laravel Query Engine
  text: Schema-aware API filtering engine for Laravel.
  tagline: Automatically generates dynamic API parameters, strict validation, and optimized queries based on Eloquent Models.
  actions:
    - theme: brand
      text: Get Started
      link: /introduction
    - theme: alt
      text: View on GitHub
      link: https://github.com/victormgomes/laravel-query-engine
    - theme: alt
      text: Packagist
      link: https://packagist.org/packages/victormgomes/laravel-query-engine

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
