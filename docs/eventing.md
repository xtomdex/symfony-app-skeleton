# EVENTING CONVENTIONS

This document defines naming conventions and structural rules for:

-   Domain Events\
-   Common (public) Events\
-   Event Listeners\
-   Event Subscribers\
-   Async Messages\
-   Message Handlers\
-   Aggregate Roots

The goal is consistency, clarity and long-term maintainability.

------------------------------------------------------------------------

# 1. Domain Events

## 1.1 Naming Rule

Domain events are named in **Past Tense**.

They describe something that **already happened**.

Correct examples:

-   `UserVerified`
-   `OrderPaid`
-   `MealRescheduled`
-   `PasswordChanged`
-   `CustomerCreated`

Incorrect examples:

-   `VerifyUser`
-   `PayOrder`
-   `RescheduleMeal`
-   `ChangePassword`

Events describe facts, not intentions.

------------------------------------------------------------------------

## 1.2 Location

### Internal module events:

    src/Modules/<Module>/Event/*

Example:

    App\Modules\User\Event\UserVerified

### Common (cross-module) events:

    src/Domain/Eventing/Event/Common/*

Example:

    App\Domain\Eventing\Event\Common\UserVerified

------------------------------------------------------------------------

## 1.3 Event Data Rules

Events must contain only:

-   Minimal required data
-   No full entity objects
-   No infrastructure types
-   No services

Typical structure:

``` php
final class UserVerified implements DomainEventInterface
{
    public function __construct(
        private readonly string $userId,
        private readonly string $email,
        private readonly \DateTimeImmutable $occurredAt,
    ) {}
}
```

------------------------------------------------------------------------

# 2. Common Events (Public Contract)

Common events are stable contracts between modules.

Other modules must not listen to internal module events directly.

Instead:

Internal Event → Bridge Listener → Common Event

Common events:

-   Are placed in `Domain`
-   Represent stable integration points
-   Can be referenced safely by any module

------------------------------------------------------------------------

# 3. Event Listeners

Listeners handle a single event.

Use `#[AsEventListener]`.

## 3.1 Naming Formula

    On<Event><Action>

Examples:

-   `OnUserVerifiedQueueWelcomeEmail`
-   `OnOrderPaidCreateInvoice`
-   `OnMealRescheduledNotifyOperator`
-   `OnUserVerifiedPublishCommonEvent`

If listener bridges internal event to common event:

-   `OnUserVerifiedPublishCommonEvent`

If listener queues async work:

-   `OnUserVerifiedQueueWelcomeEmail`

------------------------------------------------------------------------

## 3.2 Location

    src/Modules/<Module>/EventListener/*

------------------------------------------------------------------------

## 3.3 Rules

-   One listener = one responsibility
-   One listener = one event
-   Small, focused classes
-   No heavy logic inside listener

------------------------------------------------------------------------

# 4. Event Subscribers

Subscribers are used only when:

-   One class listens to multiple events
-   Centralized event mapping is required

## 4.1 Naming

    <Module>EventSubscriber

Examples:

-   `UserEventSubscriber`
-   `OrderEventSubscriber`

------------------------------------------------------------------------

## 4.2 Location

    src/Modules/<Module>/EventSubscriber/*

------------------------------------------------------------------------

## 4.3 When NOT to Use Subscriber

Do NOT use subscribers for:

-   Single event handlers
-   Small side-effects
-   Async bridge logic

Prefer `Listener` in those cases.

------------------------------------------------------------------------

# 5. Async Messages (Messenger)

Messages represent actions to be executed.

They are commands, not events.

## 5.1 Naming Rule

Use imperative form (action).

Examples:

-   `SendWelcomeEmail`
-   `SyncUserToCrm`
-   `GenerateInvoicePdf`
-   `DispatchWebhook`
-   `CreateInvoice`

Avoid:

-   `WelcomeEmailMessage`
-   `UserSynced`
-   `InvoiceGenerated`

Messages are not facts --- they are instructions.

------------------------------------------------------------------------

## 5.2 Location

    src/Modules/<Module>/Message/*

------------------------------------------------------------------------

# 6. Message Handlers

Handlers execute async work.

## 6.1 Naming Formula

    <MessageName>Handler

Examples:

-   `SendWelcomeEmailHandler`
-   `SyncUserToCrmHandler`
-   `GenerateInvoicePdfHandler`

------------------------------------------------------------------------

## 6.2 Location

    src/Modules/<Module>/MessageHandler/*

------------------------------------------------------------------------

## 6.3 Method Signature

``` php
public function __invoke(SendWelcomeEmail $message): void
```

------------------------------------------------------------------------

# 7. Bridge Pattern (Internal → Common)

When an internal module event must be exposed to other modules:

1.  Internal event is raised:

        Modules/User/Event/UserVerified

2.  Bridge listener publishes:

        Domain/Eventing/Event/Common/UserVerified

3.  Other modules listen to the Common event only.

Rule:

Modules must not depend on other modules' internal events.

------------------------------------------------------------------------

# 8. Aggregate Roots

Aggregates that produce events:

-   Implement `AggregateRoot`
-   Use `EventsTrait`
-   Call `recordEvent()` inside domain methods

Example:

``` php
public function verify(): void
{
    $this->status = Status::VERIFIED;

    $this->recordEvent(new UserVerified($this->id, $this->email));
}
```

------------------------------------------------------------------------

# Quick Reference

  Type             Tense        Example
  ---------------- ------------ ----------------------------
  Domain Event     Past         `UserVerified`
  Common Event     Past         `UserVerified`
  Listener         On + Event   `OnUserVerifiedQueueEmail`
  Subscriber       Noun         `UserEventSubscriber`
  Message          Imperative   `SendWelcomeEmail`
  MessageHandler   +Handler     `SendWelcomeEmailHandler`
