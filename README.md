# AI Website Builder SaaS

RILT monolith (React + Inertia + Laravel + Tailwind). A user types a prompt; an AI agent produces a structured site spec; deterministic rendering turns it into HTML; a sandboxed Node service compiles it; users preview and edit visually or via code.

## Stack

- Laravel 12 monolith — no separate REST/JSON API; all data flows via Inertia props
- laravel/react-starter-kit (React 19 + TS + Tailwind + shadcn/ui)
- laravel/ai — agents, structured output, provider failover (OpenRouter free models)
- laravel/cashier — Stripe billing (optional locally)
- laravel/reverb + laravel-echo — real-time generation progress
- Node.js compile worker (`compile-service/`) — internal only, never public

## Running locally

```bash
composer install && npm install
cp .env.example .env          # then set DB_* (mysql), OPENROUTER_API_KEY, etc.
php artisan migrate --seed    # creates plans + admin@example.com / test@example.com (password: password)

# terminal 1 — app
php artisan serve

# terminal 2 — queue worker (generation + compile jobs)
php artisan queue:work

# terminal 3 — websockets (live generation progress)
php artisan reverb:start

# terminal 4 — compile worker (internal HTTP service)
cd compile-service && npm start   # listens on 127.0.0.1:5178 only

npm run dev   # vite for frontend assets
```

## Key env vars

| Var | Purpose |
|---|---|
| `OPENROUTER_API_KEY` | AI provider (free models only by default) |
| `OPENROUTER_MODEL` / `OPENROUTER_BACKUP_MODEL` | Failover chain (default `z-ai/glm-5.2:free`, `minimax/minimax-m3:free`) |
| `COMPILE_SERVICE_URL` / `COMPILE_SERVICE_TOKEN` | Internal compile worker endpoint + shared secret |
| `STRIPE_KEY` / `STRIPE_SECRET` | Optional; without them paid plans show "billing not configured" |

## How a generation flows

1. `POST /projects` (rate-limited 5/min/user) — **server-side** credit deduction in one locked transaction with a ledger row (`credit_service`), then queues `GenerateWebsiteJob`.
2. The job calls `SiteBuilderAgent` (laravel/ai agent with JSON-schema structured output). Provider failover is handled by laravel/ai across the free-model chain.
3. Spec is persisted to the project; a `ProjectVersion` is created with rendered HTML.
4. `CompileSiteJob` writes source files under `storage/app/sites/{project}/src/{version}` and POSTs to the Node worker, which validates paths inside its sandboxed workspace root, minifies, and emits build output.
5. Progress broadcasts on private `generations.{userId}` channel; the UI also partial-reloads as a fallback so it works even without Reverb running.
6. Failed generations automatically refund the credit.

## Editors

Both editors write new immutable `ProjectVersion` rows against the same source of truth:

- **Visual editor** edits the spec → code is deterministically re-rendered from spec.
- **Code editor** edits raw HTML → spec is preserved untouched.

Trade-off note: last-save-wins per domain — saving visual after code regenerates code from the spec (code history remains in prior versions).

## Security posture

- Credits/billing enforced server-side only (`CreditService`, row locks, non-negative invariant)
- Inertia shared props are whitelisted fields — no model serialization, no secrets
- AI keys live only in `.env`; compile worker binds to 127.0.0.1 and requires a shared token
- Generated code treated as untrusted: path traversal rejected everywhere, output escaped at render time
- Admin routes behind `EnsureUserIsAdmin` middleware

## Tests

```bash
php artisan test   # 51 passing — credits, billing, generation (faked AI), editors, admin, auth
```
