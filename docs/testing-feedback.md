# Testing And Feedback

Ricsian is still early-stage software. It needs real-world testing across hosting providers, control panels, PHP setups, DNS configurations, and server permission models.

That means two things are true at the same time:

- You should expect to find edge cases and environment-specific failures.
- Those failures are only fixable if the report includes enough information.

This page explains how testing is supposed to work, why Ricsian includes extended logging, and what to send when something breaks.

## Why We Need Testing

Ricsian provisions and manages full VoxelSite instances across environments that behave differently:

- raw VPS setups
- local filesystem deployments
- Nginx hosts
- Laravel Forge
- cPanel / WHM
- Plesk

Even when the provisioning flow is correct in one environment, another environment may differ in:

- filesystem permissions
- DNS timing
- SSL behavior
- API responses
- control panel quirks
- PHP process user permissions

So the project improves in a loop:

1. You test on a real environment.
2. Ricsian logs what happened.
3. You send the logs and environment details.
4. We investigate and fix the issue when there is enough information.

## What To Expect

If you are testing Ricsian today, assume:

- some environments will work immediately
- some environments will need small fixes
- some adapters will expose partial behavior before every edge case is handled
- logs are part of the normal testing workflow, not a last resort

This does not mean the app is unreliable everywhere. It means the project is still collecting enough coverage across real environments to make those paths dependable.

## Why Logging Is Built In

Ricsian writes detailed plaintext logs for the exact reason above: when something fails, the log trail usually shows where and why.

The main channels are:

- `provision-YYYY-MM-DD.log`
- `adapter-YYYY-MM-DD.log`
- `mail-YYYY-MM-DD.log`
- `swarm-YYYY-MM-DD.log`

These logs are designed to answer questions like:

- Which provisioning step failed?
- Did the adapter call succeed or fail?
- Was the health check blocked by DNS or SSL?
- Did the email driver run?
- Did the operator action complete or error?

Without logs, many bug reports are guesswork. With logs, they are usually actionable.

## Where To Find Logs

Logs live in:

```text
storage/logs/
```

You can access them in several ways:

- Dashboard: `/operator/system` -> `Server Logs`
- SSH / terminal: inspect files directly under `storage/logs/`
- Download from the System page when log files exist

For a deeper breakdown of each log channel, see [troubleshooting.md](troubleshooting.md#log-files).

## What To Send When Something Breaks

Open an issue and include all of the following when possible.

### 1. Environment Details

Include:

- Ricsian version
- PHP version
- operating system
- web server
- control panel, if any
- adapter in use

Example:

```text
Ricsian version: 0.3.0
PHP version: 8.5.2
OS: Ubuntu 24.04
Web server: Nginx
Control panel: None
Adapter: nginx
```

### 2. Exact Steps

Say what you did in order, not just the final symptom.

Good:

```text
1. Installed Ricsian
2. Set adapter to cPanel
3. Tested the adapter successfully
4. Processed a VoxelSite ZIP
5. Created an instance from the dashboard
6. Provisioning failed during create_subdomain
```

Weak:

```text
Provisioning broken
```

### 3. What You Expected vs What Happened

State both clearly:

- expected result
- actual result

Example:

```text
Expected: the new instance would become active and open at the generated subdomain.
Actual: the instance stayed in provisioning, then failed after the health_check step.
```

### 4. Relevant Logs

Attach or paste the relevant lines from `storage/logs/`.

Usually:

- provisioning issue -> `provision` and `adapter`
- domain / routing issue -> `adapter`
- email issue -> `mail`
- settings / operator action issue -> `swarm`

If you are not sure which one matters, include the last 50 lines from all logs for the day the issue happened:

```bash
tail -50 storage/logs/*-$(date +%Y-%m-%d).log
```

### 5. Screenshots

Optional, but useful for:

- dashboard error states
- empty states that feel wrong
- modals or controls behaving unexpectedly
- public landing / signup / status flow

## What Makes A Report Actionable

Actionable reports usually contain:

- exact environment
- exact steps
- the failing step or visible symptom
- matching log lines
- a screenshot when the UI is involved

Reports are hard to act on when they contain only:

- “it does not work”
- no adapter name
- no environment details
- no logs
- no failing step

## Sensitive Data

Before sharing logs publicly, review them for:

- hostnames
- email addresses
- internal URLs

The logs should not contain passwords or API tokens, but you should still review them before posting.

If a report includes sensitive operational details, mention that in the issue and share only the minimum needed publicly.

If you are a VoxelSite customer and would rather not post logs in a public GitHub issue, you can send them more privately through VoxelSite support:

[VoxelSite support](https://codecanyon.net/item/voxelsite-ai-website-generator-selfhosted-own-your-files/62090509/support)

## Where To Report

Open an issue on GitHub:

[github.com/NowSquare/Ricsian/issues](https://github.com/NowSquare/Ricsian/issues)

Alternative:

- If you are a VoxelSite customer and prefer not to share logs in public, use [VoxelSite support](https://codecanyon.net/item/voxelsite-ai-website-generator-selfhosted-own-your-files/62090509/support).

If the issue turns out to be inside VoxelSite itself rather than Ricsian's provisioning/deployment layer, use VoxelSite support instead.

## In Short

The testing model for Ricsian is:

- early-stage software
- real-world testing needed
- errors are expected in some environments
- logs are built in so failures are diagnosable
- fixes depend on clear reports with enough detail

If you test and send good logs, you are directly helping make the project usable in more environments.
