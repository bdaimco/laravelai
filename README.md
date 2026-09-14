# Laravel AI CMS foundation

This repository contains a provider-agnostic foundation for a Laravel CMS. It keeps provider credentials in environment variables, stores registry structures as JSON, and makes preview-before-save an application service rule.

## AI providers

`AiService` resolves providers from `config/ai.php`. OpenAI GPT-4, Hugging Face, and Anthropic Claude are included behind the same `AiProvider` contract. Add a provider by implementing that contract and adding its class/config entry; no registry or console code needs to change.

Suggested `.env` values:

```dotenv
AI_PROVIDER=openai
OPENAI_API_KEY=
OPENAI_MODEL=gpt-4
HUGGINGFACE_API_KEY=
HUGGINGFACE_MODEL=mistralai/Mistral-7B-Instruct-v0.2
ANTHROPIC_API_KEY=
ANTHROPIC_MODEL=claude-3-5-sonnet-latest
```

## Registry and console flow

`CmsRegistry.structure` is JSON and can represent modules, plugins, themes, commands, or future registry types. `AiConsoleService::preview()` turns a provider response into a normalized file list and tree. `commit()` requires the exact preview fingerprint, so a caller cannot save an unreviewed or changed response accidentally.

The Filament layer should bind its provider dropdown to `AiConsoleService::providers()`, render `AiPreview::tree()`, and pass the displayed fingerprint back to `commit()`. This keeps the UI replaceable and the safety rule below the UI.

## Upgrade path

- Run `composer update` on a reviewed staging branch and execute migrations before production promotion.
- Add a migration for every new registry capability; do not overload existing JSON keys without a version change.
- Keep provider SDKs and credentials isolated in provider adapters and `.env` configuration.
- Export/import should serialize `CmsRegistry` rows, including `structure`, `version`, and `ai_provider`.
- CI should promote staging to production with a rollback point before migrations or registry imports.
- Planned extensions are syntax-highlighted previews, registry export/import, multi-tenant provider settings, a plugin marketplace, and automated rollback.

## GitHub CI/CD

`.github/workflows/laravel.yml` runs tests and builds a release artifact for pull requests. A push to `main` deploys that artifact to the `staging` environment. Production deployment is started manually from the workflow dispatch menu and should use required reviewers on the `production` environment. The same menu can roll back to a retained release by commit SHA.

The workflow validates Composer and PHP, runs the Laravel migration/test path, builds a release artifact, and deploys only when `composer.json`, `artisan`, and `.env.example` are present. These bootstrap files are included now; the readiness gate remains as protection against publishing an incomplete checkout.

Configure `APP_URL` as a GitHub Environment variable if the staging or production environment has a URL. Production artifact publishing does not require SSH secrets; configure approval rules on the `production` Environment if manual review is required before publishing.

For `staging`, optionally configure `DEPLOY_PATH` and create `$DEPLOY_PATH/shared/.env` on the self-hosted runner. If `DEPLOY_PATH` is omitted, the workflow uses `<runner-workspace>\staging`; if the shared `.env` is omitted, it creates one from `.env.example` and generates an application key. Configure a persistent environment file for real staging settings.

The staging deployment runs locally on the self-hosted GitHub Actions runner and does not require SSH. The Windows runner must provide Windows PowerShell, PHP, Composer, and `tar`, with a shared environment file at `$DEPLOY_PATH/shared/.env`. Releases are stored at `$DEPLOY_PATH/releases/<commit-sha>` and the live release is selected through `$DEPLOY_PATH/current` as a directory junction on Windows. Keep previous release directories available until the rollback retention window has passed.

The manual production action publishes the tested archive as a `production-<commit-sha>` GitHub Actions artifact for download or promotion by a separate production system. It does not require production SSH credentials.