# Auth API Contract

All endpoints are JSON-only and live under the `/api/v1/auth` prefix.

## POST `/api/v1/auth/login`

Authenticates a user with email and password and returns a token pair.

**Request body**

```json
{
  "email": "john.doe@example.com",
  "password": "password123"
}
```

**Response 200**

```json
{
  "token_type": "Bearer",
  "access_token": "<jwt>",
  "expires_in": 900,
  "refresh_token": "<refresh-token>",
  "refresh_expires_in": 604800,
  "scope": ["ROLE_USER"],
  "issued_at": "2025-11-08T12:00:00+00:00",
  "expires_at": "2025-11-08T12:15:00+00:00",
  "refresh_expires_at": "2025-11-15T12:00:00+00:00"
}
```

**Errors**

* `400 invalid_payload` – Missing email/password.
* `401 invalid_credentials` – Invalid email or password.

## POST `/api/v1/auth/token/refresh`

Rotates a valid refresh token and returns a brand-new token pair. Refresh tokens are single-use.

**Request body**

```json
{
  "refresh_token": "<refresh-token>"
}
```

**Response 200** – Same payload as login with new tokens.

**Errors**

* `400 invalid_payload` – Missing refresh token.
* `401 invalid_refresh_token` – Refresh token expired, revoked, or unknown.

## POST `/api/v1/auth/logout`

Revokes the provided refresh token. The access token becomes unusable once it naturally expires.

**Request body**

```json
{
  "refresh_token": "<refresh-token>"
}
```

**Response 204** – Empty body.

**Errors**

* `400 invalid_payload` – Missing refresh token.

---

## Token Claims

Access tokens (JWT RS256) include the following claims:

* `iss` / `aud` – configured via `JWT_ISSUER` and `JWT_AUDIENCE`.
* `sub` – internal user identifier.
* `email` – user email.
* `roles` – granted Symfony roles.
* `iat`, `nbf`, `exp`, `jti` – issuance metadata.

Refresh tokens are random 128-byte hex strings, hashed with SHA-512 + pepper before persistence and rotated on every use.
