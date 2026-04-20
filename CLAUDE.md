# Symfony AI LAB

## Stack

- PHP 8.5 / Symfony 8.0
- FrankenPHP (Docker, multi-stage)
- Ollama (local LLM runtime) via `symfony/ai-ollama-platform`
- Twig + AssetMapper (importmap, no Webpack/Vite)
- Tailwind CSS v4 (`symfonycasts/tailwind-bundle`)
- Preline UI (component library, theme in `assets/styles/themes/theme.css`)

## Build & Test

All commands run inside Docker via Make:

- `make up` — Start containers
- `make test` — Run PHPUnit (`make test c="--filter=ClassName"`)
- `make lint` — PHP CS Fixer dry-run (PSR-12)
- `make lint-fix` — Fix code style
- `make stan` — PHPStan static analysis
- `make sh` — Shell into PHP container
- `make tw` — Build Tailwind CSS in watch mode

## Project Decisions

- Autowired services by default. No XML configuration.
- Environment variables for all infrastructure configuration.
- Standard Symfony structure: `src/`, `config/`, `public/`, `templates/`, `tests/`.
