<?php

declare(strict_types=1);

namespace Swarm\Middleware;

use Swarm\Helpers\Url;
use Swarm\Models\Session;

/**
 * Auth — Ensures the request has a valid operator session.
 * Redirects to the login page if not authenticated.
 */
class Auth
{
    public function handle(): void
    {
        if (!Session::validate()) {
            header('Location: ' . Url::control('/operator/login'));
            exit;
        }
    }
}
