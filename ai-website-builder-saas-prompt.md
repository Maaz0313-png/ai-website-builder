# Project: AI Website Builder SaaS (Build From Scratch)

## Role
You are a senior full-stack Laravel developer building this application end-to-end on the RILT stack (React, Inertia, Laravel, Tailwind). You own architecture, database design, backend logic, and frontend implementation as one cohesive monolith — no separate API service, no token-based auth layer. Work in the phase order defined below, verify each phase before moving to the next, and flag any tradeoff before making it silently.

## Source / Inspiration
Based on Freelancer.com project: "Senior Full-Stack Developer Required – AI Website Builder SaaS"
https://www.freelancer.com/projects/laravel/senior-full-stack-developer-required

Original listing was for an existing purchased codebase. This spec reframes it as a **greenfield build** — same feature set, built from zero.

## Goal
Build a SaaS platform where a user types a natural-language prompt and the system generates a full, editable, deployable website.

## Tech Stack (RILT — no separate API backend)
- Backend: Laravel (PHP) — monolith, no REST/API layer
- Frontend: React (JS/TS) via **Inertia.js** — pages are Laravel-routed, data passed as props directly from controllers, no fetch/axios calls to a separate API
- Styling: Tailwind CSS
- AI layer: **laravel/ai** (official Laravel AI SDK — https://github.com/laravel/ai)
- Database: MySQL
- Build layer: Node.js service that compiles generated site code into deployable static/bundled output
- Queue: Laravel queues (Redis) for generation jobs and compile jobs
- Auth: **laravel/react-starter-kit** (official Laravel + React starter kit — https://github.com/laravel/react-starter-kit) — Inertia + React 19 + TypeScript + Tailwind + shadcn/ui + radix-ui, with auth scaffolding included, standard session-based auth, no API tokens/Sanctum needed
- Real-time: Laravel Echo/Reverb for streaming generation progress into Inertia pages

## Why Inertia (no API backend)
- Controllers return Inertia responses (`Inertia::render(...)`) with props — React pages consume props directly, no separate JSON API to design, version, or secure.
- One auth system (Laravel sessions) instead of building/maintaining a token-based API auth layer.
- Faster to build and fewer moving parts — ideal for a single-team, single-frontend SaaS like this.
- CSRF protection and validation stay standard Laravel — no separate API validation/exception layer needed.
- The only external HTTP boundary in this system is Laravel → Node.js compile service (internal, not public-facing) and Laravel → AI providers (via laravel/ai).

## Why laravel/ai
- `composer require laravel/ai` — one package, multiple providers (OpenAI, Anthropic, Gemini, etc.) behind one API — no need to hand-build a provider abstraction layer.
- Built-in **automatic failover** between providers/models on outage or rate limit — covers the "multiple AI provider integrations" requirement natively.
- Built-in **agents** (instructions, memory, tools, structured output) — use this for the prompt → site-spec generation step.
- Built-in **streaming, broadcasting, and queueing** — use for real-time generation progress in the UI.
- Built-in **testing tools** (fake agents, fake responses) — write tests without burning real API credits.
- Structured output support — have the agent return a typed JSON site-spec (pages/sections/components) instead of parsing free text.

## Core Features to Build

### 1. Auth & Accounts
- Registration, login, email verification, password reset — comes scaffolded with `laravel/react-starter-kit`
- Session-based auth throughout, no API tokens
- User profile management page (already scaffolded, extend as needed)
- UI components: shadcn/ui + radix-ui (bundled with the starter kit) for forms, dialogs, dropdowns, etc. — use these instead of building custom primitives

### 2. Subscription & Credit System
- Plans (free/paid tiers) with defined credit allowances
- Credit deduction per AI generation call
- Hard enforcement — no negative balances, no client-side bypass (enforced server-side in the controller/job, never trust props sent back from the client)
- Stripe (or similar) integration for billing — Laravel Cashier fits naturally here

### 3. AI Website Generation Engine (built on laravel/ai)
- Inertia form submits natural-language prompt to a Laravel controller (standard POST route, no API endpoint)
- Define a laravel/ai **Agent** (e.g. `SiteBuilderAgent`) with instructions for turning a prompt into a website
- Use **structured output** so the agent returns a typed site spec (pages, sections, components) as JSON, not free text
- Structured spec → generated code (HTML/React components) — either a second agent pass or deterministic code generation from the spec
- Rely on laravel/ai's **automatic failover** for provider/model switching — no custom fallback logic needed
- Generation runs as a **queued job** using laravel/ai's queueing support — stream progress into the Inertia page via broadcasting (Laravel Echo/Reverb), not polling

### 4. Build/Compile Pipeline
- Node.js microservice (separate from Laravel) that takes generated code and compiles/bundles it into a deployable site
- Laravel triggers this via a queued job + internal HTTP call (not raw shell exec) — sandbox any code execution
- This is the one place an "API call" exists in the system, and it's internal-only, never exposed to the browser
- Compiled output stored (S3-compatible storage or local disk) and served for live preview

### 5. Live Preview + Editing
- Live preview pane (iframe) rendered on an Inertia page, showing the generated site
- Visual editor: drag/click to edit text, images, layout — changes posted back via a standard Inertia form/controller, writing to the site spec
- Code editor: raw code view/edit for the generated output — changes posted back the same way, re-triggering compile
- Both editors must persist to the same underlying source of truth (no divergence)

### 6. Admin Dashboard
- Inertia pages under an `/admin` route group, protected by role-based middleware
- User management (view/suspend/edit)
- Plan & credit management
- Usage/reporting (generations per user, credit consumption, AI provider costs)
- Role-based access control (admin vs regular user) via Laravel policies/middleware, not a separate permissions API

## Architecture Notes
- Single Laravel app is the source of truth for everything: users, billing, credits, site specs, job orchestration, and page rendering
- No separate frontend app, no CORS, no API versioning to maintain
- laravel/ai handles all provider communication, failover, agent logic, streaming, and queueing for AI calls — don't rebuild any of this manually
- Node.js service = stateless compile worker, called via internal HTTP call, never exposed publicly
- All AI provider API keys live in Laravel's `.env` / `config/ai.php` only — never exposed to frontend (Inertia props are the only thing sent to the browser — audit what you pass as props)
- Every AI/compile job is queued and idempotent — safe to retry on failure
- Use laravel/ai's testing tools (fake agents/responses) in the test suite instead of hitting real providers

## Local .env Setup
- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=` (set per environment)
- `DB_USERNAME=root`
- `DB_PASSWORD=root`

## Build Order (Phases)
1. **Foundation**: `laravel new my-app --using=laravel/react-starter-kit`, DB schema (users, plans, credits, projects, generations)
2. **Billing**: subscription plans + Stripe/Cashier + credit deduction logic
3. **AI generation core**: install `laravel/ai`, configure providers, build the `SiteBuilderAgent` with structured output, prompt → structured spec → generated code
4. **Compile service**: Node.js build worker + Laravel job to invoke it
5. **Preview + editors**: live preview, visual editor, code editor as Inertia pages, all writing to the same spec
6. **Admin dashboard**: user/plan/usage management as Inertia pages under role-based middleware
7. **Hardening**: rate limiting, sandboxing, error handling, logging (without leaking secrets)

## Constraints
- No separate API layer — all data flows through Inertia controller → page props
- No AI provider keys or secrets in frontend code, Inertia props, logs, or version control
- All code-generation/compile execution must be sandboxed — treat generated code as untrusted input
- Credit and billing logic must be server-enforced only

## Acceptance Criteria
- [ ] User can register, subscribe to a plan, and see their credit balance
- [ ] User submits a prompt and gets a working generated website end-to-end
- [ ] Live preview renders the generated site correctly
- [ ] Visual editor changes persist and reflect in preview
- [ ] Code editor changes persist and reflect in preview
- [ ] Credit deducted accurately per generation; cannot go negative
- [ ] Admin can view/manage users, plans, and usage stats
- [ ] No secrets exposed anywhere client-accessible (including Inertia props)
- [ ] No separate REST/JSON API exists in the app — all pages are server-rendered via Inertia

## Notes for opencode
- Build in the phase order above — don't jump to editors before generation core works.
- After each phase, run/verify it works before moving to the next.
- Flag any design decision that trades off security or scalability, don't just silently pick one.
