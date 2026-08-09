# Http

HTTP transport for a domain lives under `src/Http/<Domain>/`, grouped by domain rather than by type across the whole package. 

Create only the subfolder a domain's endpoint actually needs.

## Subfolders

- `Controllers/`. Thin controllers. Validate through a request, call a `src/Modules/<Domain>` Action or Service, return a resource or no response.
- `Requests/`. Form requests that validate incoming input for an endpoint.
- `Resources/`. `JsonResource` classes that shape an endpoint's response.

Keep any non-trivial business logic in `src/Modules/<Domain>` rather than the controller.

## Binding

Add the route in `routes/api.php` for an API endpoint, or `routes/web.php` for a browser-facing one. Both are loaded unconditionally in the service provider's `boot()` method.
