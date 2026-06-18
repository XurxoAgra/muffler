# Guía de pruebas — Módulo de autenticación

Esta guía explica cómo probar manualmente el módulo de autenticación (`src/Auth`)
implementado con JWT (Lexik) + refresh tokens (Gesdinet) sobre Symfony 7.4.

## 0. Prerrequisitos

Levanta los contenedores y asegúrate de que la base de datos y las claves JWT
existen:

```bash
make start                       # o: docker compose --env-file ./docker/develop/.env up -d

# Solo la primera vez / si no existen:
docker exec muffler-api-webserver php bin/console lexik:jwt:generate-keypair --skip-if-exists
docker exec muffler-api-webserver php bin/console doctrine:database:create --if-not-exists
docker exec muffler-api-webserver php bin/console doctrine:migrations:migrate --no-interaction
```

La API escucha en `http://localhost:85` (puerto `PORT_HTTP` definido en
`docker/develop/.env`).

## 1. Endpoints

| Método | Ruta                  | Auth requerida | Descripción                              |
|--------|-----------------------|----------------|-------------------------------------------|
| POST   | `/api/auth/register`  | No             | Crea un usuario y devuelve un par de tokens |
| POST   | `/api/auth/login`     | No             | Autentica con email + password            |
| GET    | `/api/auth/me`        | Sí (Bearer)    | Devuelve el perfil del usuario autenticado |
| POST   | `/api/auth/refresh`   | No             | Cambia un refresh token por un access token nuevo |
| POST   | `/api/auth/logout`    | Sí (Bearer)    | Invalida el refresh token indicado        |

Todas las respuestas son JSON. Los errores siempre tienen esta forma:

```json
{ "error": { "code": "SNAKE_CASE_CODE", "message": "...", "details": {} } }
```

## 2. Flujo completo con curl

### 2.1 Registro

```bash
curl -s -X POST http://localhost:85/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "ana@example.com",
    "password": "Password1",
    "first_name": "Ana",
    "last_name": "García"
  }' | jq
```

Esperado: `201` con `access_token` y `refresh_token`.

### 2.2 Guardar los tokens en variables de shell

Para no copiar/pegar tokens a mano en cada llamada:

```bash
RESP=$(curl -s -X POST http://localhost:85/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "ana@example.com", "password": "Password1"}')

ACCESS_TOKEN=$(echo "$RESP" | python3 -c "import sys,json;print(json.load(sys.stdin)['access_token'])")
REFRESH_TOKEN=$(echo "$RESP" | python3 -c "import sys,json;print(json.load(sys.stdin)['refresh_token'])")

echo "$RESP" | jq
```

Esperado: `200` con `access_token` y `refresh_token`.

### 2.3 Perfil autenticado

```bash
curl -s http://localhost:85/api/auth/me \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq
```

Esperado: `200` con los datos del usuario (sin campo `password`).

### 2.4 Refrescar el access token

```bash
curl -s -X POST http://localhost:85/api/auth/refresh \
  -H "Content-Type: application/json" \
  -d "{\"refresh_token\": \"$REFRESH_TOKEN\"}" | jq
```

Esperado: `200` con un `access_token` nuevo (y `refresh_token`).

### 2.5 Logout

```bash
curl -s -X POST http://localhost:85/api/auth/logout \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"refresh_token\": \"$REFRESH_TOKEN\"}" | jq
```

Esperado: `200`. Un refresh posterior con el mismo `refresh_token` debe fallar.

```bash
curl -s -X POST http://localhost:85/api/auth/refresh \
  -H "Content-Type: application/json" \
  -d "{\"refresh_token\": \"$REFRESH_TOKEN\"}" | jq
```

Esperado: `401`.

## 3. Casos de error a verificar

```bash
# Email duplicado → 409 EMAIL_TAKEN
curl -s -X POST http://localhost:85/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email": "ana@example.com", "password": "Password1", "first_name": "Ana", "last_name": "G"}' | jq

# Contraseña incorrecta → 401 INVALID_CREDENTIALS
curl -s -X POST http://localhost:85/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "ana@example.com", "password": "wrong"}' | jq

# Sin JWT → 401 INVALID_CREDENTIALS ("JWT Token not found")
curl -s http://localhost:85/api/auth/me | jq

# JWT inválido/manipulado → 401 INVALID_CREDENTIALS ("Invalid JWT Token")
curl -s http://localhost:85/api/auth/me -H "Authorization: Bearer garbage" | jq

# Email con formato inválido → 400 VALIDATION_ERROR
curl -s -X POST http://localhost:85/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email": "notanemail", "password": "Password1", "first_name": "A", "last_name": "B"}' | jq

# Password sin mayúscula/número o demasiado corta → 400 VALIDATION_ERROR (con "details")
curl -s -X POST http://localhost:85/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email": "otro@example.com", "password": "abc", "first_name": "A", "last_name": "B"}' | jq
```

## 4. Probar con Postman / Insomnia

1. Crea una **colección** con las 5 peticiones de la sección 2.
2. En la petición de `login` o `register`, en la pestaña **Tests/Post-response
   script**, guarda los tokens en variables de entorno:
   ```js
   const body = pm.response.json();
   pm.environment.set("access_token", body.access_token);
   pm.environment.set("refresh_token", body.refresh_token);
   ```
3. En `me` y `logout`, usa `Authorization: Bearer {{access_token}}`.
4. En `refresh` y `logout`, usa `{{refresh_token}}` en el body.

## 5. Inspeccionar la base de datos (DBeaver)

Conexión PostgreSQL desde el host (ver `docs/authentication` o el README del
proyecto para más detalle):

- Host: `localhost`
- Puerto: `3385`
- Base de datos / usuario / contraseña: `muffler_api`

Tablas relevantes:

- `users`: usuarios registrados (la contraseña se guarda hasheada, nunca en claro).
- `refresh_tokens`: tokens de refresco vivos. Tras un `logout`, la fila
  correspondiente desaparece (se borra, no se marca).

## 6. Ver el contenido de un JWT

El `access_token` es un JWT estándar. Puedes decodificar el payload (sin
verificar la firma) así:

```bash
echo "$ACCESS_TOKEN" | cut -d '.' -f2 | base64 -d 2>/dev/null | jq
```

Deberías ver algo como:

```json
{
  "iat": 1781728766,
  "exp": 1781732366,
  "roles": ["ROLE_USER"],
  "email": "ana@example.com",
  "username": "ana@example.com"
}
```

`exp - iat` = 3600 segundos (1 hora), que es el TTL configurado en
`config/packages/lexik_jwt_authentication.yaml`.

## 7. Reinicio limpio (borrar todos los usuarios/tokens)

Si quieres repetir las pruebas desde cero:

```bash
docker exec muffler-api-postgres psql -U muffler_api -d muffler_api \
  -c "TRUNCATE users, refresh_tokens"
```
