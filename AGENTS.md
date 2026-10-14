# Symfony AI LAB

## Stack

- PHP 8.5 / Symfony 8.1
- Symfony AI 0.x (`symfony/ai-*`): pre-1.0, APIs change between minors
- FrankenPHP (Docker, multi-stage)
- Ollama (local LLM runtime) via `symfony/ai-ollama-platform`
- Twig + AssetMapper (importmap, no Webpack/Vite)
- Tailwind CSS v4 (`symfonycasts/tailwind-bundle`)
- Preline UI (component library, theme in `assets/styles/themes/theme.css`)

## Build & Test

All commands run inside Docker via Make:

- `make up` — Start containers
- `make test` — Run PHPUnit (`make test c="--filter=ClassName"`)
- `make lint` — PHP CS Fixer dry-run (`@Symfony` + `@Symfony:risky`, no Yoda)
- `make lint-fix` — Fix code style
- `make stan` — PHPStan static analysis
- `make before-commit` — lint-fix + Tailwind class sort + stan + test
- `make sf c="..."` — Run a `bin/console` command
- `make composer c="..."` — Run Composer
- `make sh` — Shell into PHP container
- `make logs` — Container logs (no Monolog: app logs go to stderr, not `var/log/`)
- `make tw` — Build Tailwind CSS in watch mode

## Project Decisions

- Autowired services by default. No XML configuration.
- Environment variables for all infrastructure configuration. `.env` holds committed defaults; secrets go in `.env.local`.
- Standard Symfony structure: `src/`, `config/`, `public/`, `templates/`, `tests/`.

## Symfony Conventions

- New capability → `make composer c="req <package>"` and let the Flex recipe generate config. Never hand-edit `config/bundles.php`.
- Before hand-writing infrastructure or adding a third-party lib, check whether a Symfony component covers it.
- PHP attributes over YAML: `#[Route]`, `#[Autowire]` (env, params, service ids), `#[Target]`, `#[AsTool]`, `#[AsEventListener]`... A YAML service definition is the last resort (e.g. vendor classes).
- Controllers extend `AbstractController`, stay thin, delegate to services.
- Constructor property promotion; `readonly` for DTOs/value objects. Don't make a service class `readonly` if it may become lazy.
- Maker bundle: pass every argument plus `--no-interaction`, otherwise it hangs waiting for input.
- Tests: `WebTestCase` for HTTP, `KernelTestCase` for services.

## Discover, Don't Guess

Look up framework APIs in the project rather than relying on memory, especially for `symfony/ai-*`:

- `make sf c="debug:autowiring <name>"`, `debug:container`, `debug:router`, `debug:config <bundle>`, `config:dump-reference <bundle>`
- Read source and docblocks under `vendor/symfony/ai-*`
- Validate with `lint:container`, `lint:twig templates/`, `lint:yaml config/` (also run in CI)
- On failure: check the web profiler (`/_profiler`) and `make logs` before changing code
