### Project Context

This project is a web application built in Symfony as a fullstack framework, 
using turbo and stimulus to dynamize the frontend.

Its purpose is to manage a collection of posts and act as a light CMS. It should manage the posts media, 
blocks of content, metadata, categories and tags, as well as any translation associated with these entities.

It also has a back office built with EasyAdmin to manage all these entities.

### Development Instructions

I am especially interested in best practices, performance, security, and maintainability.
When generating code, always follow Symfony best practices and coding standards.
When generating code, ensure it is compatible with the latest stable versions of the libraries.
Always be aware of PHP best practices and PSR standards.
When generating code, ensure it is well-structured, modular, and adheres to SOLID principles.
When generating code, include appropriate error handling and input validation.
When generating tests, ensure they cover edge cases and potential failure points.

### Using MCPs

## Use Sequential Thinking for Complex Tasks

**ALWAYS** use the `sequentialthinking_sequentialthinking` tool when:
- Analyzing complex problems or architectural decisions
- Planning multi-step implementations (>3 steps)
- Debugging issues that require multiple hypothesis testing
- Making decisions that affect multiple parts of the system
- Evaluating trade-offs between different approaches
- Designing new features or refactoring existing code

The sequential thinking process should:
1. Break down the problem into logical steps
2. Consider edge cases and potential issues
3. Evaluate different solutions before implementing
4. Verify assumptions against the codebase
5. Adjust the plan as new information is discovered

## Use Memory MCP to Store and Retrieve Project Knowledge

**Proactively** use memory tools to:
- Store important architectural decisions and patterns discovered in the codebase
- Save common debugging solutions and error patterns
- Document custom conventions not in standard files
- Keep track of complex relationships between entities
- Record performance optimization decisions
- Store frequently used patterns and their locations

**Read memories** at the start of conversations to:
- Understand previous decisions and context
- Avoid repeating work or analysis
- Maintain consistency with established patterns

**Update memories** after:
- Discovering new architectural patterns
- Solving complex bugs
- Making significant refactoring decisions
- Learning about non-obvious entity relationships

Memory naming conventions:
- `architecture-{component}` for architectural decisions
- `patterns-{type}` for code patterns
- `debugging-{area}` for common issues and solutions
- `entities-relationships` for complex entity mappings

## Use Context7 MCP for Library Documentation

Use context7 MCP when I need code generation, setup or configuration steps, or
library/API documentation. This means you should automatically use the Context7 MCP
tools to resolve library id and get library docs without me having to explicitly ask.

When I ask for code generation, setup or configuration steps, or library/API
documentation, you should always use context7 to get the most accurate and up-to-date
information.

This project uses the following libraries:
- Symfony
- Doctrine
- Twig
- PHPUnit
- Hotwired Turbo
- Hotwired Stimulus
- EasyAdmin
- Zenstruck Foundry

Verify the composer.json for library versions.

## Use Serena MCP for PHP Code Analysis

When generating or analysing code, always Activate the project ~/PhpstormProjects/tinie-bakerie

Use Serena tools to:
- Find symbols and their references across the codebase
- Understand class hierarchies and dependencies
- Locate where specific patterns are used
- Analyze code structure before making changes

### Verify code quality

The project use the following quality tools:
- PHPStan
- Rector
- PHP CS Fixer

After generating code in its final form, always run these tools to match the project code standards.
This project use makefile to run the commands, you can run the "make quality" command to run them all.


