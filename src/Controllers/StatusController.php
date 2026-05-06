<?php

declare(strict_types=1);

namespace Swarm\Controllers;

use Swarm\Helpers\Response;
use Swarm\Helpers\Url;
use Swarm\Models\Instance;

/**
 * StatusController — Provisioning progress screen + JSON polling endpoint.
 */
class StatusController
{
    /**
     * GET /status/{id} — Show the provisioning progress screen (HTML).
     */
    public function show(string $id): void
    {
        $instance = Instance::find((int) $id);

        if (!$instance) {
            http_response_code(404);
            Response::view('status', ['instance' => null, 'notFound' => true], 'public');
            return;
        }

        Response::view('status', [
            'instance'  => $instance,
            'notFound'  => false,
            'statusApiUrl' => Url::control('/api/status/' . $instance['id']),
        ], 'public');
    }

    /**
     * GET /api/status/{id} — JSON endpoint for Alpine.js polling.
     */
    public function json(string $id): void
    {
        $instance = Instance::find((int) $id);

        if (!$instance) {
            Response::json(['error' => 'Instance not found'], 404);
        }

        $messages = [
            'queued'           => 'Preparing your workspace...',
            'copy_template'    => 'Setting up your files...',
            'write_meta'       => 'Configuring your instance...',
            'create_subdomain' => 'Connecting your subdomain...',
            'health_check'     => 'Verifying everything works...',
            'activate'         => 'Your workspace is ready.',
            'send_welcome'     => 'Your workspace is ready.',
        ];

        $step    = $instance['step'] ?? 'queued';
        $status  = $instance['status'];
        $failed  = $status === 'failed';
        $message = $failed
            ? 'Setup ran into a problem. We\'ve been notified and will be in touch.'
            : ($messages[$step] ?? 'Working...');

        $url = null;
        if ($status === 'active') {
            $url = Url::workspace($instance, '/_studio/');
            $message = 'Your workspace is ready.';
        }

        Response::json([
            'status'  => $status,
            'step'    => $step,
            'message' => $message,
            'url'     => $url,
            'failed'  => $failed,
        ]);
    }
}
