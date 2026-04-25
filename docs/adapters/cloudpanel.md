# CloudPanel Adapter

**Status:** 📋 Planned — not yet implemented. [Contribute this adapter →](https://github.com/NowSquare/Ricsian/issues)

## Overview

[CloudPanel](https://www.cloudpanel.io/) is a free, modern server management panel built on Nginx. The adapter would use the [CloudPanel CLI](https://www.cloudpanel.io/docs/) or API to manage sites and subdomains.

## Planned Implementation

- **createSubdomain:** Create a PHP site via CloudPanel for the subdomain
- **removeSubdomain:** Delete the site via CloudPanel
- **pauseSubdomain:** Disable the site or redirect to a holding page
- **resumeSubdomain:** Re-enable the site

## Required Configuration

| Field | Description |
|-------|-------------|
| `cloudpanel_hostname` | CloudPanel server hostname |
| `cloudpanel_api_key` | CloudPanel API key (if using API) |

## Contributing

If you're running CloudPanel and would like to implement this adapter:

1. See [Writing an Adapter](writing-an-adapter.md) for the complete guide
2. CloudPanel offers both CLI tools and a REST API
3. [Open an issue](https://github.com/NowSquare/Ricsian/issues) to coordinate before starting
4. Submit a PR with your implementation + this doc updated
