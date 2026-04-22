# Symfony AI Lab

> Self-hosted, private AI chat powered by the `symfony/ai` bundle, Ollama and FrankenPHP. No API keys, no cloud.

[![CI](https://github.com/LiamBdr/symfony-ai-lab/actions/workflows/ci.yaml/badge.svg)](https://github.com/LiamBdr/symfony-ai-lab/actions/workflows/ci.yaml)
[![PHP 8.5](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Symfony 8.0](https://img.shields.io/badge/Symfony-8.0-000000?logo=symfony&logoColor=white)](https://symfony.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

🤖 **Live demo** → [ai.ipseitech.com](https://ai.ipseitech.com)

## Table of Contents

- [About](#about)
- [Features](#features)
- [Stack](#stack)
- [Requirements](#requirements)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Development](#development)
- [Self-hosting in Production](#self-hosting-in-production)
- [Contributing](#contributing)
- [License](#license)
- [Acknowledgements](#acknowledgements)

## About

Symfony AI Lab is a reference implementation of the [`symfony/ai`](https://github.com/symfony/ai) bundle, the official AI integration for Symfony. It ships a ready-to-deploy private chat interface backed by [Ollama](https://ollama.com/). No API keys, no external LLM provider, everything runs on your own hardware.

## Features

- AI Agent built on `symfony/ai-agent`, orchestrates the conversation through a composable input-processor pipeline
- Local LLMs with Ollama (model swappable via a single environment variable)
- Session-scoped chat history (`symfony/ai-session-message-store`)
- Preline UI + Tailwind CSS v4, served through AssetMapper
- FrankenPHP worker mode with automatic HTTPS
- Production-ready multi-stage Docker image
- Optional self-hosted analytics via Umami (off by default when env vars are empty)

## Stack

| Layer | Tech |
| --- | --- |
| Runtime | [FrankenPHP](https://frankenphp.dev), [Caddy](https://caddyserver.com/) |
| Framework | [Symfony 8.0](https://symfony.com/), PHP 8.5 |
| AI | [`symfony/ai-agent`](https://github.com/symfony/ai), `symfony/ai-bundle`, `symfony/ai-chat`, `symfony/ai-session-message-store`, `symfony/ai-ollama-platform`, [Ollama](https://ollama.com/) |
| Frontend | Twig, AssetMapper, [Tailwind CSS v4](https://tailwindcss.com/), [Preline UI](https://preline.co/), Symfony UX
| Tooling | PHPUnit 13, [PHPStan](https://phpstan.org/), [PHP CS Fixer](https://cs.symfony.com/) |

## Requirements

- Docker Engine with Compose plugin ≥ v2.10
- ~6 GB of free RAM for the Ollama model
- Ports `80` and `443` available on `localhost`
- *(optional)* GNU Make - provides shortcut commands like `make up` / `make test`; everything also works via raw `docker compose exec` invocations

## Quick Start

```bash
git clone https://github.com/LiamBdr/symfony-ai-lab.git
cd symfony-ai-lab
docker compose build --pull --no-cache
docker compose up --wait
docker compose exec php composer install
docker compose exec php bin/console tailwind:build
docker compose exec ollama ollama pull qwen3.5:2b
```

Then open `https://localhost` in your browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334).

To stop everything: `docker compose down --remove-orphans`.

## Configuration

Copy `.env` to `.env.local` and override any of the following:

| Variable | Default | Purpose |
| --- | --- | --- |
| `OLLAMA_HOST_URL` | `http://ollama:11434` | Ollama API endpoint |
| `OLLAMA_CHAT_MODEL` | `qwen3.5:2b` | Model name pulled in Ollama |
| `SERVER_NAME` | `localhost` | Caddy server name(s) |
| `UMAMI_SCRIPT_URL` | *(empty)* | Umami tracker script URL (optional) |
| `UMAMI_WEBSITE_ID` | *(empty)* | Umami website ID (optional) |

## Development

All commands run inside the PHP container, no local PHP install required.

| Command | Purpose |
| --- | --- |
| `make up` | Start containers in the background |
| `make down` | Stop containers |
| `make sh` | Shell into the FrankenPHP container |
| `make test` | Run PHPUnit (pass options via `c=`) |
| `make lint` / `make lint-fix` | Check / fix PHP code style |
| `make stan` | PHPStan static analysis |
| `make tw` | Build Tailwind CSS in watch mode |
| `make before-commit` | Lint-fix + PHPStan + tests (pre-commit gate) |

## Self-hosting in Production

**Reference deployment.** A live version runs at [ai.ipseitech.com](https://ai.ipseitech.com) on a [Hetzner](https://www.hetzner.com/) CPX32 managed via [Dokploy](https://dokploy.com/). The same Compose stack is used in production.

**Analytics.** Usage is tracked with [Umami](https://umami.is/), self-hosted on the same Dokploy instance. Tracking is controlled by the `UMAMI_SCRIPT_URL` and `UMAMI_WEBSITE_ID` environment variables and is disabled when either is empty.

## Contributing

Pull requests and issues are welcome.

- Use [Conventional Commits](https://www.conventionalcommits.org/) (`feat:`, `fix:`, `chore:`, `docs:`, `refactor:`, `test:`).
- Run `make before-commit` before pushing, it runs lint-fix, PHPStan, and tests.
- For larger changes, open an issue first to discuss the approach.

## License

This project is released under the [MIT License](LICENSE).

## Acknowledgements

- The [Symfony AI](https://github.com/symfony/ai) team for the `symfony/ai` bundle.
- [Kévin Dunglas](https://dunglas.dev) for [FrankenPHP](https://frankenphp.dev) and the [`symfony-docker`](https://github.com/dunglas/symfony-docker) template this project bootstrapped from.
- The [Ollama](https://ollama.com/) team for the local LLM runtime.
- [Preline UI](https://preline.co/) for the component library.
