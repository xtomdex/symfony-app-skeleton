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
- Doctrine
- Twig
- Messenger
- Any external framework

Domain contains only:

- Contracts (interfaces)
- Enums
- Domain Exceptions
- Pure DTOs (e.g. ApiResponse)
- Value objects

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

# 3. Testing Policy

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

# 4. Extension Principles

When adding new subsystems (JWT, RBAC, Versioning, OpenAPI):

- Do not break existing API contract
- Do not bypass exception mapper
- Add documentation section instead of rewriting fundamentals
- Add unit and functional tests
