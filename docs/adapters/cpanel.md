# cPanel / WHM Adapter

**Status:** 🧪 Testing — we need community testing across different cPanel versions and hosting setups.

The cPanel adapter uses the [WHM API](https://api.docs.cpanel.net/) to create subdomains and manage hosting accounts.

## How It Works

- **createSubdomain:** Creates a subdomain under a cPanel account via WHM API and sets the document root
- **removeSubdomain:** Removes the subdomain via WHM API
- **pauseSubdomain / resumeSubdomain:** Currently log warnings only. The adapter does not yet switch the site into a maintenance page.

SSL handling depends on your cPanel setup. Ricsian does not trigger AutoSSL directly in the current adapter.

## Prerequisites

- cPanel/WHM server (cPanel version 11.68+)
- WHM root or reseller access with subdomain management privileges
- WHM API token (not a cPanel token — WHM has broader permissions)
- Wildcard DNS: `*.yourdomain.com` → your server IP

⚠️ **Important:** You need a WHM API token, not a cPanel API token. WHM tokens have the privilege level needed to manage subdomains across accounts.

## Configuration

| Field | Description | Example |
|-------|-------------|---------|
| `hostname` | WHM base URL. `https://server.example.com` and `https://server.example.com:2087` are both accepted. | `https://server.example.com:2087` |
| `api_token` | WHM API token | `ABCDEF123456...` |

### Getting a WHM API Token

1. Log in to WHM (usually `https://yourdomain.com:2087`)
2. Go to Development → Manage API Tokens
3. Create a new token
4. Copy the token

## Troubleshooting

- **"Access denied" errors:** Ensure you're using a WHM token, not a cPanel token
- **AutoSSL not issuing certificates:** Check WHM → SSL/TLS → Manage AutoSSL. Ensure all providers are enabled
- **Subdomain not resolving:** Verify wildcard DNS points to the cPanel server
- **504 Gateway Timeout:** WHM API can be slow on shared hosting — health check retries should handle this

## Help Us Test

If you're running cPanel, please test Ricsian and [report any issues](https://github.com/NowSquare/Ricsian/issues) with:
- Your cPanel version
- Hosting type (dedicated, VPS, shared, reseller)
- Any error messages from `storage/logs/adapter-YYYY-MM-DD.log`

See [../testing-feedback.md](../testing-feedback.md) for the full reporting checklist and the reasoning behind the logging model.
