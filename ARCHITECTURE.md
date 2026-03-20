# Architecture: l5-repository

## Purpose
A Laravel 5+ package that implements the Repository pattern on top of Eloquent. Provides a data-access abstraction layer with built-in caching, criteria filtering, presenters (via Fractal), and artisan code generators.

## Directory Structure
```
src/Prettus/Repository/
  Contracts/
    Repository_Interface.php          # All CRUD + filter methods
    Repository_Criteria_Interface.php # Criteria management
    Cacheable_Interface.php           # Cache decoration contract
    Presenter_Interface.php / Presentable.php / Transformable.php
  Eloquent/
    Base_Repository.php               # Core implementation — all Eloquent + criteria + cache logic
  Criteria/
    Request_Criteria.php              # Auto-applies search/order/filter from HTTP request
  Traits/
    Cacheable_Repository.php          # Adds response caching to any repository method
    Presentable_Trait.php             # Attaches a Fractal presenter to results
    Transformable_Trait.php
  Events/
    Repository_Entity_{Created,Updated,Deleted,...}.php  # Domain events
  Generators/
    Commands/                         # Artisan commands: make:repository, make:criteria, etc.
    *_Generator.php                   # Stub-based file generators
    Migrations/                       # Schema/migration parsing for generator
  Presenter/
    Fractal_Presenter.php             # Base presenter using league/fractal
  Providers/
    Repository_Service_Provider.php   # Laravel service provider
  Exceptions/
  Helpers/Cache_Keys.php
  Listeners/Clean_Cache_Repository.php
  resources/config/repository.php    # Package configuration
  resources/lang/                    # i18n for error messages
```

## Key Design Decisions
- **Decorator stack** — the `Cacheable_Repository` trait wraps Eloquent results in a cache layer without changing the repository interface.
- **Criteria pattern** — filtering logic is extracted into `Criteria_Interface` implementations that are pushed onto the repository; `Request_Criteria` auto-maps query string parameters.
- **Code generation** — artisan commands generate repository interfaces, Eloquent implementations, criteria, and presenters from stub templates, reducing boilerplate.
- **Event dispatch** — all write operations fire typed events (`Repository_Entity_Created`, etc.) that can be listened to for cache invalidation or audit logging.

## Extension Points
- Extend `Base_Repository` and implement `model()` returning your Eloquent model class name.
- Create custom `Criteria_Interface` implementations for reusable query scopes.
- Implement `Presenter_Interface` with a Fractal transformer for API response shaping.

## Dependency Flow
```
Controller
  └─ Repository (Base_Repository + Cacheable_Repository trait)
       ├─ Criteria stack → filters the Eloquent query
       ├─ Eloquent Model → database
       ├─ Cache layer → wraps read results
       └─ Events → Clean_Cache_Repository listener
```
