# Testing

Infrastructure is treated as stable product code.

---

## Unit Tests

Must exist for:

- App\Domain\DTO\ApiResponse
- App\Infrastructure\CommandBus\CommandBusExceptionMapper
- ConvertJsonRequestSubscriber
- App\Modules\User\Entity\User (factory method, field defaults, domain events)
- App\Infrastructure\Security\UserIdentity (mapping from User, role defaults)
- App\Infrastructure\Security\Provider\ByUsernameProvider (load, not found, refresh, supports)

---

## Functional Tests

Must verify:

- API success envelope
- API validation failure
- API internal error mapping
- Twig render-only behavior
- Twig invalid form (handler not executed)
- Twig valid form (handler executed)

---

## Test Isolation

Test-only:

- Controllers
- Routes
- Services
- Templates

Must exist only in test environment.

---

## Test Conventions

### Stubs vs Mocks

Use `createStub()` when the dependency is just needed to satisfy a type hint.

Use `createMock()` only when verifying that a method was called (`expects()`).

PHPUnit 12+ emits notices when mocks have no expectations configured.
