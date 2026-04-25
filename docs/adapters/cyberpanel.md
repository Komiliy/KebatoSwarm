# CyberPanel Adapter

**Status:** 📋 Planned — not yet implemented. [Contribute this adapter →](https://github.com/NowSquare/Ricsian/issues)

## Overview

[CyberPanel](https://cyberpanel.net/) is a web hosting panel powered by OpenLiteSpeed (or LiteSpeed Enterprise). The adapter would use the [CyberPanel API](https://cyberpanel.net/docs/api-guide/) to manage websites and subdomains.

## Planned Implementation

- **createSubdomain:** Create a child domain via CyberPanel API
- **removeSubdomain:** Delete the child domain
- **pauseSubdomain:** Suspend the website
- **resumeSubdomain:** Unsuspend the website

## Required Configuration

| Field | Description |
|-------|-------------|
| `cyberpanel_hostname` | CyberPanel server hostname |
| `cyberpanel_port` | CyberPanel port (usually 8090) |
| `cyberpanel_username` | CyberPanel admin username |
| `cyberpanel_password` | CyberPanel admin password |

## Notes

CyberPanel uses OpenLiteSpeed by default. VoxelSite is compatible with LiteSpeed/OpenLiteSpeed, but adapter-level configuration may differ from Nginx/Apache setups. The `.htaccess` file included with VoxelSite is supported by LiteSpeed.

## Contributing

If you're running CyberPanel and would like to implement this adapter:

1. See [Writing an Adapter](writing-an-adapter.md) for the complete guide
2. CyberPanel provides a REST API at port 8090
3. [Open an issue](https://github.com/NowSquare/Ricsian/issues) to coordinate before starting
4. Submit a PR with your implementation + this doc updated
