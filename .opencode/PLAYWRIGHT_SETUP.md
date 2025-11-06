# Playwright Setup

This project uses the Playwright Docker image (`mcr.microsoft.com/playwright:v1.56.1-jammy`) for both MCP interactive browser automation and E2E test execution.

## Two Modes of Operation

### 1. MCP Playwright (Interactive Browser Automation)
- Used through OpenCode's Playwright MCP server
- Persistent browser context for interactive tasks
- Access via MCP tools in the IDE
- Configuration: `opencode.json` → `mcp.playwright`

### 2. E2E Tests (Automated Test Suite)
- Run via `make e2e` command
- Non-persistent, runs tests and exits
- Uses configuration from `playwright.config.ts`
- Test files located in `tests/e2e/`

## Setup

The setup requires `node_modules` at the project root because:
- `playwright.config.ts` imports from `@playwright/test`
- Test files import from `@playwright/test`
- These dependencies must be resolved

To install dependencies:
```bash
make e2e-install
```

The `make e2e` command automatically installs dependencies if `node_modules` doesn't exist.

## Configuration

Both modes use the same Docker Playwright image and share:
- Project mount: `.:/app:rw`
- Working directory: `/app`
- Base URL: `http://php:80` (defined in `playwright.config.ts`)

## Available Commands

```bash
make e2e-install      # Install Playwright dependencies (run once)
make e2e              # Run E2E tests
make e2e-debug        # Run tests in debug mode
make e2e-report       # Show HTML test report (http://localhost:9323)
make e2e-show-trace   # Show trace viewer for failed tests
```

## Notes

- MCP Playwright and `make e2e` cannot run simultaneously (browser profile conflict)
- The MCP server is for interactive browser automation during development
- E2E tests should be run separately when needed
- Both use the same underlying Playwright installation in the Docker image
