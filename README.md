# LeadCrazy — The Million Dollar Lead Form

A lead page and quote form for local service businesses, in the same plain-PHP,
no-Composer style as PromoMonster.

| Plan | Price | What they get |
|---|---|---|
| **Free** | $0 | Page at `leadcrazy.com/{business}` with the lead form, services, 1 special offer, up to 5 cities and 5 zip codes. 10 leads a month (extra leads are saved, locked until upgrade). LeadCrazy branding. |
| **Pro** | $19/mo | Full page: offers, gallery, testimonials, zip codes, stats, video. Unlimited leads, no branding, **listed on MonsterList** (via the JSON feed). |
| **Premium** | $49/mo | Everything in Pro, plus **embed code** for the member's own website: full page or form only. |

## How it works

**Self-serve, live in minutes:** `/how-it-works` is a six-step builder (trade, business, services,
service area, offer, publish) with a live preview. Publishing creates a Free account and puts the page
live immediately; staff can polish it later in superadmin.

**Done for you:**

1. A business signs up at `/members/signup` and picks a plan.
2. They fill in the **intake** (services, cities, zip codes, about, offers, testimonials, logo and photos).
   Paid plans go to Stripe Checkout right after.
3. **Superadmin builds the page** at `/superadmin/account?id=…` (content, photos, offers, reviews,
   lead-form options), previews it, and sets the status to **Live**.
4. Leads land in the member's dashboard (`/members/leads`): status tracking, notes, CSV export.
   Members ask for page changes from `/members/page`; requests queue in `/superadmin/requests`.

## Public URLs

| URL | What |
|---|---|
| `/{slug}` | The business page (live pages only) |
| `POST /lead/{slug}` | Lead submission |
| `/widget/{slug}.js` | Premium embed script: `<script src="…/widget/{slug}.js" data-view="page\|form" async></script>` |
| `/embed/{slug}?view=page\|form` | What the widget iframes (Premium only, frameable) |
| `/feed/monsterlist?key=…` | JSON feed of live Pro + Premium pages for MonsterList |
| `POST /webhooks/stripe` | Stripe subscription events |

## Spam protection on the lead form

No cookies on public pages. The form uses a signed timing token (rejects bots that post in under
3 seconds and forged or expired forms), a hidden honeypot field, per-IP rate limits, and
**Cloudflare Turnstile** when keys are set in config.

## Install (shared hosting, same as PromoMonster)

1. Upload the project. Point the document root at `public/` if you can; otherwise the root
   `.htaccess` maps everything into `public/`.
2. Create an empty MySQL database.
3. Copy `app/config.example.php` to `app/config.php` and fill in: `app_url`, `app_key`,
   database, `support_email`, Turnstile keys, Stripe keys and Price ids, and the MonsterList feed key.
4. Make `public/uploads/` and `storage/logs/` writable.
5. Visit **`/install`**, enter your `app_key`, and it creates the tables and your superadmin login.
   (Alternatives: import `database/schema.sql` with phpMyAdmin's Import tab, and
   `php database/make-admin.php you@leadcrazy.com` from a shell.)

### Stripe

Create two monthly Prices ($19 Pro, $49 Premium), paste their `price_…` ids into config, add a
webhook endpoint `https://leadcrazy.com/webhooks/stripe` for `checkout.session.completed` and
`customer.subscription.*`, and turn on the Customer Portal. Without Stripe keys, paid plan choices
are recorded as requests and superadmin sets the plan by hand.

### Not in this version

- **Email.** No lead-notification or password-reset emails (leads go to the dashboard only).
  A forgotten password is handled by superadmin's **Temporary password** button.
- **MonsterList side.** MonsterList needs to import `/feed/monsterlist`; that change lives in the
  MonsterList codebase.
- Privacy and terms pages are placeholders for legal review.

## Local development

```
php -S 127.0.0.1:8080 -t public public/index.php
```
