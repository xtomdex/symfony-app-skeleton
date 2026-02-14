# Testing

Infrastructure is treated as stable product code.

---

## Unit Tests

Must exist for:

- App\Domain\DTO\ApiResponse
- App\Infrastructure\CommandBus\CommandBusExceptionMapper
- ConvertJsonRequestSubscriber

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
