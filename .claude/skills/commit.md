---
name: commit
description: Use when creating git commits in this Symfony project. Enforces Conventional Commits format, single-responsibility rule, and imperative English messages.
---

# Commit

## Overview

All commits follow [Conventional Commits](https://www.conventionalcommits.org/). One commit = one responsibility. Never mix concerns.

## Workflow

1. Review staged changes (`git diff --cached`)
2. Verify all changes belong to a single responsibility
3. If changes span multiple concerns, split into separate commits
4. Write the commit message following the format below
5. Run quality checks before committing: `make lint`, `make stan`, `make test`

## Format

```
<type>: <description>
```

- **type**: `feat`, `fix`, `chore`, `docs`, `refactor`, `test`
- **description**: concise, English, imperative mood, lowercase after prefix
- No period at the end
- No scope required (keep it simple)

## Examples

```
feat: add vector similarity search endpoint
fix: handle null embedding in search results
chore: update phpstan to v2.1
refactor: extract embedding service from controller
test: add integration tests for pgvector queries
docs: document environment variables for pgvector
```

## Red Flags -- STOP and Split

- Staged changes touch both production code and unrelated config
- Mix of new feature code and reformatting/style changes
- Test additions bundled with refactoring of the code under test
- Dependency updates mixed with feature work

If any of these apply: unstage, split into separate commits, each with its own type prefix.

## Common Mistakes

| Mistake | Fix |
|---------|-----|
| `feat: Add endpoint` | Lowercase after prefix: `feat: add endpoint` |
| `feat: add search endpoint.` | No trailing period |
| `update code` | Missing type prefix |
| `feat: add search + refactor services` | Split into two commits |
| `fix: various fixes` | Be specific: `fix: handle null embedding in search results` |

## Quality Gate

Before committing, verify:
- `make lint` -- PSR-12 compliance
- `make stan` -- static analysis passes
- `make test` -- tests pass

If any check fails, fix before committing. Do not commit with known failures.
