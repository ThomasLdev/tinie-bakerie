# .opencode Directory Structure

```
.opencode/
├── README.md                       # Quick start guide for AI agents
├── MAIN-INSTRUCTIONS.md            # Core instructions (all agents read this first)
├── STRUCTURE.md                    # This file - directory overview
│
├── agents/                         # Specialized agent configurations
│   ├── feature-developer.md        # 🔨 New features with TDD workflow
│   ├── bug-fixer.md               # 🐛 Systematic debugging and fixing
│   ├── refactoring-expert.md      # ♻️  Safe code quality improvements
│   ├── testing-expert.md          # ✅ Test writing and TDD
│   └── code-reviewer.md           # 👀 Code review and quality checks
│
└── docs/                          # Documentation organized by theme
    └── testing/                   # All testing-related documentation
        ├── complete-guide.md      # Full testing strategy and patterns
        ├── decision-guide.md      # Quick: which test type to use?
        └── e2e-setup.md          # Playwright E2E testing setup
```

## File Purposes

### Root Files

- **README.md** - Entry point, explains structure, quick navigation
- **MAIN-INSTRUCTIONS.md** - Core instructions all agents must read
- **STRUCTURE.md** - This file, visual overview

### Specialized Agents

Each agent provides:
- When to use it
- Specific workflow for that task type
- Common patterns and anti-patterns
- Task-specific checklist

**Load the appropriate agent** based on your task type for optimized workflows.

### Testing Documentation

Comprehensive testing guides:
- **complete-guide.md** - Philosophy, TDD workflow, patterns, examples
- **decision-guide.md** - Quick matrix for choosing test types
- **e2e-setup.md** - Setting up Playwright E2E tests

## Usage Flow

```
1. AI Agent starts conversation
   ↓
2. Read: .opencode/README.md (quick navigation)
   ↓
3. Read: .opencode/MAIN-INSTRUCTIONS.md (core principles)
   ↓
4. Identify task type → Load specialized agent:
   - Feature? → agents/feature-developer.md
   - Bug? → agents/bug-fixer.md  
   - Refactor? → agents/refactoring-expert.md
   - Tests? → agents/testing-expert.md
   - Review? → agents/code-reviewer.md
   ↓
5. Reference testing docs as needed
   ↓
6. Explore codebase with Serena for technical details
```

## Maintenance

### Adding New Documentation

```
New agent type? → Add to agents/
New workflow? → Add to agents/ or docs/ as appropriate
Testing patterns? → Update docs/testing/
```

### Updating Existing Docs

```
Better workflow discovered? → Update specialized agent
Testing strategy evolved? → Update docs/testing/
New conventions? → Update MAIN-INSTRUCTIONS.md
```

## Philosophy

**Code is self-documenting** - Agents explore the codebase to understand "what"

**Docs focus on "how"** - Workflows, decision-making, best practices

**Specialized agents** - Optimized workflows for specific task types

**Keep it DRY** - Link to detailed docs, don't duplicate

---

**Last Updated:** $(date +%Y-%m-%d)
