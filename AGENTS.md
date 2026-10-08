# Agent Guidelines & Rules: e-Cuti Inspektorat

These rules apply to all code generated and modified in this repository.

---

## 1. Security & Integrity Rules (Non-Negotiable)

### Secrets & Credentials
- NEVER hardcode API keys, database credentials, bot tokens, or passwords in source files or blade templates.
- Always load sensitive credentials via `SettingService` or server-side environment variables (`.env`).
- Never prefix secret keys with `VITE_` or expose them in client-side bundles.
- The `.env` file MUST always remain in `.gitignore` and must NEVER be tracked by git.
- `.env.example` must contain placeholder/dummy values only.

### Database & SQL Injection
- NEVER concatenate user input into SQL queries.
- ALWAYS use Eloquent ORM or Query Builder parameterized bindings. Never use unescaped `DB::raw()`, `whereRaw()`, or `orderByRaw()` with raw request input.
- Guard against mass-assignment vulnerabilities: explicitly define `$fillable` on all Eloquent models.

### Authentication & Access Control (Anti-IDOR)
- EVERY route that accesses or modifies personal/leave data MUST be protected with `auth` middleware.
- Admin endpoints MUST enforce `role:admin,super_admin` middleware.
- Every route taking a resource ID (e.g., `/pengajuan/{pengajuan}`) MUST verify ownership or authorization:
  `$pengajuan->pegawai_id === $currentUser->pegawai->id` OR verify authorized role (Atasan Langsung / PyBMC / Admin Kepegawaian).
- Unauthorized requests MUST return HTTP 403 Forbidden or HTTP 401 Unauthorized.

### Input Validation & XSS Prevention
- All user inputs MUST be validated server-side (`$request->validate([...])`).
- In Blade templates, always use `{{ $var }}` (which automatically runs `htmlspecialchars`).
- NEVER use `{!! $var !!}` on user-supplied content.
- Restrict file uploads: validate MIME types (`mimes:pdf,jpg,jpeg,png`), enforce max file size, and store sensitive attachments in private storage (`storage/app/private/cuti_dokumen`), never in the public web root.

### Session & Cookie Security
- Session cookie name MUST be uniquely scoped (`ecuti_insp_session`) to prevent collision with other applications on the shared server.
- Session cookies MUST set `httpOnly: true`, `sameSite: 'lax'`, and `secure: true` when accessed via HTTPS.
- Protect against session fixation: regenerate session ID on login (`$request->session()->regenerate()`) and invalidate on logout.

### HTTP Headers & Anti-Cache
- Sensitive authenticated responses MUST send `Cache-Control: no-cache, no-store, max-age=0, must-revalidate` and `Pragma: no-cache` to prevent sensitive leave/medical data from being cached on shared office computers.
- Send security headers: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`.

---

<!-- antislop:start -->
## antislop
For UI, copy, people, mobile layout, or code comments work, read `antislop.md` (core) and then the skill for the task:
- UI / visual: `skills/antislop-ui/SKILL.md`
- Copy & text: `skills/antislop-copywriting/SKILL.md`
- People: `skills/antislop-human/SKILL.md`
- Mobile / responsive: `skills/antislop-layoutmobile/SKILL.md`
- Code comments: `skills/antislop-code/SKILL.md`

Always refer to `DESIGN.md` in the workspace root for visual direction and product identity.

Before starting, follow the core's "Two Usage Modes" section in strict order: explicit session instruction first, then global preference, then ask. A session instruction always wins. For a resolved mode, say `antislop active: <mode> (session override).` or `antislop active: <mode> (global preference).` once before presenting findings or making edits, using the actual mode and source. Acknowledging the user's request without naming the source does not replace this notice.
Only an explicit choice of antislop during or after selects a session mode. A request to review, audit, or avoid file edits does not select a mode; read the global preference in that case. Another skill's mode does not select antislop's mode.
If the mode is unresolved, ask during/after and end the response; wait for the answer before any UI review, planning, or concept. For read-only tasks, put the active-mode notice only at the start of the final answer, never in progress messages. For editing tasks, announce before the first edit and omit it from the final answer.
To update antislop later: download `antislop.md` again, or run `npx antislop-ai --update` if it was installed as skill folders.
<!-- antislop:end -->
