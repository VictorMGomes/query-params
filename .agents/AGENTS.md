# AI Developer Guidelines for `laravel-query-engine`

You are an AI coding assistant helping to develop and maintain the
`victormgomes/laravel-query-engine` package. This package is a schema-aware API
filtering engine for Laravel that handles complex query parameters (filtering,
sorting, field selection, relationship loading, and pagination) using native
Laravel validation and highly optimized Eloquent queries.

For general coding standards (commit conventions, code quality, testing workflow),
refer to the synced `.agents/AGENTS.md` from `laravel-libs-shared`.

The following rules are **package-specific** to `laravel-query-engine`:

## Architecture & Design Principles

- **Database Agnosticism:** Never write raw SQL unless it is absolutely
  necessary and isolated to a specific database driver (e.g.,
  `PostgresHandler`). Always prefer native Eloquent Builder methods to guarantee
  compatibility across MySQL, PostgreSQL, SQLite, and SQL Server.
- **Security & Visibility:** The package is designed to be secure by default.
  Always respect the model's `$visible` and `$hidden` attributes. Unrecognized
  parameters must fail validation, not fail silently or execute blindly.
- **Delegation & Strategy Pattern:** Keep the codebase modular and avoid God
  Classes.
  - `QueryNormalizer` must delegate all specific logic to focused `Normalizer`
      classes (e.g., `FiltersNormalizer`).
  - `Resource` must delegate metadata gathering to specialized `Generator`
      classes (e.g., `FilterGenerator`).
  - Generators should use the **Instantiable Generator Pattern**, avoiding
      static monolithic methods.
  - Filter operations and database drivers must be cleanly delegated to
      focused Handler classes (e.g. `StringHandler`).
- **Performance:** Avoid runtime schema introspection overhead when possible.
  The package relies on caching mechanisms for schema validation rules.

## Package-Specific Testing Notes

- **Database Tests:** Always review `tests/TestCase.php` to understand the
  available test schema and database configuration before writing new
  database-dependent tests.
