# Contributing

Thank you for considering contributing! This document covers the contribution
workflow that applies to all packages in this ecosystem.

## Contribution Workflow

### 1. Open an Issue First

For non-trivial changes (features, bug fixes, refactoring), **open an issue
before starting work**. This helps align on the approach and prevents wasted
effort. Small fixes (typos, broken links) can go directly to a Pull Request.

### 2. Fork or Branch

| Your access | Workflow |
|---|---|
| **External contributor** | Fork the repository, create a feature branch in your fork |
| **Trusted collaborator** (write access) | Create a feature branch directly in the main repository |

### 3. Create a Feature Branch

```bash
git checkout -b feat/your-feature-name
```

### 4. Make Changes & Ensure Quality

Run the QA checks before opening a PR:

```bash
docker compose run --rm dev composer run format:all
docker compose run --rm dev composer run test
docker compose run --rm dev composer run check:types
```

### 5. Submit a Pull Request

- Provide a clear, descriptive title.
- Explain _why_ you are making the change.
- **Link the PR to the issue** (e.g., `Closes #123`).
- Follow **Conventional Commits** for your commit messages (e.g., `feat:`,
  `fix:`, `docs:`, `chore:`).
- For **Breaking Changes**, use `!` after the type/scope (e.g.,
  `feat!: rewrite core logic`). This triggers a major version release.
- Keep the PR focused on a single feature or bug fix.

## AI-Assisted Development

This project includes AI guidelines for coding assistants. If you are using an
AI tool to help with your contribution, please refer to:

- `.agents/AGENTS.md` — General coding standards and commit conventions
- `.agents/skills/development_workflow/SKILL.md` — Docker commands and QA checks

These guidelines ensure AI-assisted contributions follow the same standards as
manual contributions.
