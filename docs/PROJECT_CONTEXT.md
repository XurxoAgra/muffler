# Proyecto: muffler

Contexto de referencia para redactar prompts. Pegar entero o por secciones.

API JSON Symfony 7.4 / PHP 8.4 (floor composer 8.2) / Doctrine ORM 3 / PostgreSQL.
Dominio: mantenimiento de vehículos (matrícula, revisiones, facturas).
Sin frontend. Todo corre en el contenedor `muffler-api-webserver` (Makefile, nunca php/composer en host).

## Arquitectura

DDD + hexagonal. 4 contextos: Auth, Vehicle, Maintenance, Shared.
`src/<Context>/{Domain,Application,Infrastructure}`. Dependencias hacia dentro.

- Domain: agregados, VO, eventos, interfaces de repositorio. Prohibido Symfony/Doctrine.
- Application: 1 carpeta por caso de uso = `<Verbo><Nombre>Command|Query.php` + `Handler.php` con un único `handle()`. Prohibido Symfony/Doctrine/HTTP. Queries devuelven DTO, nunca agregados.
- Infrastructure: `Persistence/` (repos Doctrine + mapping **XML**, nunca atributos ORM), `Http/` (Controller + `Request/*Request.php` DTO), `Security/`, `Console/`, `Ocr/`, `Storage/`, `Import/`.
- Controllers mapean excepciones de dominio a códigos HTTP. Sin lógica de negocio.

Regla completa: `.claude/rules/architecture.md`. Skill `/use-case` para añadir casos de uso.

## Cableado que autowiring NO hace

- Puerto nuevo (interfaz): línea explícita `Port: '@Adapter'` en `config/services.yaml` (`src/**/Domain/` y `src/**/Http/Request/*Request.php` están excluidos del resource).
- Entidad nueva: `auto_mapping: false`. Hay que escribir XML en `Infrastructure/Persistence/Mapping/` y, si cae fuera de los 4 dirs existentes, añadir entrada en `mappings:` de `config/packages/doctrine.yaml`.

## Modelo de datos

- `users` (User: UserId/UserEmail/UserPassword VO, firstName, lastName, roles, timestamps). Roles: ROLE_USER, ROLE_ADMIN.
- `refresh_tokens` (gesdinet).
- `vehicles` (id string 36, plate, year smallint, type enum car|moto|van|truck, ownerId, vin unique nullable, customMake/customModel, FK make/model).
- `vehicle_makes` / `vehicle_models` (catálogo, 1:N).
- `vehicle_users` (userId, role enum owner|shared, FK vehicle) — compartición.
- `maintenance_records` (uuid, serviceDate, mileage, notes, cost decimal(10,2), shopName, nextServiceDate, verified bool, FK vehicle/type/invoice/createdBy).
- `maintenance_record_type` (uuid, key unique, icon, defaultPeriodicityMonths, defaultPeriodicityKm, active).
- `invoices` (uuid, filePath, amount, date, shopName, description, status enum pending|verified, FK vehicle/uploadedBy).

## Endpoints

- POST `/api/auth/register`, `/api/auth/login`, `/api/auth/refresh`, `/api/auth/logout`; GET `/api/auth/me`.
- GET/POST `/api/vehicles`, GET/PUT/DELETE `/api/vehicles/{uuid}`.
- GET `/api/vehicle-makes`, GET `/api/vehicle-makes/{id}/models`.
- GET `/api/maintenance-record-types`.
- GET/POST `/api/vehicles/{vehicleId}/maintenance-records`; GET/PUT/DELETE `/api/maintenance-records/{id}`.
- POST `/api/vehicles/{vehicleId}/invoices/scan`.

## Auth

LexikJWT + refresh tokens. Firewalls `login` (json_login, `email`/`password`) y `api` (stateless, jwt, refresh-jwt).
Público: login, register, refresh, `/api/doc`. Resto ROLE_USER.
Login/refresh apuntan a `UnreachableController` a propósito: el firewall intercepta antes del router.
Autorización por recurso con `VehicleVoter` (VIEW/EDIT/DELETE) vía `denyAccessUnlessGranted`.
Usuario actual: `#[CurrentUser] SymfonyUserAdapter $authUser` (`->userId`).

## Estilo

php-cs-fixer `@Symfony`, phpstan nivel 5 sobre `src`. `declare(strict_types=1)` siempre.
Clases `final readonly` por defecto, propiedades promovidas `private`. 4 espacios, LF.
Inglés en todo (identificadores, comentarios, commits, strings). Strings en español = legacy.
Sin `else` tras return/throw. Sin `new` en Domain/Application. Sin magic strings: enums/VO.

## Comandos (todos vía Makefile, dentro del contenedor)

`make init|start|stop|down|install|bash|create-db|migrate|migration/diff|clear`
`make style/fix|style/code-style|style/static-analysis|style/all` ← ejecutar antes de commit (no hay CI ni hook).
Único comando de app: `php bin/console app:import-vehicle-catalog`.
Targets muertos (NO usar): `test/*`, `rbac*`, `rebuild-db`, `queue/*`, `consume/ticketing`, `init/websocket`, `uuid`, `clear/pool/mail`.

## Estado

- Sin suite de tests. PHPUnit no instalado, sin `tests/` (aunque composer ya mapea `App\Tests\`). No scaffoldear tests salvo petición.
- `OcrServiceInterface` → `NullOcrService`: OCR de facturas sin implementar.
- Git: rama desde `develop` como `feature/<nombre>`, PR a `develop`. `main` = release.

## Local

App http://localhost:8080. Postgres host 3306 → 5432, DB/user/pass `muffler`/`root`/`root`.
Colección Bruno: `docs/bruno/muffler_api/`. Fragmentos OpenAPI: `docs/api/*.yaml`.

## Qué añadir en cada prompt concreto

**Qué** cambiar, **en qué contexto** (Auth/Vehicle/Maintenance), **contrato del endpoint** (método, ruta, payload, respuesta, códigos de error) y si toca **migración**.
