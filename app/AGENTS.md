# Security Rules for e-Cuti Inspektorat

These rules apply to all code generated and modified in this project, adapted from the [benavlabs/vibe-check](https://github.com/benavlabs/vibe-check) security standards. They are non-negotiable.

## 1. Secrets & Credentials
- NEVER hardcode API keys, database credentials, bot tokens, or passwords in source files or blade templates.
- Always load sensitive credentials via `SettingService` or server-side environment variables (`.env`).
- Never prefix secret keys with `VITE_` or expose them in client-side bundles.
- The `.env` file MUST always remain in `.gitignore` and must NEVER be tracked by git.
- `.env.example` must contain placeholder/dummy values only.

## 2. Database & SQL Injection
- NEVER concatenate user input into SQL queries.
- ALWAYS use Eloquent ORM or Query Builder parameterized bindings. Never use unescaped `DB::raw()`, `whereRaw()`, or `orderByRaw()` with raw request input.
- Guard against mass-assignment vulnerabilities: explicitly define `$fillable` on all Eloquent models.

## 3. Authentication & Access Control (Anti-IDOR)
- EVERY route that accesses or modifies personal/leave data MUST be protected with `auth` middleware.
- Admin endpoints MUST enforce `role:admin,super_admin` middleware.
- Every route taking a resource ID (e.g., `/pengajuan/{pengajuan}`) MUST verify ownership or authorization:
  `$pengajuan->pegawai_id === $currentUser->pegawai->id` OR verify authorized role (Atasan Langsung / PyBMC / Admin Kepegawaian).
- Unauthorized requests MUST return HTTP 403 Forbidden or HTTP 401 Unauthorized.

## 4. Input Validation & XSS Prevention
- All user inputs MUST be validated server-side (`$request->validate([...])`).
- In Blade templates, always use `{{ $var }}` (which automatically runs `htmlspecialchars`).
- NEVER use `{!! $var !!}` on user-supplied content.
- Restrict file uploads: validate MIME types (`mimes:pdf,jpg,jpeg,png`), enforce max file size, and store sensitive attachments in private storage (`storage/app/private/cuti_dokumen`), never in the public web root.

## 5. Session & Cookie Security
- Session cookie name MUST be uniquely scoped (`ecuti_insp_session`) to prevent collision with other applications on the shared server.
- Session cookies MUST set `httpOnly: true`, `sameSite: 'lax'`, and `secure: true` when accessed via HTTPS.
- Protect against session fixation: regenerate session ID on login (`$request->session()->regenerate()`) and invalidate on logout.

## 6. HTTP Headers & Anti-Cache
- Sensitive authenticated responses MUST send `Cache-Control: no-cache, no-store, max-age=0, must-revalidate` and `Pragma: no-cache` to prevent sensitive leave/medical data from being cached on shared office computers.
- Send security headers: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`.

## 7. Server & Infrastructure Hardening
- Deny direct web access to sensitive dotfiles (`.env`, `.git`), configs (`composer.json`), logs, and database dumps via `.htaccess`.
- Disable script/PHP execution in all storage and upload directories.
