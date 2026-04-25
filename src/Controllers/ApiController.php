<?php

declare(strict_types=1);

namespace Swarm\Controllers;

use Swarm\Helpers\Response;
use Swarm\Models\Instance;
use Swarm\Models\Setting;
use Swarm\Services\Provisioner;
use Swarm\Services\SubdomainGenerator;

/**
 * ApiController - Machine-to-machine endpoints for billing automations.
 */
class ApiController
{
    /**
     * POST /api/provision
     *
     * Creates and provisions a workspace from an external system such as n8n.
     */
    public function provision(): void
    {
        $this->authenticate();

        $payload = $this->jsonPayload();
        $name = trim((string) ($payload['name'] ?? ''));
        $email = trim(strtolower((string) ($payload['email'] ?? '')));
        $requestedSlug = trim((string) ($payload['slug'] ?? ''));

        if ($name === '') {
            Response::json(['ok' => false, 'error' => 'name is required'], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['ok' => false, 'error' => 'valid email is required'], 422);
        }

        $tm = new \Swarm\Services\TemplateManager();
        if (empty($tm->listVersions())) {
            Response::json(['ok' => false, 'error' => 'No VoxelSite template is prepared yet.'], 422);
        }

        $existing = Instance::findByEmail($email);
        if ($existing) {
            Response::json([
                'ok'            => true,
                'existing'      => true,
                'instance_id'   => (int) $existing['id'],
                'slug'          => $existing['slug'],
                'status'        => $existing['status'],
                'status_url'    => $this->absoluteUrl('/status/' . $existing['id']),
                'workspace_url' => 'https://' . $existing['subdomain'],
            ]);
        }

        $maxInstances = (int) Setting::get('max_instances', '100');
        $counts = Instance::countByStatus();
        if ($counts['total'] >= $maxInstances) {
            Response::json(['ok' => false, 'error' => 'Instance limit reached.'], 429);
        }

        $slug = $requestedSlug !== ''
            ? $this->sanitizeSlug($requestedSlug)
            : SubdomainGenerator::generate($name);

        if ($slug === '') {
            Response::json(['ok' => false, 'error' => 'valid slug is required'], 422);
        }

        if (Instance::slugExists($slug)) {
            Response::json(['ok' => false, 'error' => 'slug is already in use'], 409);
        }

        $notes = $this->buildProvisionNotes($payload);
        $instanceId = Instance::create([
            'slug'   => $slug,
            'name'   => $name,
            'email'  => $email,
            'status' => 'queued',
            'type'   => 'tenant',
        ]);

        if ($notes !== '') {
            Instance::update($instanceId, ['notes' => $notes]);
        }

        $baseDomain = Setting::get('base_domain', 'localhost');
        $response = [
            'ok'            => true,
            'existing'      => false,
            'instance_id'   => $instanceId,
            'slug'          => $slug,
            'status'        => 'queued',
            'status_url'    => $this->absoluteUrl('/status/' . $instanceId),
            'workspace_url' => "https://{$slug}.{$baseDomain}",
        ];

        http_response_code(202);
        header('Content-Type: application/json');
        echo json_encode($response);
        header('Connection: close');
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            flush();
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }
            flush();
        }

        ignore_user_abort(true);
        set_time_limit(0);

        Provisioner::run($instanceId);
        exit;
    }

    private function authenticate(): void
    {
        $expected = (string) (Setting::get('api_token') ?: ($_ENV['SWARM_API_TOKEN'] ?? ''));
        if ($expected === '') {
            Response::json(['ok' => false, 'error' => 'API token is not configured'], 503);
        }

        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            Response::json(['ok' => false, 'error' => 'Bearer token required'], 401);
        }

        if (!hash_equals($expected, trim($matches[1]))) {
            Response::json(['ok' => false, 'error' => 'Invalid API token'], 403);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonPayload(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            Response::json(['ok' => false, 'error' => 'Invalid JSON body'], 400);
        }

        return $payload;
    }

    private function sanitizeSlug(string $slug): string
    {
        $slug = preg_replace('/[^a-z0-9-]/', '', strtolower($slug)) ?? '';
        return preg_replace('/-+/', '-', trim($slug, '-')) ?? '';
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function buildProvisionNotes(array $payload): string
    {
        $external = [];
        foreach (['source', 'memberpress_member_id', 'memberpress_transaction_id', 'memberpress_subscription_id'] as $key) {
            if (isset($payload[$key]) && $payload[$key] !== '') {
                $external[$key] = (string) $payload[$key];
            }
        }

        if (empty($external)) {
            return '';
        }

        return "Provisioned via API\n" . json_encode($external, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private function absoluteUrl(string $path): string
    {
        $baseDomain = Setting::get('base_domain', 'localhost');
        return 'https://' . $baseDomain . $path;
    }
}
