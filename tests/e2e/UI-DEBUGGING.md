# Visual Debugging with Playwright

Playwright provides excellent visual debugging tools when running in Docker.

## Best Approach: HTML Report + Trace Viewer ⭐

### Step 1: Run Your Tests

```bash
make e2e
```

Playwright automatically captures:
- Screenshots on failure
- Videos of test execution
- Trace files with DOM snapshots

### Step 2: View the Interactive Report

```bash
make e2e-report
```

This opens **http://localhost:9323** with an interactive report.

### What You'll See:

```
┌─────────────────────────────────────────────────────────────┐
│ Playwright HTML Report                                      │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ✓  should create post with media via UI         (15s)    │
│  ✓  should add multiple media items              (12s)    │
│  ✗  should remove media item                     (8s)     │  ← Click failed test
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Step 3: Click on a Test

When you click a test, you see:
- **Test steps** (each action)
- **Screenshots** at each step
- **Error details** (if failed)
- **"Open trace"** button ← Click this!

### Step 4: Trace Viewer (Time-Travel Debugger!)

The trace viewer shows:

```
┌─────────────────────────────────────────────────────────────┐
│ Actions Timeline          │  DOM Snapshot                   │
├───────────────────────────┼─────────────────────────────────┤
│ 1. page.goto()            │  [Live DOM at this moment]      │
│ 2. page.fill()            │                                 │
│ 3. page.click() ← ERROR   │  You can inspect elements!      │
│ 4. page.waitFor()         │  See what the page looked like  │
│                           │  at the exact moment of failure │
├───────────────────────────┴─────────────────────────────────┤
│ Console | Network | Source | Metadata                       │
└─────────────────────────────────────────────────────────────┘
```

**Features:**
- 🕐 **Time-travel**: Click any action to see DOM state at that moment
- 🔍 **Inspect**: See exactly what elements existed
- 📸 **Screenshots**: Visual state at each step
- 🌐 **Network**: See API calls and responses
- 📝 **Console**: View console.log output
- 🎯 **Locator**: Find exact selectors for elements

## Quick View Failed Tests Only

```bash
make e2e-show-trace
```

Opens trace viewer directly for failed tests.

## Why This Works Great in Docker

Unlike GUI applications that need X11 forwarding, the HTML report:
- ✅ Runs as a web server in Docker
- ✅ Accessible from your browser
- ✅ Full interactivity
- ✅ No display forwarding needed
- ✅ Works on any OS (Linux, Mac, Windows)

## Debugging Workflow

### 1. Test Fails Locally

```bash
make e2e
# ✗ Test failed: selector not found
```

### 2. Open Report

```bash
make e2e-report
# Opens http://localhost:9323
```

### 3. Click Failed Test → "Open Trace"

You see:
- Exact moment it failed
- What the page looked like
- Why the selector wasn't found

### 4. Fix Selector in Test

```typescript
// Before (wrong):
await page.click('.add-media-button');

// After (correct - found via trace viewer):
await page.click('[data-collection-holder="media"] button.add-item');
```

### 5. Run Again

```bash
make e2e
# ✓ Test passed!
```

## Advanced: Screenshots and Videos

### Automatic Capture

Playwright automatically saves (configured in `playwright.config.ts`):
- Screenshots on failure
- Videos on failure
- Traces on first retry

### View Artifacts

After running tests:

```
test-results/
├── post-crud-should-create-post-chromium/
│   ├── test-failed-1.png          ← Screenshot at failure
│   ├── video.webm                 ← Full video of test
│   └── trace.zip                  ← Trace file
```

### View Video

Videos are embedded in the HTML report - just click the test!

## Comparing to Native Playwright UI Mode

**Native UI Mode** (not practical in Docker):
- Requires X11 display forwarding
- Complex setup with Docker
- OS-dependent

**HTML Report + Trace Viewer** (works perfectly in Docker):
- No display forwarding needed
- Works everywhere
- Actually more powerful (saved traces)

## Configuration

See `playwright.config.ts` for capture settings:

```typescript
use: {
  screenshot: 'only-on-failure',  // When to capture
  video: 'retain-on-failure',      // Save videos
  trace: 'on-first-retry',         // Detailed traces
}
```

## Tips

1. **Always open the report** - Don't just read terminal output
2. **Use trace viewer** - It's like a debugger for UI tests
3. **Inspect DOM snapshots** - See exactly what elements existed
4. **Check network tab** - Verify API calls happened
5. **Look at console** - See JavaScript errors

## Common Scenarios

### "Selector not found"

1. Open trace viewer
2. Navigate to the failed step
3. Look at DOM snapshot
4. Find the actual selector
5. Update your test

### "Element not visible"

1. Open trace viewer
2. Check screenshot at that moment
3. Verify element actually appears
4. Add proper wait if needed:
   ```typescript
   await page.waitForSelector('.element', { state: 'visible' });
   ```

### "Unexpected behavior"

1. Watch the video in the report
2. See exactly what the browser did
3. Spot timing issues or unexpected clicks

## Summary

**Best debugging workflow:**

```bash
# 1. Run tests
make e2e

# 2. View report (if any failures)
make e2e-report

# 3. Click failed test → "Open trace"

# 4. Use trace viewer to:
#    - See exact DOM state
#    - Find correct selectors
#    - Understand what went wrong

# 5. Fix test and repeat
```

**The HTML report + trace viewer is MORE powerful than headed mode!**
