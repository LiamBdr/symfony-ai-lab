---
name: planning-and-refactoring
description: Use when asked to plan a feature, execute an implementation plan, or perform structural refactoring across files in a Symfony project
---

# Planning and Refactoring Workflow

## Overview

Discipline framework for planning features and executing structural changes. Core principle: plan first, execute in phases, verify between each phase.

## When to Use

- Asked to plan a feature or architectural change
- Given an existing plan to execute
- Performing multi-file refactors or renames
- Deleting or restructuring files

## Planning Rules

**When asked to plan:** Output only the plan. No code until explicitly told to proceed.

**When given a plan:** Follow it exactly. Flag real problems and wait for confirmation before deviating.

**Non-trivial features (3+ steps or architectural decisions):** Ask about implementation, UX, and tradeoffs before writing code. Do not assume answers.

## Execution Rules

### Multi-File Changes

Never attempt multi-file refactors in one response. Break into phases:

1. Complete one phase
2. Verify it works (`make test`, `make stan`, `make lint`)
3. Get approval
4. Continue to next phase

### Pre-Refactor Cleanup

Before any structural refactor on a large file, first remove dead code:

- Unused imports (`use` statements)
- Orphan methods (unreferenced private/protected methods)
- Debug statements (`dump()`, `dd()`, `var_dump()`, `error_log()`)

Commit cleanup separately from the structural change.

### Renames and Signature Changes

On any rename or signature change, search separately for:

| Target | Search strategy |
|--------|----------------|
| Direct calls | Grep for method/function name |
| Type references | Grep for class name in typehints, return types, PHPDoc |
| Imports | Grep for `use Namespace\ClassName` |
| Service references | Check `config/services.yaml` and any YAML/XML configs |
| Test mocks | Grep in `tests/` for mocked class or method |

### File Deletion

Never delete a file without verifying nothing references it:

1. Grep the entire codebase for the filename and class name
2. Check service configuration files
3. Check template references (`templates/`)
4. Verify no test depends on it

## Verification Commands

```bash
make test              # Run PHPUnit
make stan              # PHPStan static analysis
make lint              # PHP CS Fixer dry-run (PSR-12)
```

Run all three after each phase before requesting approval to continue.

## Red Flags - STOP and Reassess

- Writing code when only a plan was requested
- Skipping verification between phases
- Changing a method signature without searching for callers
- Deleting a file without grepping for references
- Combining cleanup and feature work in one commit
