<?php

declare(strict_types=1);

namespace Swarm\Services;

use Swarm\Logger;
use Swarm\Helpers\Url;
use Swarm\Models\Setting;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Mailer — PHPMailer wrapper for transactional emails.
 */
class Mailer
{
    /**
     * Send the welcome email to a new tenant.
     */
    public static function sendWelcome(array $instance): void
    {
        $url = Url::workspace($instance, '/_studio/');

        self::send(
            to:      $instance['email'],
            subject: "Your workspace is ready — {$instance['name']}",
            body:    self::welcomeHtml($instance['name'], $url),
            altBody: "Your workspace is ready! Visit: {$url}"
        );

        Logger::info('mail', 'Welcome email sent', ['email' => $instance['email'], 'slug' => $instance['slug']]);
    }

    /**
     * Send a provision failure notification to the operator.
     */
    public static function sendProvisionFailed(array $instance, string $error): void
    {
        $operatorEmail = Setting::get('operator_email');
        if (!$operatorEmail) {
            return;
        }

        self::send(
            to:      $operatorEmail,
            subject: "Provisioning failed: {$instance['slug']}",
            body:    "<h2>Provisioning Failed</h2><p>Instance: <strong>{$instance['slug']}</strong></p><p>Email: {$instance['email']}</p><p>Error: {$error}</p>",
            altBody: "Provisioning failed for {$instance['slug']}: {$error}"
        );

        Logger::info('mail', 'Provision failure notification sent', ['slug' => $instance['slug']]);
    }

    /**
     * Send a test email.
     */
    public static function sendTest(string $to): bool
    {
        try {
            self::send(
                to:      $to,
                subject: 'Ricsian — Test Email',
                body:    '<h2>It works.</h2><p>Your email configuration is correct.</p>',
                altBody: 'Ricsian test email — your configuration is correct.'
            );
            return true;
        } catch (\Throwable $e) {
            Logger::error('mail', 'Test email failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send an email via PHPMailer.
     */
    private static function send(string $to, string $subject, string $body, string $altBody = ''): void
    {
        $driver = Setting::get('mail_driver', 'log');

        if ($driver === 'null') {
            return;
        }

        if ($driver === 'log') {
            Logger::info('mail', "Email (log driver): To={$to} Subject={$subject}");
            return;
        }

        $config = Setting::getJson('mail_config', []);

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $config['host']        ?? 'localhost';
        $mail->Port       = (int) ($config['port']  ?? 587);
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username']     ?? '';
        $mail->Password   = $config['password'] ?? $config['smtp_password'] ?? '';
        $mail->SMTPSecure = $config['encryption']   ?? PHPMailer::ENCRYPTION_STARTTLS;

        $fromEmail = $config['from_address'] ?? Setting::get('operator_email', 'noreply@example.com');
        $fromName  = $config['from_name']    ?? 'Ricsian';
        $mail->setFrom($fromEmail, $fromName);

        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body    = $body;
        $mail->AltBody = $altBody;

        $mail->send();
    }

    private static function welcomeHtml(string $name, string $url): string
    {
        $escapedName = htmlspecialchars($name);
        $escapedUrl  = htmlspecialchars($url);

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family:Inter,system-ui,sans-serif;margin:0;padding:40px;background:#FAFAFA;color:#09090B;">
  <div style="max-width:520px;margin:0 auto;background:#FFF;border:1px solid #E4E4E7;border-radius:12px;padding:40px;">
    <h1 style="font-size:22px;font-weight:700;margin:0 0 16px;letter-spacing:-0.025em;">Your workspace is ready.</h1>
    <p style="color:#52525B;line-height:1.6;margin:0 0 24px;">
      Hi there — <strong>{$escapedName}</strong> is live and waiting for you.
      Visit your Studio to set up your AI provider, create your admin account, and start building.
    </p>
    <a href="{$escapedUrl}" style="display:inline-block;background:#EA580C;color:#FFF;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;font-size:15px;">
      Visit your workspace →
    </a>
    <p style="color:#A1A1AA;font-size:13px;margin:24px 0 0;">
      This is your direct link. Bookmark it. No password needed to access the installer.
    </p>
  </div>
</body>
</html>
HTML;
    }
}
