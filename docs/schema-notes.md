# Schema notes

Why the tables in database/schema.sql look the way they do.

## users

people

## accounts

One account per business. plan is what the account may use today; Billing is
the only thing that writes it once Stripe is connected, and superadmin can
set it by hand for an account with no live subscription.

## intakes

What the business told us at signup. Superadmin builds the page from this; it
is kept, never overwritten, so there is always a record of what was asked for.

## pages

The Million Dollar Lead Form page itself. One row per account.

## testimonials

Testimonials the business supplied. Entered by superadmin from what the
business sent, and labelled on the page as provided by the business: they are
the business's claims, not reviews LeadCrazy collected or verified.

## change_requests

A member asking for their page to be changed. Superadmin builds pages, so this
is the member's only way to edit one.

## leads

A lead is stored whatever the plan. is_locked marks one that arrived past the
Free monthly allowance: kept, counted, and shown the moment the account
upgrades, because a lead thrown away is a customer the business never hears of.

## rate_limits

plumbing

## stripe_events

Stripe event ids already handled, so a retried webhook is applied once.
