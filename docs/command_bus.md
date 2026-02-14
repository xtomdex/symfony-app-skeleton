# CommandBus Pattern

This skeleton uses a CommandBus-style controller pipeline.

---

## UseCase Structure

Each UseCase typically contains:

- Command (input DTO)
- Handler (invokable service)
- Optional Form (for validation)

---

## Controller Rules

Controllers:

- Must remain thin
- Must not contain business logic
- May inject contextual data into commands
- Must rely on exception mapper for error handling

---

## Exception Mapping

All exceptions pass through:

`App\Domain\Contract\CommandBusExceptionMapperInterface`

Default implementation:

`App\Infrastructure\CommandBus\CommandBusExceptionMapper`

New domain exceptions must:

- Be mapped explicitly
- Have corresponding unit tests
