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

The workflow detects whether `composer.json` exists. Until the Laravel application bootstrap is added, it runs PHP syntax checks and creates a source release artifact while skipping Composer, staging, and production deployment steps. Once `composer.json`, `artisan`, and `.env.example` are committed, Composer validation and the full Laravel deployment pipeline activate automatically.

Configure these GitHub Environment values for both `staging` and `production`:

- Secrets: `SSH_PRIVATE_KEY`, `SSH_HOST`, `SSH_USER`
- Variable: `DEPLOY_PATH` (for example `/var/www/laravelai`)
- Variable: `APP_URL`

The remote host must provide PHP, Artisan-compatible extensions, and a shared environment file at `$DEPLOY_PATH/shared/.env`. Releases are stored at `$DEPLOY_PATH/releases/<commit-sha>` and the live release is selected through `$DEPLOY_PATH/current`. Keep previous release directories available until the rollback retention window has passed.