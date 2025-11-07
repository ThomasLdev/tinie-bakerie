# .opencode - AI Agent Documentation

Organized AI agent instructions and specialized agent configurations for Tinie Bakerie.

## 📁 Structure

```
.opencode/
├── README.md                    # This file - quick navigation
├── MAIN-INSTRUCTIONS.md         # Core instructions for all agents
├── MCP-USAGE-GUIDE.md          # Which MCP tools to use when (IMPORTANT!)
├── STRUCTURE.md                # Visual directory overview
├── agent/                       # Specialized agents for specific tasks
│   ├── feature-developer.md     # Implementing new features (TDD workflow)
│   ├── bug-fixer.md            # Debugging and fixing bugs
│   ├── refactoring-expert.md   # Code quality improvements
│   ├── testing-expert.md       # Test writing and TDD
│   └── code-reviewer.md        # Code review
└── docs/
    └── testing/                 # Testing documentation
        ├── complete-guide.md    # Full testing strategy
        └── decision-guide.md    # When to use which test type
```

## 🤖 Specialized Agents

### When to Use Each Agent

**🔨 Feature Developer** (`agents/feature-developer.md`)
- Implementing new features
- Adding new functionality
- Building complete user stories
- **Workflow:** Planning → TDD → Integration → Validation

**🐛 Bug Fixer** (`agents/bug-fixer.md`)
- Debugging issues
- Fixing bugs
- Investigating errors
- **Workflow:** Investigation → Reproduction → Debug → Fix → Validate

**♻️  Refactoring Expert** (`agents/refactoring-expert.md`)
- Improving code quality
- Performance optimization
- Design improvements
- **Workflow:** Assessment → Small changes → Test after each → Validate

**✅ Testing Expert** (`agents/testing-expert.md`)
- Writing tests
- Implementing TDD
- Improving coverage
- **Workflow:** RED → GREEN → REFACTOR

**👀 Code Reviewer** (`agents/code-reviewer.md`)
- Reviewing code
- Checking security
- Enforcing best practices
- **Workflow:** Automated checks → Detailed review → Constructive feedback

## 🚀 Quick Start for AI Agents

### First Time
```
1. Read MAIN-INSTRUCTIONS.md (core principles)
2. Read MCP-USAGE-GUIDE.md (which tools to use)
3. Load appropriate specialized agent for your task
4. Explore code with Serena as needed
```

### For Specific Tasks
```
Feature request? → Load: agent/feature-developer.md
Bug report? → Load: agent/bug-fixer.md
Improve code? → Load: agent/refactoring-expert.md
Write tests? → Load: agent/testing-expert.md
Review code? → Load: agent/code-reviewer.md
```

### MCP Tools Efficiency

**Each specialized agent specifies:**
- **MUST use** - Critical tools for that task type
- **SHOULD use** - Recommended tools for efficiency
- **MAY use** - Optional tools for specific cases

See **[MCP-USAGE-GUIDE.md](MCP-USAGE-GUIDE.md)** for complete tool reference.

## 📚 Testing Documentation

All testing docs in `docs/testing/`:
- **complete-guide.md** - Full TDD strategy, patterns, examples (includes FormType testing & Playwright)
- **decision-guide.md** - Quick: which test type to use?

## 🎯 Philosophy

**Code is self-documenting** - Agents explore the codebase to understand technical details.

**Docs focus on workflows** - How to approach tasks, not what the code does.

**Specialized agents** - Different workflows for different task types.

**Stay current** - Update when discovering new patterns.

---

**Project:** Tinie Bakerie - Lightweight CMS
**Stack:** Symfony + Doctrine + Twig + EasyAdmin + Turbo/Stimulus
**Testing:** PHPUnit + Foundry + Playwright
