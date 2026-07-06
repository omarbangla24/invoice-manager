# Cloudflare Free Inbound Email Setup

This Worker receives Cloudflare Email Routing messages, parses attachments, and posts them to Laravel:

```text
Cloudflare Email Routing -> Worker email() -> Laravel /inbound/email -> invoice/admin panel
```

## Requirements

- Domain DNS managed by Cloudflare.
- Laravel app deployed publicly over HTTPS. Cloudflare cannot call `127.0.0.1`.
- Same token in Laravel Admin > Settings > Inbound webhook token and Worker secret `INBOUND_EMAIL_TOKEN`.

## Install

```bash
cd cloudflare-email-worker
npm install
```

## Configure

Edit `wrangler.toml`:

```toml
LARAVEL_INBOUND_URL = "https://your-domain.com/inbound/email"
```

Set secret:

```bash
npx wrangler secret put INBOUND_EMAIL_TOKEN
```

Optional forwarding copy:

```toml
FORWARD_TO_EMAIL = "accountant@gmail.com"
```

That address must be verified in Cloudflare Email Routing destination addresses.

## Deploy

```bash
npx wrangler deploy
```

## Cloudflare Dashboard

1. Go to Cloudflare Dashboard.
2. Open your domain.
3. Go to Compute & AI > Email Service > Email Routing.
4. Onboard domain. Cloudflare adds MX/SPF/DKIM records.
5. Create Routing Rule.
6. Email pattern: `invoices`.
7. Action: Send to Worker.
8. Worker: `invoice-portal-email-worker`.
9. Save.

Now emails sent to `invoices@your-domain.com` create invoice records.

Matched sender/client goes straight to `/admin/invoices`.
Unmatched sender goes to `/admin/unmatched-emails` for manual transfer.
