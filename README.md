# Query Params

[![Latest Version on Packagist](https://img.shields.io/packagist/v/victormgomes/query-params.svg?style=flat-square)](https://packagist.org/packages/victormgomes/query-params)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/victormgomes/query-params/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/victormgomes/query-params/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/victormgomes/query-params/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/victormgomes/query-params/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/victormgomes/query-params.svg?style=flat-square)](https://packagist.org/packages/victormgomes/query-params)
[![License](https://img.shields.io/packagist/l/victormgomes/query-params.svg?style=flat-square)](https://packagist.org/packages/victormgomes/query-params)

**Automatically generate query parameters from Eloquent models**

---

## Introduction

**Query Params** provides a robust, zero-configuration way to handle filtering, sorting, and pagination in your Laravel APIs. It automatically generates validation rules based on your Eloquent models and builds the corresponding queries, saving you from writing repetitive boilerplate.

### Why use this package?

*   **Automatic Rules**: Instantly generate validation rules for filters, sorting, and relations based on your Model's structure.
*   **Powerful Filtering**: Supports advanced operations like equals, not equals, greater than, less than, and more.
*   **Documentation Ready**: Works seamlessly with tools like Scramble or Swagger to automatically document your API's query parameters.
*   **Clean Controllers**: Keep your controller logic minimal by offloading query building to a dedicated, model-aware builder.

---

## Support us

We invest a lot of resources into creating [best in class open source packages](https://github.com/victormgomes). You can support us by [sponsoring us on GitHub](https://github.com/sponsors/VictorMGomes).

---

## Installation

```bash
composer require victormgomes/query-params
```

---

## Usage

### 1. Rules Generation
Inject automatic query parameter rules into your `FormRequest`:

```php
use Victormgomes\QueryParams\Rules;

class UserIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return Rules::generate(User::class);
    }
}
```

### 2. Query Building
Use the builder in your controller to automatically apply filters, sorting, and pagination:

```php
use Victormgomes\QueryParams\QueryBuilder;

class UserController extends Controller
{
    public function index(UserIndexRequest $request)
    {
        $users = QueryBuilder::build(User::class, $request);

        return response()->json($users);
    }
}
```

---

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Victor M. Gomes](https://github.com/VictorMGomes)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
