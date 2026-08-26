---
paths:
  - "src/**"
---

# DDD + hexagonal architecture rules

Every bounded context (`Auth`, `Vehicle`, `Maintenance`, `Shared`) is organized into three
concentric layers. Dependency arrows always point inward: `Domain ← Application ← Infrastructure`.

```
src/<Context>/
├── Domain/          # Aggregates, Value Objects, Domain Events, Repository interfaces (ports)
├── Application/      # Use cases: <Verb><Noun>Command.php + <Verb><Noun>Handler.php
└── Infrastructure/
    ├── Persistence/  # Doctrine repositories + XML mapping (never annotations)
    ├── Http/          # Controllers + Request DTOs
    └── Security/      # Symfony security adapters, etc.
```

## Import rules (strict)

| Layer | Can import | Cannot import |
|---|---|---|
| Domain | other Domain classes, PHP stdlib | Symfony\*, Doctrine\*, any vendor package |
| Application | Domain classes, PHP stdlib | Symfony\*, Doctrine\*, HTTP request/response objects |
| Infrastructure | Application, Domain, Symfony\*, Doctrine\*, any vendor | business logic (delegate to handlers) |
| Http (sub-layer of Infrastructure) | Application commands/queries/handlers, Symfony HttpFoundation | Domain directly (except domain exceptions, for mapping to HTTP responses) |

Ports (interfaces) are defined in Domain or Application; adapters (concrete implementations)
live in Infrastructure. `config/services.yaml` explicitly binds each port to its adapter —
Symfony's autowiring does not resolve interfaces on its own.

## Use case pattern (Application layer)

Each use case is a `Command`/`Query` + `Handler` pair, one folder per use case:

```php
// Command: immutable data bag, no logic
final class CreateVehicleCommand {
    public function __construct(
        public readonly string $brand,
        public readonly string $model,
    ) {}
}

// Handler: orchestrates domain + ports, no HTTP concern
final class CreateVehicleHandler {
    public function __construct(private readonly VehicleRepository $vehicles) {}

    public function handle(CreateVehicleCommand $command): Vehicle { /* ... */ }
}
```

- One public method: `handle(XxxCommand $command): SomeReturnType`.
- Never inject the HTTP Request. Never throw HTTP exceptions — throw domain exceptions;
  the controller maps them to HTTP status codes.
- Queries return DTOs/read models, never domain aggregates.

## Domain conventions

- Named constructors instead of public constructors with logic, e.g. `User::register(...)`.
- No public setters — mutate through intent-revealing methods.
- Wrap primitives (emails, IDs, money) in Value Objects rather than passing raw scalars.
- Domain Events are immutable, named in past tense, recorded internally via something like
  `$this->recordEvent(new VehicleWasCreated(...))`.
- Repository interfaces expose one method per use case need, not generic CRUD.

## Clean code rules

- No `else` after `return`/`throw` — use early returns.
- No `new` inside Domain or Application services — inject dependencies via constructor.
- No magic strings/numbers — use constants, enums, Value Objects.

## What NOT to do

| Don't | Do instead |
|---|---|
| Annotate domain entities with `#[ORM\...]` | Use Doctrine XML mapping in Infrastructure |
| Import Symfony/Doctrine classes in Domain or Application | Use ports (interfaces) |
| Put business logic in controllers | Put it in handlers or the domain |
| Throw `HttpException` from a handler | Throw domain exceptions; map them in the controller |
| Use `EntityManager` directly in a controller | Call the repository port |
| Return domain aggregates from query handlers | Return DTOs / read models |
| Use `new` for services inside domain/application | Inject via constructor |
