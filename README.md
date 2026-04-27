# Query Params

[![Latest Version on Packagist](https://img.shields.io/packagist/v/victormgomes/query-params.svg?style=flat-square)](https://packagist.org/packages/victormgomes/query-params)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/victormgomes/query-params/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/victormgomes/query-params/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/victormgomes/query-params/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/victormgomes/query-params/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/victormgomes/query-params.svg?style=flat-square)](https://packagist.org/packages/victormgomes/query-params)

Automatically generate query parameters based on Eloquent Models

## Features

This package provides automatic rules and a builder for query parameters to be used in GET routes for index requests.  
This includes rules generation and a query builder.  
The available parameter operations are:

- Filters – equal, not equal, less than, and many others.  
- Sorting – ascending and descending.  
- Fields – fields to be included in the response.  
- Includes – nested relations to be included in the response.  
- Pagination – page limit and page number.  

## Installation

```bash
composer require victormgomes/query-params
```

## Usage

### Rules generation

First, use the `Rules::generate` method to inject the automatic query parameter rules into the rules of the desired class that extends a `FormRequest`.  
You must also provide a fully qualified class name (FQCN) as an argument, for example:

```php
//app/Http/Requests/Users/UserIndexRequest.php
<?php

namespace App\Http\Requests\Users;

use App\Models\User\User;
use Illuminate\Foundation\Http\FormRequest;
use Victormgomes\QueryParams\Rules;

class UserIndexRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = Rules::generate(User::class);

        return $rules;
    }
}
```

Now the rules for your query parameters can handle incoming requests,  
and can also be automatically described in OpenAPI documentation with tools like Scramble or Swagger.  
Additionally, you can import the OpenAPI JSON into tools like Postman to see all possible query parameters.  

The rules will look like this:

![Alt text](https://raw.githubusercontent.com/VictorMGomes/art/refs/heads/main/query-params/images/rules-example.png)

### Query Builder

In the `index` method of the controller, use the `UserIndexRequest` class as the type of the incoming request.  
Then, use the `QueryBuilder::build` method to build the query for the model,  
passing the FQCN of the model and the request as arguments:

```php
//app/Http/Controllers/User/UserController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UserIndexRequest;
use App\Models\User\User;
use Victormgomes\QueryParams\QueryBuilder;

class UserController extends Controller
{
    public function index(UserIndexRequest $request): JsonResponse
    {
        $users = QueryBuilder::build(User::class, $request);

        return response()->json([
            'status' => 'success',
            'data' => $users,
        ], 200);
    }
...
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Victor M. Gomes](https://github.com/VictorMGomes)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
