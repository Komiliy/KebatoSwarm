<?php

declare(strict_types=1);

namespace Swarm\Middleware;

use Swarm\Helpers\Url;

/**
 * ControlApp - Keeps management/reporting routes on the configured control app.
 */
class ControlApp
{
    public function handle(): void
    {
        $controlUrl = Url::controlAppUrl();
        if ($controlUrl === '' || Url::isCurrentControlRequest()) {
            return;
        }

        $target = Url::control(Url::currentRequestPath());
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $isApi = str_starts_with(parse_url(Url::currentRequestPath(), PHP_URL_PATH) ?: '', '/api/')
            || str_contains($accept, 'application/json');

        if (in_array($method, ['GET', 'HEAD'], true) && !$isApi) {
            header('Location: ' . $target, true, 302);
            exit;
        }

        http_response_code(421);
        header('Content-Type: application/json');
        echo json_encode([
            'ok' => false,
            'error' => 'This endpoint must be called through the configured control_app_url.',
            'control_app_url' => $controlUrl,
        ]);
        exit;
    }
}
