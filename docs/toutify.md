# Toutify sign-in (members)

Members sign in through Toutify, LeadCrazy's parent site, with OpenID Connect.
Superadmins keep their LeadCrazy password login at `/superadmin/login`.

## Settings

Set as environment variables, or in the `toutify` block of `app/config.php`
(environment wins). Never in the code.

| Setting | Value |
|---|---|
| `TOUTIFY_ISSUER` | `https://toutify.com` |
| `TOUTIFY_CLIENT_ID` | `leadcrazy` (exactly as registered on Toutify; capitals matter) |
| `TOUTIFY_CLIENT_SECRET` | client secret from Toutify |
| `TOUTIFY_WEBHOOK_SECRET` | webhook secret from Toutify |

`app_url` in `app/config.php` must be exactly the address registered on
Toutify, e.g. `https://leadcrazy.com` (no trailing slash).

## Addresses to register on Toutify

| What | URL |
|---|---|
| Redirect URI | `https://leadcrazy.com/auth/toutify/callback` |
| Post-logout redirect | `https://leadcrazy.com/` |
| Initiate login | `https://leadcrazy.com/auth/toutify/initiate` |
| Back-channel logout | `https://leadcrazy.com/auth/toutify/backchannel-logout` |
| Webhook | `https://leadcrazy.com/auth/toutify/webhook` |

## How it fits together

- `users.toutify_sub` links a LeadCrazy login to its Toutify member. Members
  imported from LeadCrazy are linked on their first Toutify sign-in, by email,
  only when Toutify reports the email as verified, never for a staff login.
- Every member session has a `toutify_sessions` row; deleting it (back-channel
  logout, suspension, deletion) signs the member out on their next request.
- `toutify_events` deduplicates webhook deliveries; rows older than 30 days are
  removed.
- The tables and columns are added automatically on the first request after
  upload (`App\Support\Schema`).
- Sessions are stored in `storage/sessions` (two-hour idle lifetime);
  discovery and keys are cached in `storage/cache`.
