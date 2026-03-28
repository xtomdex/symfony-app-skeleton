# Instruction: Create Use Cases

This instruction guides the creation of use cases (Command + Handler) for an existing entity, including domain events and unit tests.

Prerequisites: the entity, repository, DTOs, enums, and domain exceptions must already exist.

Before starting, the caller must provide:
- **Entity name** and **module**
- **List of use cases** to create (e.g. Create, Update, Delete, Publish, Get, List)
- **For each write use case:** which command fields are needed, which domain method to call
- **For read use cases:** lookup strategy (by id, by slug, by enum field, etc.)
- **Whether domain events are needed** (if the entity should dispatch events for project-level listeners)
- **Any business rules / guards** (e.g. "can only delete if archived")

---

## Step 1: Study References

Read these files to understand existing patterns. Do NOT deviate from them.

```
# Existing use cases — study directory structure, Command/Handler style
src/Modules/System/UseCase/Document/
src/Modules/User/UseCase/

# CommandBus exceptions — understand NotFoundException, ValidationException
src/Infrastructure/CommandBus/

# Eventing conventions
docs/eventing.md

# Existing domain events
src/Modules/System/Event/
src/Modules/User/Event/

# AggregateRoot / EventsTrait
# (find the trait/interface used by entities that record events)

# Entity being targeted
src/Modules/{Module}/Entity/{Entity}.php
src/Modules/{Module}/Entity/Base{Entity}.php  # if BaseEntity pattern

# Repository
src/Modules/{Module}/Repository/{Entity}Repository.php

# DTOs
src/Modules/{Module}/DTO/{Entity}View.php
src/Modules/{Module}/DTO/{Entity}ListItem.php

# Domain exceptions
src/Modules/{Module}/Exception/

# Pagination
docs/pagination.md
src/Domain/Pagination/ListCriteria.php

# Test conventions
docs/testing.md
tests/Unit/Modules/System/UseCase/Document/  # reference tests if available
```

Read what is available and follow the patterns found.

---

## Step 2: Create Domain Events (if needed)

Location: `src/Modules/{Module}/Event/`

Rules from `docs/eventing.md`:
- Named in **past tense** (e.g. `DocumentCreated`, `InvoicePaid`)
- `final class` implementing `DomainEventInterface`
- Minimal data: IDs and essential fields only — no entity objects, no infrastructure types
- Constructor with `public readonly` properties
- Check existing events for exact class structure (constructor style, interface import)

Create one event per state-changing operation:
- `{Entity}Created` — recorded in `create()` factory method
- `{Entity}Updated` — recorded in `updateContent()` or equivalent domain method
- `{Entity}Deleted` — recorded in a `delete()` domain method
- Additional events for domain-specific transitions (e.g. `Published`, `Archived`, `Cancelled`)

### Modify entity to record events

If the entity does not already use AggregateRoot/EventsTrait, add it (check BaseDocument or other aggregate roots for the exact pattern).

Add `$this->recordEvent(new {Event}(...))` at the end of each domain method.

For delete: add a `delete()` method that only records the event (no state change). Override `remove()` in the repository to call `$entity->delete()` before `parent::remove($entity)`.

---

## Step 3: Create Use Cases

### Directory Structure

For modules with a single primary entity (e.g. User module):
```
src/Modules/{Module}/UseCase/{Action}/{Action}{Entity}Command.php
src/Modules/{Module}/UseCase/{Action}/{Action}{Entity}Handler.php
```

For modules with multiple entities (e.g. System module):
```
src/Modules/{Module}/UseCase/{Entity}/{Action}/{Action}{Entity}Command.php
src/Modules/{Module}/UseCase/{Entity}/{Action}/{Action}{Entity}Handler.php
```

Sub-actions create additional nesting:
```
src/Modules/{Module}/UseCase/{Action}/{SubAction}/{Action}{SubAction}{Entity}Command.php
```

### Command Rules

- `final readonly class`
- Constructor with `public` promoted properties
- Write commands: `{Action}{Entity}Command` (e.g. `CreateDocumentCommand`)
- Create commands always include `string $id` as first parameter — ID is provided by the caller
- No validation logic inside the command

### Handler Rules

- `final class`
- Single `__invoke({Command} $command)` method
- Constructor injection: repository, EntityManager (check existing handlers for exact injection pattern)
- Domain exceptions are caught and converted to CommandBus exceptions — they NEVER leak out of the handler:
  - `{Entity}NotFoundException` → CommandBus `NotFoundException`
  - `{Entity}SlugAlreadyExistsException` → CommandBus `ValidationException`
  - Any state-conflict exception → CommandBus `ValidationException` (or `ConflictException` if available)

### Standard Use Case Patterns

**Create:**
1. Validate uniqueness constraints (e.g. slug) via repository → throw ValidationException if violated
2. Call `{Entity}::create($command->id, ...)` — ID from command, NEVER generated in handler
3. Persist and flush
4. Return `$command->id`

**Update:**
1. Load entity by ID → throw NotFoundException if null
2. Call domain method (e.g. `$entity->updateContent(...)`)
3. Flush (no persist needed — entity is already managed)

**Delete:**
1. Load entity by ID → throw NotFoundException if null
2. Apply guards if needed (check status, ownership, etc.) → throw ValidationException if violated
3. Call `$repository->remove($entity)` — if entity has events, the overridden remove() calls `$entity->delete()` to record the event
4. Flush

**Status transitions (Publish, Archive, Cancel, etc.):**
1. Load entity by ID → throw NotFoundException if null
2. Call domain method (e.g. `$entity->publish()`)
3. Flush

**Get (single entity):**
1. Determine lookup strategy from command (by id, by slug, by enum field)
2. Call appropriate repository method
3. If null → throw NotFoundException
4. Return `{Entity}View::fromEntity($entity)`

**List (paginated):**
1. Pass `ListCriteria` from command to `$repository->findPaginated()`
2. Map result: `->map({Entity}ListItem::fromEntity(...))`
3. Return `PaginatedResult`

---

## Step 4: Create Unit Tests

Location: `tests/Unit/Modules/{Module}/UseCase/{Entity}/` (or `tests/Unit/Modules/{Module}/UseCase/` for single-entity modules)

Mirror the use case directory structure.

### Handler Test Rules

- `createMock()` for repository and EntityManager when verifying calls with `expects()`
- `createStub()` when no expectations are needed
- PHPUnit 12 conventions: `createStub()` for dependencies without `expects()`, `createMock()` only when asserting method calls
- Check existing handler tests for exact injection and assertion patterns

### Test Cases Per Pattern

**Create handler:**
- `test_creates_{entity}_successfully` — uniqueness check passes, persist+flush called, returns id
- `test_throws_validation_when_{unique_field}_exists` — uniqueness violated → expects ValidationException

**Update handler:**
- `test_updates_{entity}` — entity found, flush called
- `test_throws_not_found` — entity not found → expects NotFoundException

**Delete handler:**
- `test_deletes_{entity}` — entity found, remove+flush called
- `test_throws_not_found` — expects NotFoundException
- Additional guard tests if business rules apply

**Status transition handlers:**
- `test_{action}s_{entity}` — entity found, domain method called, flush called
- `test_throws_not_found` — expects NotFoundException

**Get handler:**
- One test per lookup strategy: `test_returns_{entity}_view_by_{field}`
- One not-found test per lookup strategy: `test_throws_not_found_by_{field}`

**List handler:**
- `test_returns_paginated_result` — repository returns PaginatedResult, handler maps to ListItem DTO

### Event Recording Tests

Add to entity test class:
- `test_{domain_method}_records_{event_name}_event` — call domain method, verify recorded events contain expected event class with correct data
- Check existing entity tests for the method used to retrieve recorded events from the trait

---

## Checklist

Before finishing, verify:

- [ ] Use case directories follow the project's nesting convention
- [ ] Commands are `final readonly class`, handlers are `final class` with `__invoke`
- [ ] Create commands accept `string $id` as first parameter
- [ ] No ID generation inside handlers
- [ ] Domain exceptions are caught inside handlers and converted to CommandBus exceptions
- [ ] Domain exceptions never leak out of handlers
- [ ] Entity domain methods call `recordEvent()` (if events are used)
- [ ] Delete repository override calls `$entity->delete()` before `parent::remove()` (if events are used)
- [ ] All handler tests cover success + not-found + validation scenarios
- [ ] Event recording tests verify correct event class and data
- [ ] No controllers, routes, templates, migrations, or services config created
- [ ] Existing entity, repository, DTO, and enum files are NOT modified (unless adding EventsTrait or recordEvent calls)
