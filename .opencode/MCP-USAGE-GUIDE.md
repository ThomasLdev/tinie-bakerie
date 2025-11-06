# MCP Tools Usage Guide

> **Purpose:** Guide for AI agents on which MCP tools to use for different tasks

## Overview

Each specialized agent has specific MCP tools they MUST, SHOULD, or MAY use. This ensures efficiency and consistency.

## MCP Tools Available

### 1. Serena MCP (Code Intelligence)

**Purpose:** Explore, understand, and modify code with semantic awareness

**Key Operations:**
- `find_symbol` - Locate classes, methods, properties by name
- `find_referencing_symbols` - See where code is used (impact analysis)
- `search_for_pattern` - Regex search across files
- `get_symbols_overview` - File structure overview
- `replace_symbol_body` - Safe code replacement
- `rename_symbol` - Refactor names across codebase
- `insert_after_symbol` / `insert_before_symbol` - Add new code
- `list_dir` - Directory exploration
- Memory operations: `write_memory`, `read_memory`, `list_memories`

**When to use:**
- ✅ Understanding existing code structure
- ✅ Finding where code is used
- ✅ Safe refactoring operations
- ✅ Adding new methods/classes
- ✅ Storing/retrieving project knowledge

**When NOT to use:**
- ❌ Simple file reading (use Read instead)
- ❌ Running commands (use Bash instead)

### 2. Sequential Thinking

**Purpose:** Structured problem-solving and decision-making

**When to use:**
- ✅ Planning complex features (>3 steps)
- ✅ Evaluating multiple approaches
- ✅ Root cause analysis for bugs
- ✅ Refactoring strategy planning
- ✅ Architectural decisions

**When NOT to use:**
- ❌ Simple, straightforward tasks
- ❌ Well-defined single-step operations

### 3. Memory MCP

**Purpose:** Store and retrieve project-specific knowledge

**Operations:**
- `memory_create_entities` - Store new knowledge
- `memory_add_observations` - Update existing knowledge
- `memory_search_nodes` - Find relevant knowledge
- `memory_read_graph` - List all stored knowledge

**When to use:**
- ✅ First conversation of the day (check for context)
- ✅ After solving complex problems (store solution)
- ✅ Before implementing similar features (check patterns)
- ✅ After discovering anti-patterns (document them)

**When NOT to use:**
- ❌ For temporary information
- ❌ For framework/library documentation (use Context7)

### 4. Context7 MCP

**Purpose:** Access up-to-date library/framework documentation

**Operations:**
- `resolve_library_id` - Find library ID
- `get_library_docs` - Fetch documentation

**When to use:**
- ✅ Unfamiliar Symfony components
- ✅ New bundles/libraries
- ✅ API reference needed
- ✅ Version-specific features

**When NOT to use:**
- ❌ Project-specific code (use Serena)
- ❌ General PHP knowledge
- ❌ Stored patterns (use Memory)

### 5. TodoWrite/TodoRead

**Purpose:** Task planning and progress tracking

**When to use:**
- ✅ Complex tasks with multiple steps
- ✅ TDD cycles (RED → GREEN → REFACTOR)
- ✅ Multi-step debugging
- ✅ Long-running refactoring

**When NOT to use:**
- ❌ Single-step tasks
- ❌ Trivial operations

### 6. Bash

**Purpose:** Execute system commands

**When to use:**
- ✅ Running tests (`make test`)
- ✅ Quality checks (`make quality`)
- ✅ Docker commands (`docker compose exec php ...`)
- ✅ Viewing logs
- ✅ Database operations

**When NOT to use:**
- ❌ File reading (use Read instead)
- ❌ Code searching (use Serena or Grep)
- ❌ Communication with user (output text directly)

### 7. Read/Write/Edit

**Purpose:** Direct file operations

**When to use:**
- ✅ Reading configuration files
- ✅ Quick file edits
- ✅ Creating new files (when needed)

**When NOT to use:**
- ❌ Complex refactoring (use Serena)
- ❌ Symbol-aware operations (use Serena)

### 8. Grep/Glob

**Purpose:** Fast file searching

**When to use:**
- ✅ Finding files by pattern
- ✅ Content search when Serena not needed
- ✅ Quick lookups

**When NOT to use:**
- ❌ Symbol-based searching (use Serena)
- ❌ Complex code exploration (use Serena)

## Agent-Specific MCP Usage

### 🔨 Feature Developer

**MUST:**
- Serena (code exploration/modification)
- TodoWrite (planning)
- Sequential Thinking (feature design)

**SHOULD:**
- Memory (check patterns)
- Context7 (API docs)

**Workflow:**
```
1. Sequential Thinking → Plan feature
2. Memory → Check for similar patterns
3. TodoWrite → Create task list
4. Serena → Explore affected code
5. Context7 → Check Symfony APIs (if needed)
6. Serena → Implement changes
7. Bash → Run tests/quality
8. Memory → Store learnings
```

### 🐛 Bug Fixer

**MUST:**
- Serena (find code, trace refs)
- Sequential Thinking (root cause)
- TodoWrite (investigation steps)

**SHOULD:**
- Bash (logs, debug commands)
- Memory (known issues)

**Workflow:**
```
1. Sequential Thinking → Analyze problem
2. TodoWrite → Plan investigation
3. Serena → Find affected code
4. Bash → View logs, run repro
5. Memory → Check similar bugs
6. Serena → Implement fix
7. Bash → Run tests
8. Memory → Document solution
```

### ♻️ Refactoring Expert

**MUST:**
- Serena (impact analysis, safe refactoring)
- Sequential Thinking (evaluate options)
- Bash (test after EACH change)

**SHOULD:**
- Memory (store patterns)
- TodoWrite (incremental steps)

**Workflow:**
```
1. Bash → Run tests (must be green)
2. Sequential Thinking → Plan approach
3. TodoWrite → Break into small steps
4. Serena → Find all references
5. Serena → Make ONE change
6. Bash → Run tests
7. Repeat steps 5-6 for each change
8. Memory → Store successful pattern
```

### ✅ Testing Expert

**MUST:**
- Serena (code understanding)
- TodoWrite (TDD cycle tracking)
- Bash (run tests continuously)

**SHOULD:**
- Read (existing test patterns)
- Sequential Thinking (test strategy)

**Workflow:**
```
1. TodoWrite → Track: RED → GREEN → REFACTOR
2. Serena → Find code to test
3. Serena → Add test method (RED)
4. Bash → Run test (should fail)
5. Serena → Implement minimal code (GREEN)
6. Bash → Run test (should pass)
7. Serena → Refactor (REFACTOR)
8. Bash → Run test (still passes)
```

### 👀 Code Reviewer

**MUST:**
- Serena (code analysis, impact)
- Bash (automated checks)
- Sequential Thinking (evaluate decisions)

**SHOULD:**
- Read (config/validation files)
- Memory (anti-patterns)

**Workflow:**
```
1. Bash → make quality && make test
2. Serena → find_symbol (changed code)
3. Serena → find_referencing_symbols (impact)
4. Sequential Thinking → Evaluate approach
5. Read → Check config/validation
6. Memory → Check known anti-patterns
7. Provide structured feedback
```

## Best Practices

### Do's ✅

- **Activate Serena first** for any code work
- **Use TodoWrite** for multi-step tasks
- **Check Memory** at conversation start
- **Run tests frequently** with Bash
- **Use Sequential Thinking** for complex decisions
- **Store learnings** in Memory after solving hard problems

### Don'ts ❌

- **Don't use Bash** for file reading (use Read)
- **Don't use Bash** for code search (use Serena/Grep)
- **Don't skip Sequential Thinking** for complex tasks
- **Don't forget TodoWrite** for tracking progress
- **Don't neglect Memory** - it speeds up future work

## Efficiency Tips

1. **Batch Serena operations** when exploring multiple symbols
2. **Use TodoWrite early** to maintain context
3. **Check Memory first** - avoid re-solving problems
4. **Run quality checks** before detailed review
5. **Use appropriate tool** - don't force one tool for everything

## Example: Complete Feature Implementation

```
1. [Memory] Check for similar features
2. [Sequential Thinking] Plan feature architecture
3. [TodoWrite] Create task breakdown
4. [Serena] Explore affected entities/services
5. [Context7] Check Symfony Form API (if needed)
6. [TodoWrite] Mark "Planning" complete
7. [Serena] Add test method (TDD - RED)
8. [Bash] Run test → Fails ✓
9. [TodoWrite] Mark "RED" complete
10. [Serena] Implement minimal code (TDD - GREEN)
11. [Bash] Run test → Passes ✓
12. [TodoWrite] Mark "GREEN" complete
13. [Serena] Refactor code (TDD - REFACTOR)
14. [Bash] Run tests → Still passes ✓
15. [Bash] make quality → No issues ✓
16. [TodoWrite] Mark "REFACTOR" complete
17. [Memory] Store pattern if reusable
18. [TodoWrite] Mark feature complete
```

## Summary by Tool Priority

**High Priority (Use Often):**
- Serena MCP
- TodoWrite/TodoRead
- Bash
- Sequential Thinking

**Medium Priority (Use When Needed):**
- Memory MCP
- Read/Write/Edit
- Context7 MCP

**Low Priority (Specific Cases):**
- Grep/Glob
- WebFetch

---

**Remember:** The right tool makes the job easier. Don't force one tool when another is better suited.
