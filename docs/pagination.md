# Pagination

The skeleton provides standardized contracts for list/pagination/sorting functionality. This is a Domain-level abstraction used by use cases and repositories to return paginated results in a uniform way.

Consumers of these contracts include AdminPanel components (DataTable, Pagination), but the Domain layer does NOT depend on AdminPanel. The controller bridges between them.

---

# 1. Overview

The pagination system consists of three parts:

- **`ListCriteria`** — input DTO built by controllers from request query parameters
- **`PaginatedResultInterface` / `PaginatedResult`** — output contract returned by repositories and handlers
- **`PaginatedQueryTrait`** — Doctrine helper that applies pagination and sorting to a QueryBuilder

All files are skeleton-owned.

---

# 2. ListCriteria

`App\Domain\Pagination\ListCriteria`

Pure readonly DTO. No validation, no dependencies, no logic.

| Field | Type | Default | Description |
|---|---|---|---|
| `page` | `int` | `1` | Requested page number |
| `pageSize` | `int` | `20` | Items per page |
| `sortField` | `?string` | `null` | Sort field name (query param name, not DQL) |
| `sortDirection` | `string` | `'asc'` | Sort direction: `'asc'` or `'desc'` |

Controllers build `ListCriteria` from request query parameters:

```php
$criteria = new ListCriteria(
    page: $request->query->getInt('page', 1),
    pageSize: $request->query->getInt('pageSize', 20),
    sortField: $request->query->get('sort'),
    sortDirection: $request->query->get('direction', 'asc'),
);
```

`ListCriteria` is included in use case commands alongside domain-specific filters. It is not validated in the DTO itself — input sanitization is handled by `PaginatedQueryTrait`.

---

# 3. PaginatedResultInterface / PaginatedResult

`App\Domain\Pagination\PaginatedResultInterface`
`App\Domain\Pagination\PaginatedResult`

Contract for paginated query results.

| Method | Return | Description |
|---|---|---|
| `items()` | `list<T>` | Page items |
| `currentPage()` | `int` | Current page number (after clamping) |
| `totalPages()` | `int` | Total number of pages |
| `pageSize()` | `int` | Items per page (after clamping) |
| `totalItems()` | `int` | Total count of matching items |

## map()

`PaginatedResult::map(callable $mapper): self`

Transforms items while preserving all pagination metadata. Used in handlers to convert entities to DTOs:

```php
return $this->repository->findPaginated($criteria)
    ->map(UserListItem::fromEntity(...));
```

The mapper receives each item and returns the transformed value. Pagination metadata (currentPage, totalPages, pageSize, totalItems) is copied unchanged.

---

# 4. PaginatedQueryTrait

`App\Infrastructure\Pagination\PaginatedQueryTrait`

Doctrine pagination and sorting helper for use in repositories.

## Usage

```php
use App\Infrastructure\Pagination\PaginatedQueryTrait;

final class UserRepository extends ServiceEntityRepository
{
    use PaginatedQueryTrait;

    public function findPaginated(ListCriteria $criteria): PaginatedResult
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.status = :status')
            ->setParameter('status', 'active');

        return $this->paginate($qb, $criteria, [
            'name'      => 'u.name',
            'email'     => 'u.email',
            'createdAt' => 'u.createdAt',
        ]);
    }
}
```

## paginate() signature

```php
protected function paginate(
    QueryBuilder $qb,
    ListCriteria $criteria,
    array $sortableFields = [],
): PaginatedResult
```

`$qb` must have all domain filters applied but no `orderBy`, `setFirstResult`, or `setMaxResults` set. The trait applies those.

## $sortableFields whitelist

`$sortableFields` maps query param names to DQL expressions:

```php
return $this->paginate($qb, $criteria, [
    'name'      => 'u.name',
    'email'     => 'u.email',
    'createdAt' => 'u.createdAt',
]);
```

If `criteria->sortField` is not a key in this map, sorting is silently skipped. This is the primary guard against arbitrary sort field injection. Repositories explicitly declare which fields are sortable.

If `$sortableFields` is empty (the default), sorting is never applied.

## Input sanitization

The trait enforces these constraints regardless of what the controller sends:

- `page` is clamped to `>= 1`
- `pageSize` is clamped to `1..100`
- `page` is clamped to `<= totalPages` (requesting page 999 of 5 returns page 5, not an empty result)

## Count query

The trait clones the QueryBuilder, resets `orderBy`, and executes a `COUNT()` query before applying pagination. This ensures the total count is accurate.

---

# 5. Controller Integration

Full example with AdminPanel DataTable and Pagination components.

**Command:**
```php
final readonly class ListUsersCommand
{
    public function __construct(
        public ListCriteria $criteria,
        // domain-specific filters:
        public ?string $search = null,
    ) {}
}
```

**Handler:**
```php
final class ListUsersHandler
{
    public function __construct(private UserRepository $repository) {}

    public function __invoke(ListUsersCommand $command): PaginatedResult
    {
        return $this->repository->findPaginated($command->criteria, $command->search)
            ->map(UserListItem::fromEntity(...));
    }
}
```

**Controller:**
```php
#[Route('/admin/users', name: 'admin_users_list')]
public function list(Request $request): Response
{
    $criteria = new ListCriteria(
        page: $request->query->getInt('page', 1),
        pageSize: $request->query->getInt('pageSize', 20),
        sortField: $request->query->get('sort'),
        sortDirection: $request->query->get('direction', 'asc'),
    );

    /** @var PaginatedResult<UserListItem> $result */
    $result = $this->commandBus->handle(new ListUsersCommand($criteria));

    return $this->render('admin/users/list.html.twig', [
        'table' => new DataTableDTO(
            columns: UserListItem::columns(),
            rows: array_map(fn(UserListItem $item) => $item->toRow(), $result->items()),
        ),
        'pagination' => new PaginationDTO(
            currentPage: $result->currentPage(),
            totalPages: $result->totalPages(),
            pageSize: $result->pageSize(),
            totalItems: $result->totalItems(),
        ),
    ]);
}
```

**Template:**
```twig
<twig:AdminPanel:DataTable :table="table" />
<twig:AdminPanel:Pagination :pagination="pagination" />
```

---

# 6. File Ownership

All files in this system are skeleton-owned:

| File | Owner |
|---|---|
| `src/Domain/Pagination/ListCriteria.php` | skeleton |
| `src/Domain/Pagination/PaginatedResultInterface.php` | skeleton |
| `src/Domain/Pagination/PaginatedResult.php` | skeleton |
| `src/Infrastructure/Pagination/PaginatedQueryTrait.php` | skeleton |
