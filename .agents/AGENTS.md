# AI Developer Guidelines

You are an AI coding assistant helping to develop and maintain a Laravel package
from the `victormgomes` ecosystem. Whenever you work on any of these projects,
you **must** strictly adhere to the following rules.

For package-specific guidelines (architecture, design patterns, domain rules),
refer to the `.agents/AGENTS.md` file in each repository.

## 1. Commit Standards (Conventional Commits)

All commit messages **must be in English** and follow the Conventional Commits
specification. Use one of the following prefixes:

- **Breaking Changes:** If a commit introduces a breaking change (requiring a
  major version bump), append an exclamation mark `!` to the prefix (e.g.,
  `feat!:`, `refactor!:`).

- `feat`: A new feature.
- `fix`: A bug fix.
- `docs`: Documentation only changes (e.g., changes to `README.md`, `SKILL.md`,
  or the `docs/` folder).
- `style`: Changes that do not affect the meaning of the code (white-space,
  formatting, missing semi-colons, etc.).
- `refactor`: A code change that neither fixes a bug nor adds a feature.
- `perf`: A code change that improves performance.
- `test`: Adding missing tests or correcting existing tests.
- `build`: Changes that affect the build system or external dependencies (e.g.,
  updating `composer.json`).
- `ci`: Changes to CI configuration files and scripts (e.g.,
  `.github/workflows/`).
- `chore`: Maintenance tasks, refactoring tooling, or other changes that don't
  modify `src/` or `tests/`.
- `revert`: Reverts a previous commit.

## 2. Code Quality & Strict Typing

- **Strict Types:** Every PHP file must start with `declare(strict_types=1);`.
- **Explicit Return Types:** Always specify return types for methods and
  closures. If a function or closure does not return anything, explicitly define
  `: void` (e.g., `function (): void { ... }`).
- **Code Formatting:** Adhere to the formatting tool configured in the project
  (check the `scripts` section in `composer.json`, typically Laravel Pint).
- **Static Analysis:** The project enforces high code quality. Always check
  `composer.json` for the configured static analysis tools (like PHPStan or PHP
  Insights). Avoid `mixed` types when possible and document types using PHPDoc
  if PHP native typing is insufficient.

## 3. Testing & Development Workflow

- **Framework:** Check `composer.json` to determine the active testing framework
  (e.g., Pest).
- **Test-Driven:** Every new feature or bug fix must be accompanied by an
  automated test.
- **IMPORTANT:** Do not run PHP or Composer commands directly on the host.
  Always use the provided Docker environment. Refer to the
  `development_workflow` skill for instructions on discovering and executing QA
  checks.
