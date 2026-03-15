# Security

This skeleton provides authentication and authorization infrastructure.

Projects configure which mechanisms to enable via `security.yaml`.

The skeleton does not include UI (controllers, templates, routes) for authentication. Each project implements its own login pages and flows.

---

# 1. Structure

## 1.1 User Module

    src/Modules/User/

Contains the User entity, repository, enums, and user-related use cases.

- `Entity/BaseUser` — MappedSuperclass with auth fields (id, username, password, roles, type, status, timestamps)
- `Entity/User` — concrete entity, extends BaseUser, project extension point
- `Enum/UserStatus` — pending, active, inactive, blocked
- `Enum/UserType` — client, admin, root
- `Enum/UserRole` — ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_USER

## 1.2 Auth Module (planned)

    src/Modules/Auth/

Contains authentication mechanisms that go beyond standard form login.

- OTP entity, repository, use cases
- Password reset token entity, repository, use cases
- Email verification use cases
- Related events, listeners, async messages

Auth module depends on User module (reads users by email/username).

User module does not depend on Auth module.

## 1.3 Infrastructure Security Layer

    src/Infrastructure/Security/

Contains Symfony Security component integrations.

- `UserIdentity` — read-only DTO implementing `UserInterface` and `PasswordAuthenticatedUserInterface`
- `Provider/ByUsernameProvider` — `UserProviderInterface` implementation, bridges `UserRepository` to Symfony Security
- Authenticators (when custom auth flows are needed, e.g. OTP)
- UserChecker (when status-based access control is needed)
- Voters (when attribute-based authorization is needed)
- Success/Failure handlers (when custom post-auth behavior is needed)

---

# 2. Design Decisions

## 2.1 UserIdentity Separation

Symfony Security works with `UserIdentity`, not the Doctrine `User` entity directly.

`ByUsernameProvider` loads `User` from database, converts to `UserIdentity` via `UserIdentity::fromUser()`.

This prevents Doctrine entity serialization into session and keeps the Security layer decoupled from entity internals.

## 2.2 Authentication is Infrastructure

Standard form login (email + password) is handled entirely by Symfony `form_login`.

No custom authenticator, no use case, no command bus involvement.

Projects configure `form_login` in their `security.yaml` with desired parameters (login_path, field names, default_target_path, remember_me).

## 2.3 OTP and Password Reset are Use Cases

Unlike form login, OTP and password reset flows contain business logic:

- Code/token generation with TTL
- Rate limiting
- Invalidation after use
- Async email dispatch

These live as Command + Handler in the Auth module.

OTP authenticator in Infrastructure is a thin wrapper that delegates validation to the use case handler and creates a `SelfValidatingPassport` on success.

## 2.4 Error Handling in Auth Flows

Standard form login: Symfony stores `AuthenticationException` in session. Login controller reads it via `AuthenticationUtils::getLastAuthenticationError()` and maps to a user-facing message.

Status-based blocking: `UserChecker` throws `CustomUserMessageAccountStatusException`. Same mechanism — exception stored in session, controller displays the message.

Projects control the error-to-message mapping in their login controller.

---

# 3. Project Configuration

The skeleton provides components. Projects assemble them in `security.yaml`.

## 3.1 Minimal Setup (form login)

    security:
        providers:
            users_by_username:
                id: App\Infrastructure\Security\Provider\ByUsernameProvider
        firewalls:
            main:
                lazy: true
                provider: users_by_username
                form_login:
                    login_path: app_login
                    check_path: app_login
                    username_parameter: username
                    password_parameter: password
                    default_target_path: /dashboard
                logout:
                    path: app_logout

## 3.2 Adding UserChecker

When status-based blocking is needed:

1. Create `App\Infrastructure\Security\Checker\UserChecker` implementing `UserCheckerInterface`
2. Register in firewall: `user_checker: App\Infrastructure\Security\Checker\UserChecker`

## 3.3 Adding OTP Authentication

When OTP login is needed:

1. Auth module provides `RequestOtp` and `VerifyOtp` use cases
2. `OtpAuthenticator` in Infrastructure delegates to `VerifyOtpHandler`
3. Register authenticator in firewall under `custom_authenticators`

## 3.4 Adding API Firewall

When API authentication is needed:

1. Add separate `api` firewall with `stateless: true`
2. Add API-specific success/failure handlers returning `ApiResponse` envelope
3. Configure authenticator (form_login, JWT, or token-based)

---

# 4. File Ownership

| File | Owner | Rule |
|---|---|---|
| `BaseUser.php` | Skeleton | Skeleton updates stable auth fields |
| `BaseUser::create()`, `createRoot()` | Skeleton | Base implementations; projects MUST override in User when adding required fields |
| `User.php` | Project | Skeleton creates once, never modifies after |
| `UserStatus`, `UserType`, `UserRole` | Shared | Skeleton defines base cases, projects extend |
| `UserIdentity` | Skeleton | Stable DTO, rarely changes |
| `ByUsernameProvider` | Skeleton | Stable provider, rarely changes |
| `security.yaml` | Project | Skeleton provides no default auth config |

Enum files (UserStatus, UserType, UserRole) may produce minor merge conflicts when both skeleton and project add new cases. These conflicts are trivial to resolve.

---

# 5. Testing

## Unit Tests (in skeleton)

- `User::create()` — factory correctness, field defaults, domain events
- `UserIdentity::fromUser()` — field mapping, role defaults
- `ByUsernameProvider` — load, not found, refresh, supports

## Functional Tests (in projects)

- Login success → redirect
- Login failure → redirect with error message
- Blocked user → specific error message
- Protected route → redirect to login when anonymous
- Protected route → accessible after login

Functional auth tests require `form_login` configuration and belong in projects, not in the skeleton.
