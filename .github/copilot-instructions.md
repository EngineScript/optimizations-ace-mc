---
applyTo: '**'
---

# Optimizations ACE MC - Development Standards

## Environment

- **Work in:** Remote GitHub Codespaces only. Never suggest local terminal commands.
- **WordPress:** 6.8+ minimum
- **PHP:** 8.2+ minimum (use typed properties, readonly, enums, union types, named arguments)
- **WooCommerce:** 5.0+ (guaranteed active - no activation checks needed)
- **WP Store Locator:** Guaranteed active - no activation checks needed
- **Standards:** Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/) for PHP, JS, CSS, HTML, and accessibility.

## Security

All security rules are **mandatory and non-negotiable**.

**Input:** Sanitize all user input - `sanitize_text_field()`, `sanitize_email()`, `wp_kses()`, `absint()`. Validate with `is_email()` and `wp_verify_nonce()`. Use `$wpdb->prepare()` for database queries; never use raw SQL.

**Output:** Escape all dynamic output - `esc_html()`, `esc_attr()`, `esc_url()`, `esc_js()`.

**Authorization:** Check `current_user_can()` before sensitive operations. Use `wp_nonce_field()` / `wp_verify_nonce()` for forms and state-changing requests.

**Prevention:** Guard against SQL injection, XSS, CSRF, LFI, and path traversal. Follow the principle of least privilege. Flag and fix security issues immediately when found.

## Code Standards

- **WordPress APIs only:** Use WP functions instead of raw PHP equivalents. Prefer hooks (`add_action()`, `add_filter()`) over direct calls.
- **PHP 8.2+:** Use typed properties, return type declarations, parameter types, `readonly` where appropriate, null coalescing, and short array syntax.
- **JavaScript ES2025+:** Any project-owned JavaScript should target modern ES2025+ syntax and WordPress 6.5+ browser support. Prefer modules where appropriate, `const`/`let`, arrow functions, optional chaining, nullish coalescing, template literals, and native DOM APIs. Avoid legacy `var`, jQuery-first patterns, IIFEs, and transpilation-only syntax unless a WordPress or WP Store Locator integration requires that specific shape.
- **PHPDoc:** Use `@param`, `@return`, and `@since` tags on all functions and methods.
- **Naming:** Functions: `snake_case`. Classes: `PascalCase_With_Underscores`. Constants: `UPPER_SNAKE_CASE`. Files: `lowercase-with-hyphens.php`.
- **Performance:** Avoid N+1 queries. Use WordPress caching (`wp_cache_*()`, transients). Enqueue assets with `wp_enqueue_*()`. Focus on correctness first, then optimize.
- **Error handling:** Use `WP_Error` for WordPress errors. Log errors without exposing sensitive data. Handle edge cases gracefully.
- **Unused code:** Flag potentially unused code for review before removing it; WordPress hooks can call code dynamically.

## Internationalization

- **Text domain:** `'optimizations-ace-mc'`
- Mark all user-facing strings with `__()`, `_e()`, `esc_html__()`, `esc_attr__()`, and related i18n helpers.
- Update `.pot` language files when adding or modifying translatable strings.

## Documentation & Versioning

**Changelogs:**

- Update both CHANGELOG.md and the readme.txt changelog section for every code change, and keep them in sync.
- Use the "Unreleased" section for ongoing changes.

**Version releases (only when explicitly instructed):**

- Semantic versioning: MAJOR.MINOR.PATCH
- Update version in: plugin header, README.md, readme.txt, CHANGELOG.md, GEMINI.md, `.pot` files, constants, composer.json
- Move "Unreleased" changes to the new version section.
- **Never auto-update versions.**

## CI/CD & Workflows

- GitHub Actions workflows live in `.github/workflows/`.
- **Gemini AI integration:** Code review and issue analysis via Google Gemini API. Sanitize all user-controlled content (diffs, issue bodies) before passing it to LLM prompts.
- **Static analysis:** PHPStan (Level 5+), PHPCS (WordPress standards), PHPMD, Psalm
- **Test matrix:** PHP 8.2, 8.3, 8.4, 8.5 x WordPress 6.8, latest, nightly
- **Security:** Never expose API keys or tokens in logs. Use `${{ secrets.* }}` for credentials. Add timeouts to external API calls. Fail builds on critical security findings.
- **Error handling in workflows:** Do not suppress tool failures with `|| echo`. Log full output and set appropriate exit codes.

## Workflow Rules

- Edit files in place. Create new files only when architecturally necessary.
- Proceed automatically unless an action is destructive or irreversible.
- Auto-identify and fix bugs. Ask for confirmation only before data loss or deletion.
- Provide concise, actionable responses. Never create separate summary `.md` files.
