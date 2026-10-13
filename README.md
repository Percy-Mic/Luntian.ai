# Luntian AI — deployment guide

Stack: **PHP** + **Firebase Auth** (Google/Facebook) + **Gemini API** + **PostgreSQL (Aiven)** + **manual GCash** payments.

All paid third-party APIs are optional except free tiers: Firebase Spark, Gemini free quota, Aiven trial/free, Vercel Hobby, GitHub free.

---

## 1. Local setup (XAMPP)

### Requirements
- PHP 8.1+ with extensions: `pdo_pgsql`, `curl`, `json`, `mbstring`, `fileinfo`
- Composer
- PostgreSQL (Aiven connection string works from local)

```bash
cd c:\xampp\htdocs\luntianAi
composer install
copy .env.example .env
```

Edit `.env` (see section 3). **Never commit `.env` or API keys to GitHub.**

### Database

In [Aiven Console](https://console.aiven.io/), create a PostgreSQL service. Copy the connection URI into `DATABASE_URL`.

Run schema:

```bash
psql "YOUR_DATABASE_URL" -f database/schema.sql
```

Or paste `database/schema.sql` into Aiven’s web SQL console.

### Apache

Enable `pdo_pgsql` in `php.ini`, restart Apache.

Open: `http://localhost/luntianAi/api/health.php` — `database` should be `true`.

---

## 2. Firebase (Google + Facebook login)

### Firebase Console (`luntian-ai-pro`)
1. **Authentication → Sign-in method**: enable Email/Password, Google, Facebook.
2. **Project settings → Your apps → Web**: copy config into `.env` (`FIREBASE_*` keys).
3. Server verifies tokens via Firebase Identity Toolkit (uses `FIREBASE_API_KEY` only — no service account required for this setup).

### Facebook (Meta Developer)
1. [developers.facebook.com](https://developers.facebook.com/) → your app → **Facebook Login → Settings**.
2. Valid OAuth redirect URIs:
   - `https://luntian-ai-pro.firebaseapp.com/__/auth/handler`
   - `http://localhost` (for local testing)
3. Copy **App ID** → `FIREBASE_FACEBOOK_APP_ID` in `.env`.
4. In Firebase → Facebook provider, paste App ID and **App Secret**.

### Authorized domains (Firebase → Authentication → Settings)
Add:
- `localhost`
- Your Vercel domain (e.g. `luntian-ai.vercel.app`)

---

## 3. Environment variables (`.env`)

| Variable | Purpose |
|----------|---------|
| `APP_URL` | Full site URL, no trailing slash |
| `DATABASE_URL` | Aiven PostgreSQL URI (`?sslmode=require`) |
| `GEMINI_API_KEY` | [Google AI Studio](https://aistudio.google.com/apikey) — free tier |
| `GEMINI_MODEL` | Optional Gemini model name; defaults to `gemini-2.0-flash` |
| `FIREBASE_*` | Web app config from Firebase |
| `FIREBASE_FACEBOOK_APP_ID` | Meta app ID (optional, for reference) |
| `GCASH_*` | Display name, mobile, PHP prices |
| `ADMIN_SECRET` | Protects `/api/admin/payments.php` and `/admin/payments.php` |

**Security:** Rotate any API key that was shared in chat or committed by mistake. Use a long, random `ADMIN_SECRET`; do not put it in browser code.

---

## 4. GCash (manual — no API)

1. Save your QR as: `public/assets/images/gcash-qr.png`
2. Set `GCASH_ACCOUNT_NAME`, `GCASH_MOBILE`, prices in `.env`
3. User flow: **Subscription → Pay with GCash → pay → upload proof**
4. Admin verifies and approves at `/admin/payments.php`.

Payment proof files are validated as JPG, PNG, or WebP, with a 5 MB limit. For production, store proofs in private object storage rather than a public web directory.

---

## 5. Chat API and persistence

All chat endpoints require a valid Firebase bearer token.

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/chat.php` | POST | Generate a Gemini response and persist the user/assistant messages |
| `/api/chats.php` | GET | List the signed-in user's recent conversations |
| `/api/chats.php?id=123` | GET | Load one conversation and its messages |
| `/api/chats.php` | DELETE | Delete one conversation using JSON `{"sessionId":123}` |
| `/api/health.php` | GET | Check application/database availability |
| `/api/auth/config.php` | GET | Public Firebase web configuration |
| `/api/auth/session.php` | POST | Sync a verified Firebase identity |
| `/api/auth/me.php` | GET | Return the signed-in user's account and plan |
| `/api/payments/create.php` | POST | Create a pending subscription payment |
| `/api/payments/submit.php` | POST | Submit GCash reference and proof image |
| `/api/payments/status.php?ref=...` | GET | Read payment status for the current user |
| `/api/admin/payments.php` | GET/POST | Admin-only payment review using `X-Admin-Secret` |

Gemini receives the latest 20 stored messages as conversation context. The browser does not store a separate mock conversation history; the database is the source of truth. Each conversation is scoped to its owning user on the server.

---

## 6. Vercel

PHP on Vercel uses the community `vercel-php` builder (`vercel.json` included).

1. Import GitHub repo at [vercel.com](https://vercel.com)
2. **Settings → Environment Variables**: add every required key from `.env.example`
3. Deploy

**Note:** Vercel functions do not provide durable local storage for uploads. Store payment proofs in private object storage in production, or deploy the PHP application to a suitable persistent runtime.

Set `APP_URL` to your production URL after deployment. Verify the health endpoint, Firebase login, database connection, Gemini responses, and payment submission in the deployed environment.

---

## 7. Aiven PostgreSQL

1. Create a PostgreSQL service.
2. Set `DATABASE_URL` to the connection string.
3. Run `database/schema.sql`.
4. Restrict inbound connections to the application where possible.

---

## 8. Continuous integration

GitHub Actions runs PHP syntax checks for application files on pushes and pull requests. This catches parse errors, but it does not replace integration tests against configured Firebase, PostgreSQL, Gemini, and payment credentials.

---

## 9. Troubleshooting

- **401 on chat:** Sign in again; Firebase tokens expire and are refreshed by the auth SDK.
- **Firebase popup blocked:** Allow popups for your domain.
- **Database false on health:** Check `pdo_pgsql`, `DATABASE_URL`, and SSL mode.
- **Gemini 429:** The provider quota/rate limit may have been reached; retry later or review your API quota.
- **Facebook login fails:** Check OAuth redirect URIs and Firebase Facebook App Secret.
- **History doesn't load:** Confirm the database schema is applied and that the signed-in account has a valid bearer token.

---

© 2026 Luntian AI
