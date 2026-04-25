# DirectAdmin Adapter

**Status:** 📋 Planned — not yet implemented. [Contribute this adapter →](https://github.com/NowSquare/Ricsian/issues)

## Overview

[DirectAdmin](https://www.directadmin.com/) is a lightweight web hosting control panel popular as an alternative to cPanel. The adapter would use the [DirectAdmin API](https://docs.directadmin.com/developer/api/) to manage subdomains.

## Planned Implementation

- **createSubdomain:** Create a subdomain via DirectAdmin API, set document root
- **removeSubdomain:** Remove the subdomain via API
- **pauseSubdomain:** Redirect subdomain to a holding page
- **resumeSubdomain:** Restore original routing

## Required Configuration

| Field | Description |
|-------|-------------|
| `da_hostname` | DirectAdmin server hostname |
| `da_port` | DirectAdmin port (usually 2222) |
| `da_username` | DirectAdmin admin/reseller username |
| `da_login_key` | DirectAdmin login key or password |

## Contributing

If you're running DirectAdmin and would like to implement this adapter:

1. See [Writing an Adapter](writing-an-adapter.md) for the complete guide
2. The DirectAdmin API uses simple `GET`/`POST` requests with session-based or login key auth
3. [Open an issue](https://github.com/NowSquare/Ricsian/issues) to coordinate before starting
4. Submit a PR with your implementation + tests + this doc updated
