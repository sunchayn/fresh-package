# Modules

Business logic for a domain lives under `src/Modules/<Domain>/`.

Name each domain in PascalCase, for example `Greeting`.

A module holds domain logic only. Application logic, the part that exposes a module to the outside world, lives outside `src/Modules`. This includes console commands under `src/Console`, and HTTP controllers, requests, and resources under `src/Http`.

## Subfolders

- `Actions/`. Single method `VerbNounAction` classes. Each one does one thing.
- `DataTransferObjects/`. The `final readonly` DTOs that provision Actions.
- `Services/`. Logic that spans multiple Actions, or wraps a complex external SDK. Compose Actions instead of growing one service into several responsibilities.
- `Contracts/`. Interfaces a domain exposes so another domain can depend on a boundary instead of a concrete class. Add one only when a second domain actually needs to cross that boundary.
- `Queries/`. Read-only classes for a non-trivial read, such as pagination, joins, filtering, or eager-loaded relations.
- `Enums/`. Domain enumerations with TitleCase keys, for example `FavoritePerson` rather than `FAVORITE_PERSON`.
