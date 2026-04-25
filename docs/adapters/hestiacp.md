# HestiaCP Adapter

**Status:** 📋 Planned — not yet implemented. [Contribute this adapter →](https://github.com/NowSquare/Ricsian/issues)

## Overview

[HestiaCP](https://hestiacp.com/) is a free, open-source web hosting control panel (fork of VestaCP). The adapter would use the [HestiaCP API](https://docs.hestiacp.com/server_administration/rest_api.html) to manage web domains and subdomains.

## Planned Implementation

- **createSubdomain:** Create a web domain or subdomain via HestiaCP CLI/API
- **removeSubdomain:** Remove the domain via API
- **pauseSubdomain:** Suspend the domain
- **resumeSubdomain:** Unsuspend the domain

## Required Configuration

| Field | Description |
|-------|-------------|
| `hestia_hostname` | HestiaCP server hostname |
| `hestia_port` | HestiaCP port (usually 8083) |
| `hestia_username` | HestiaCP admin username |
| `hestia_password` | HestiaCP admin password or API key |

## Contributing

If you're running HestiaCP and would like to implement this adapter:

1. See [Writing an Adapter](writing-an-adapter.md) for the complete guide
2. HestiaCP provides both a CLI (`v-add-web-domain`) and REST API
3. [Open an issue](https://github.com/NowSquare/Ricsian/issues) to coordinate before starting
4. Submit a PR with your implementation + this doc updated
