# MemberPress + n8n Provisioning

Use this integration when WordPress and MemberPress handle the public site, billing, and user accounts while Ricsian provisions workspaces.

## Flow

MemberPress webhook -> n8n -> Ricsian POST /api/provision -> workspace

## Configure Ricsian API Token

On the Ricsian server:

```bash
cd /var/www/Ricsian
php -r "require 'src/bootstrap.php'; Swarm\Models\Setting::set('api_token', bin2hex(random_bytes(32))); echo Swarm\Models\Setting::get('api_token') . PHP_EOL;"
```

Save the printed token in n8n as a credential or workflow secret.

## n8n HTTP Request

Method:

```text
POST
```

URL:

```text
https://app.ricsian.com/api/provision
```

Headers:

```text
Authorization: Bearer YOUR_Ricsian_API_TOKEN
Content-Type: application/json
```

Body:

```json
{
  "source": "memberpress",
  "name": "{{$json.member.display_name || $json.member.email}}",
  "email": "{{$json.member.email}}",
  "slug": "{{$json.member.username}}",
  "memberpress_member_id": "{{$json.member.id}}",
  "memberpress_transaction_id": "{{$json.transaction.id}}",
  "memberpress_subscription_id": "{{$json.subscription.id}}"
}
```

Adjust field paths to match the actual MemberPress webhook payload shown in n8n.

## Ricsian Response

Success returns `202 Accepted`:

```json
{
  "ok": true,
  "existing": false,
  "instance_id": 12,
  "slug": "customer-site",
  "status": "queued",
  "status_url": "https://app.ricsian.com/status/12",
  "workspace_url": "https://customer-site.ricsian.com"
}
```

If the email already has a workspace, the endpoint returns the existing workspace instead of creating a duplicate.

## MemberPress Trigger

Use the MemberPress Developer Tools add-on to send webhooks to n8n. Good starting events are successful transaction/account activation events. Select the exact event name from the MemberPress developer tools screen because MemberPress exposes the available webhook events inside the plugin.
