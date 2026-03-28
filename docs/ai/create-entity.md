# Instruction: Create Entity

This instruction guides the creation of a new Doctrine entity with supporting infrastructure (enums, DTOs, repository, domain exceptions, tests).

Before starting, the caller must provide:
- **Entity name** (e.g. `Document`, `Invoice`)
- **Module** where the entity lives (e.g. `System`, `Billing`)
- **Fields** with types and constraints
- **Enums** needed (if any)
- **Domain methods** beyond `create()` (e.g. `publish()`, `cancel()`)
- **Whether BaseEntity pattern is needed** (MappedSuperclass for skeleton extensibility)
- **Whether read DTOs are needed** (EntityView, EntityListItem)
- **Whether domain exceptions are needed** (and which ones)
- **Whether pagination support is needed** (sortable fields list)

---

## Step 1: Study References

Read these files to understand existing patterns. Do NOT deviate from them.

```
# Entity patterns (MappedSuperclass + concrete)
src/Modules/User/Entity/BaseUser.php
src/Modules/User/Entity/User.php
src/Modules/System/Entity/BaseDocument.php
src/Modules/System/Entity/Document.php

# Enum style
src/Modules/System/Enum/DocumentType.php
src/Modules/System/Enum/DocumentStatus.php
src/Modules/User/Enum/UserStatus.php

# Repository style
src/Modules/System/Repository/DocumentRepository.php
src/Domain/Persistence/BaseRepository.php

# DTO style
src/Modules/System/DTO/DocumentView.php
src/Modules/System/DTO/DocumentListItem.php

# Domain exception style
src/Modules/System/Exception/DocumentNotFoundException.php

# Pagination infrastructure
src/Domain/Pagination/ListCriteria.php
src/Infrastructure/Pagination/PaginatedQueryTrait.php

# Test style
tests/Unit/Modules/System/Entity/BaseDocumentTest.php
tests/Unit/Modules/User/Entity/UserTest.php
```

Not all references may exist yet. Read what is available and follow the patterns found.

---

## Step 2: Create Enums (if needed)

Location: `src/Modules/{Module}/Enum/{EnumName}.php`

Rules:
- Backed string enums (`enum X: string`)
- Case names: PascalCase (e.g. `case Legal`)
- Case values: snake_case (e.g. `'legal'`)
- Follow exact style of existing enums in the project

---

## Step 3: Create Entity

### 3a: With BaseEntity Pattern (skeleton extensibility)

Use when the entity is provided by the skeleton and projects may extend it.

**Base class:** `src/Modules/{Module}/Entity/Base{Entity}.php`

- `#[ORM\MappedSuperclass]`
- Add `#[ORM\HasLifecycleCallbacks]` if using Timestampable trait (check BaseUser)
- `use TimestampableTrait;` — check exact import path from BaseUser
- Protected constructor, no arguments
- All properties are `private` with public getters, no setters
- ID property: `string` type, passed from outside — check BaseUser for exact Doctrine mapping

**Factory method `create()`:**
- First parameter is always `string $id` — ID is provided by the caller, NEVER generated inside the entity
- Returns `static` (not `self`)
- Uses `new static()` (not `new self()`) — critical for LSP when projects extend
- Sets sensible defaults for status-like fields (e.g. always draft on creation)

**Domain methods** (e.g. `publish()`, `archive()`, `updateContent()`):
- Modify internal state
- If entity uses AggregateRoot/EventsTrait — call `$this->recordEvent(...)` at the end of each method

**Concrete class:** `src/Modules/{Module}/Entity/{Entity}.php`

- `#[ORM\Entity]` — no `repositoryClass` parameter
- `#[ORM\Table(name: '{table_name}')]`
- Extends `Base{Entity}`
- Add indexes if it is needed
- Empty body — project extension point
- Skeleton MUST NOT modify this file after initial creation

### 3b: Without BaseEntity Pattern

Use when the entity won't be extended (e.g. EventLog, immutable records).

Single `final class` with:
- `#[ORM\Entity]`
- `#[ORM\Table(name: '{table_name}')]`
- Private constructor with all parameters
- Static `create()` factory method returning `self`
- Same ID rule: string, passed from outside

---

## Step 4: Create Repository

Location: `src/Modules/{Module}/Repository/{Entity}Repository.php`

Rules:
- Extends `App\Domain\Persistence\BaseRepository`
- `final class`
- Generic PHPDoc: `@extends BaseRepository<{Entity}>`
- Constructor passes entity class to parent

If pagination is needed:
- `use PaginatedQueryTrait;`
- Add `findPaginated(ListCriteria $criteria): PaginatedResult` method
- Define `$sortableFields` whitelist mapping query param names to DQL expressions
- No filters in skeleton scope — just pagination and sorting

Additional finder methods as needed:
- `findBySlug(string $slug): ?{Entity}` — if entity has slug
- `existsBySlug(string $slug): bool` — if slug uniqueness validation is needed
- `findById()` — only if BaseRepository's `get()` is insufficient (e.g. for nullable return type)

---

## Step 5: Create Read DTOs (if needed)

Location: `src/Modules/{Module}/DTO/`

**{Entity}View** — full representation:
- `final readonly class`
- All entity fields as constructor-promoted public properties
- Static `fromEntity({Entity} $entity): self` factory method

**{Entity}ListItem** — compact representation for lists:
- `final readonly class`
- Subset of fields (exclude heavy fields like `content`, `payload`)
- Static `fromEntity({Entity} $entity): self` factory method

---

## Step 6: Create Domain Exceptions (if needed)

Location: `src/Modules/{Module}/Exception/`

Rules:
- `final class` extending the project's base domain exception (check existing exceptions for the base class)
- Static factory methods with descriptive names: `byId()`, `bySlug()`, `forSlug()`, `forDocument()`
- Exceptions contain the relevant identifier in the message
- These are internal domain exceptions — they are NOT registered in CommandBusExceptionMapper
- Use case handlers catch them and convert to CommandBus exceptions (NotFoundException, ValidationException)

---

## Step 7: Create Unit Tests

Location: `tests/Unit/Modules/{Module}/`

Follow PHPUnit 12 conventions. Check existing tests for exact style.

### Entity tests (`Entity/Base{Entity}Test.php` or `Entity/{Entity}Test.php`)
- Test `create()` factory: all fields set correctly, defaults applied, ID matches passed value
- Test each domain method: state changes, timestamp updates
- Test event recording if entity uses AggregateRoot/EventsTrait
- Use the concrete class (e.g. `Document::create(...)`) to test the `new static()` chain

### Enum tests (`Enum/{EnumName}Test.php`)
- All cases exist with correct backed values

### DTO tests (`DTO/{Entity}ViewTest.php`, `DTO/{Entity}ListItemTest.php`)
- `fromEntity()` maps all fields correctly

### Exception tests (`Exception/{ExceptionName}Test.php`)
- Each static factory produces a message containing the relevant identifier

---

## Checklist

Before finishing, verify:

- [ ] Entity constructor is protected (BaseEntity) or private (standalone)
- [ ] Factory method uses `new static()` (BaseEntity) or `new self()` (standalone final)
- [ ] Factory method accepts `string $id` as first parameter
- [ ] No ID generation inside entity or repository
- [ ] `#[ORM\Entity]` has no `repositoryClass` parameter
- [ ] Repository extends `BaseRepository`, not `ServiceEntityRepository`
- [ ] Enums use PascalCase names, snake_case values
- [ ] Domain exceptions are NOT mapped in CommandBusExceptionMapper
- [ ] All tests pass
- [ ] No controllers, routes, templates, migrations, or services config created
