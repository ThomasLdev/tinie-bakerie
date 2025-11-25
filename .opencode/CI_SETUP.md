# CI/CD Setup Guide

## Overview

This project uses Docker Compose profiles to ensure Node.js only runs when needed:
- **Production**: No Node.js (lean and secure)
- **Development**: Node.js available for linting, formatting, E2E tests
- **CI Pipeline**: Node.js runs only in jobs that need it

## Docker Compose Profiles

### Services by Profile

| Service | Default (Production) | `dev` Profile | Purpose |
|---------|---------------------|---------------|---------|
| php | ✅ | ✅ | Symfony application |
| database | ✅ | ✅ | PostgreSQL |
| redis | ✅ | ✅ | Cache |
| node | ❌ | ✅ | Linting, formatting, E2E tests |

## Commands Reference

### Local Development
```bash
make up-dev      # Start all services including Node.js
make start       # Full dev setup (up-dev + assets + typescript)
make lint        # Run ESLint
make e2e         # Run Playwright tests
```

### Production
```bash
make up          # Start production services (no Node.js)
make verify-prod # Verify Node.js is not running
```

### CI Environment
```bash
make up-ci       # Start services for CI (includes Node.js)
# Or directly:
docker compose --profile dev up -d
```

## CI Pipeline Jobs

### 1. Static Analysis (Needs Node.js)
- **Starts**: `docker compose --profile dev up --wait --no-build`
- **Runs**: PHP static analysis + TypeScript linting
- **Why Node.js**: ESLint, Prettier, TypeScript compiler

### 2. Functional Tests (No Node.js)
- **Starts**: `docker compose up --wait --no-build`
- **Runs**: PHPUnit functional tests
- **Why no Node.js**: PHP-only tests, faster startup

### 3. E2E Tests (Needs Node.js)
- **Starts**: `docker compose --profile dev up --wait --no-build`
- **Runs**: Playwright E2E tests
- **Why Node.js**: Playwright runs in Node.js

## Environment Variables

### CI Variable
```bash
CI=${CI:-false}
```

**Behavior:**
- **Local**: `CI=false` (default) - Interactive mode
- **CI Pipeline**: `CI=true` (auto-set) - Non-interactive, optimized
- **Production**: Not used (Node.js doesn't run)

**What it controls:**
- Playwright: Non-interactive mode, no video recording
- npm: Disables interactive prompts
- Test frameworks: CI-optimized output

## Production Deployment

### Using Docker Compose
```bash
# Production - Node.js will NOT start
docker compose -f compose.yaml -f compose.prod.yaml up -d

# Verify
docker compose ps
# Should NOT show 'node' service
```

### Verification
```bash
make verify-prod
# Output:
# ✅ SUCCESS: Node.js container is NOT running (production mode)
# Running containers:
# - database
# - php
# - redis
```

## Troubleshooting

### Node.js Running in Production
```bash
# This should NEVER happen, but if it does:
docker compose --profile dev down
docker compose up -d  # Without --profile dev
```

### CI Job Failing - Missing Node.js
```bash
# Ensure CI job uses --profile dev:
docker compose --profile dev up --wait --no-build
```

### Local Dev - Node.js Not Running
```bash
# Use dev profile:
make up-dev
# Or:
docker compose --profile dev up -d
```

## Best Practices

1. **Always use `make` commands** - They handle profiles correctly
2. **Never use `--profile dev` in production** - Production is default
3. **CI jobs needing Node.js must use `--profile dev`** - Otherwise Node.js won't start
4. **Run `make verify-prod` before deploying** - Safety check
5. **Keep `CI` variable as-is** - It's automatically set by CI systems

## Architecture Decision

### Why Profiles Instead of Separate Compose Files?
- ✅ Single source of truth
- ✅ Explicit opt-in for Node.js (safer)
- ✅ Same file works for all environments
- ✅ Clear separation of concerns
- ✅ Impossible to accidentally run Node.js in production

### Why Node.js in CI?
- ✅ ESLint for TypeScript code quality
- ✅ Prettier for code formatting consistency
- ✅ TypeScript compiler for type safety
- ✅ Playwright for E2E testing
- ❌ Not needed for PHP-only tests (functional tests)
