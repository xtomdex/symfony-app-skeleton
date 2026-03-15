# Architecture

This repository is a Symfony-based application skeleton with a strict, testable HTTP pipeline built around a CommandBus-style pattern.

The architecture prioritizes:

- Stability and predictability
- Strict API contract
- Domain purity
- Centralized error handling
- Testable infrastructure
- AI-friendly development rules

This document describes architectural rules, not implementation details.

---

# 1. Core Principles

## 1.1 Domain Purity

The `src/Domain` layer MUST NOT depend on:

- Symfony
- Twig
- Messenger
- Any external framework

Domain contains only:

- Contracts (interfaces)
- Enums
- Domain Exceptions
- Pure DTOs (e.g. ApiResponse)
- Value objects

Domain CAN depend on `Doctrine`. It is an only exception.

Domain must remain framework-agnostic.

---

## 1.2 UseCase-Centric Design

Business logic belongs in UseCases (Command + Handler).

Controllers are orchestration layers only.

Allowed exception:
- Controllers may inject contextual data into commands (e.g. current user ID, locale).
- Controllers must NOT contain business rules.

UseCases remain the primary source of business behavior.

---

## 1.3 Strict API Envelope

All API endpoints MUST return the unified envelope represented by:

`App\Domain\DTO\ApiResponse`

This rule applies to:

- Success responses
- Validation failures
- Authentication errors
- Authorization errors
- Not found
- Internal errors

No endpoint may return raw JSON outside this envelope.

---

## 1.4 Centralized Exception Mapping

All exceptions must be converted using:

`App\Domain\Contract\CommandBusExceptionMapperInterface`

Default implementation:

`App\Infrastructure\CommandBus\CommandBusExceptionMapper`

Controllers do not decide HTTP status codes or error codes directly.

All mapping rules are centralized.

---

## 1.5 Input Normalization

JSON request bodies are converted into request parameters using:

`ConvertJsonRequestSubscriber`

Webhook endpoints may disable JSON parsing via route default:

    defaults: { skip_json_body_parsing: true }

---

# 2. API Contract

All API responses use:

`App\Domain\DTO\ApiResponse`

Structure:

    {
      "ok": bool,
      "result": mixed|null,
      "error_code": string|null,
      "error_message": string|null,
      "status_code": int,
      "errors": null|array<string,string>
    }

Error codes come from:

`App\Domain\Enum\ResponseErrorCode`

Minimum required error codes:

- validation_failed
- unauthorized
- forbidden
- not_found
- internal_error

---

# 3. Layer Dependencies

## 3.1 Allowed Dependency Direction

    Domain ← Infrastructure
    Domain ← Modules
    Modules ← Infrastructure

Infrastructure depends on Modules and Domain.

Modules depend on Domain.

Domain depends on nothing.

Modules MUST NOT depend on Infrastructure.

## 3.2 Module Dependencies

Modules may depend on other modules.

Dependency must be unidirectional (no circular references).

Cross-module communication for decoupled scenarios uses Common Events (see eventing.md).

---

# 4. Entity Design

## 4.1 Closed Constructors

Entity constructors MUST be `protected`.

Entity instances are created through semantic static factory methods.

    User::create(...)
    User::signUp(...)
    User::invite(...)

## 4.2 Skeleton Extensibility (BaseEntity Pattern)

When an entity is provided by the skeleton and expected to be extended by projects:

- Skeleton defines a `MappedSuperclass` base class with stable fields (e.g. `BaseUser`)
- Project defines the concrete `Entity` class extending the base (e.g. `User`)
- Base class lives in the same module as the concrete class
- Factory method in base class uses `new static()` and returns `static`

Skeleton owns the base class. Project owns the concrete class.

After initial creation, the skeleton MUST NOT modify the concrete class.

This eliminates merge conflicts when pulling skeleton updates into projects.

---

# 5. Testing Policy

Unit tests must cover:

- ApiResponse DTO
- CommandBusExceptionMapper
- ConvertJsonRequestSubscriber

Functional tests must cover:

- API success
- API validation failure
- API internal error
- Twig render-only
- Twig invalid form (handler not called)
- Twig valid form (handler called)

Test-only artifacts must not load in dev/prod environments.

---

# 6. Extension Principles

When adding new subsystems (JWT, RBAC, Versioning, OpenAPI):

- Do not break existing API contract
- Do not bypass exception mapper
- Add documentation section instead of rewriting fundamentals
- Add unit and functional tests
