# Invoice Portal

Production-oriented Laravel app for collecting business owner invoices and expense receipts for accountants.

## Features

- Admin/accountant dashboard with client, invoice, pending, done, and declined counts.
- Client profile management with separate login, webmail address, and storage folder.
- Client portal upload for images, PDFs, spreadsheets, documents, CSV, and text files.
- Client-isolated storage paths: `clients/{client-folder}/{year}/{month}/original`.
- Best-effort image compression into a parallel `compressed` folder when PHP GD is available.
- Invoice statuses: Pending, Done / Counted, Declined / Returned.
- Admin comments/replies visible to clients.
- Token-protected inbound email webhook that accepts multipart `attachments[]` and creates invoices for matched clients.
- Feature tests for upload, access isolation, admin review, and inbound email ingestion.
- Queue-backed file optimization jobs.
- Deep PDF compression through Ghostscript when installed.
- Browser preview for PDFs and images.
- Admin client edit/delete, audit logs, and in-app notifications.
- Local/S3/R2 storage selector in Admin Settings.

## Demo Logins

After `php artisan migrate:fresh --seed`:

- Admin: `admin@example.com` / `password`
- Client: `client@example.com` / `password`

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve --host=127.0.0.1 --port=8000
php artisan queue:work
```

Open `http://127.0.0.1:8000`.

## Email Ingestion

Set a strong shared secret:

```env
INBOUND_EMAIL_TOKEN=replace-with-a-long-random-value
```

Webhook endpoint:

```http
POST /inbound/email
Authorization: Bearer {INBOUND_EMAIL_TOKEN}
Content-Type: multipart/form-data
```

Supported fields:

- `from_email` required
- `to_email` optional
- `subject` optional
- `attachments[]` optional files
- `provider`, `message_id`, `metadata` optional

Client matching happens by sender email or by the client's configured `webmail_address`.

## Free Cloudflare Inbound Email

Cloudflare Email Routing can receive `invoices@your-domain.com` for free when your domain uses Cloudflare DNS.

Worker adapter is included:

```text
cloudflare-email-worker/
```

Setup summary:

```bash
cd cloudflare-email-worker
npm install
npx wrangler secret put INBOUND_EMAIL_TOKEN
npx wrangler deploy
```

Then in Cloudflare Dashboard:

- Compute & AI > Email Service > Email Routing
- Onboard domain
- Create routing rule for `invoices@your-domain.com`
- Action: Send to Worker
- Worker: `invoice-portal-email-worker`

Set Laravel Admin > Settings > Inbound webhook token to same secret.

Matched emails create invoices. Unmatched emails appear under Admin > Unmatched Email.

## Production Notes

- Use MySQL/PostgreSQL for production instead of SQLite.
- Set `APP_ENV=production`, `APP_DEBUG=false`, and a real `APP_URL`.
- Use S3-compatible storage such as AWS S3, Wasabi, or Cloudflare R2 by changing `FILESYSTEM_DISK`.
- For S3/R2 storage, install the Laravel S3 adapter:

```bash
composer require league/flysystem-aws-s3-v3
```

- Run queues with Supervisor if PDF/image optimization is expanded into background jobs.
- Install Ghostscript for deep PDF compression:

```bash
gs --version
```

- Put the app behind HTTPS and set secure session cookie options.
- Connect Mailgun, SendGrid Inbound Parse, Postmark, or Cloudflare Email Routing to `/inbound/email`.
- Install Ghostscript or a queue-based PDF optimizer if deep PDF compression is required.

## Verification

```bash
php artisan test
```
# invoice-manager
